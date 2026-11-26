#!/usr/bin/env bash
# Noahs build kit: thin wrapper around drush + Node to build Noah's Page Builder content by code.
#
# Run it FROM THE DRUPAL PROJECT ROOT (the folder with composer.json), with the kit copied inside the project
# (containers such as DDEV or Lando only see files inside the project):
#
#   noahs-build/kit/noahs.sh <command> [args]
#
# Commands
#   env                              JSON summary: Drupal/Noahs versions, Pro modules, enabled bundles, widgets.
#   defaults                         Regenerate work/defaults.json (real default values of every installed widget).
#   run <script.php> [args…]         Run a PHP build script with drush php:script (script path relative to project root).
#   settings <spec.json> [dry]       Apply Noahs Settings (config + general_settings.css), like saving the form.
#   global-styles <spec.json> [dry]  Apply Global styles (noahs_settings row + settings.css). "dry" prints the CSS only.
#   css <entity_type> <id> [lang]    Generate and save the CSS of a saved design with the editor's own JS.
#                                    entity_type: node | block_content | taxonomy_term | commerce_product | popups
#                                                 | pro_theme (id = header, footer, blog…) | pro_display (id = <entity_type>:<bundle>:<view_mode>)
#
# Environment
#   NOAHS_DRUSH  drush command. Default: "ddev drush" if .ddev/ exists, "lando drush" if .lando.yml exists,
#                otherwise "vendor/bin/drush". Examples: "ddev drush @mysite", "vendor/bin/drush -l example.com".
#   NOAHS_URL    Site base URL, needed by "css" (default: drush status uri).
#   NOAHS_WEB    Host path of the Drupal docroot (default: web, docroot or . — whichever holds core/).
#   NOAHS_INSECURE=1  Accept self-signed HTTPS certificates (automatic for *.ddev.site, *.lndo.site, localhost, *.test).
set -euo pipefail

KIT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT="$(pwd)"
case "$KIT" in "$ROOT"/*) ;; *) echo "The kit must live inside the project and be run from the project root." >&2; exit 1 ;; esac
KIT_REL="${KIT#"$ROOT"/}"
WORK="$KIT/work"
mkdir -p "$WORK"

if [[ -z "${NOAHS_DRUSH:-}" ]]; then
  if [[ -d .ddev ]]; then NOAHS_DRUSH="ddev drush"
  elif [[ -f .lando.yml ]]; then NOAHS_DRUSH="lando drush"
  else NOAHS_DRUSH="vendor/bin/drush"; fi
fi
if [[ -z "${NOAHS_WEB:-}" ]]; then
  for d in web docroot html .; do [[ -f "$d/core/lib/Drupal.php" ]] && { NOAHS_WEB="$d"; break; }; done
fi
NOAHS_WEB="${NOAHS_WEB:-web}"

# shellcheck disable=SC2086
D() { $NOAHS_DRUSH "$@" </dev/null; }
php_script() { local s="$1"; shift; D php:script "$KIT_REL/php/$s" -- "$@"; }

node_ready() {
  command -v node >/dev/null || { echo "Node.js 18+ is required for this command." >&2; exit 1; }
  [[ -d "$KIT/node/node_modules/jsdom" ]] || (cd "$KIT/node" && npm install --no-audit --no-fund --silent >/dev/null)
}

module_dir() { # host path of the noahs_page_builder module
  local rel
  rel="$(D php:eval 'echo \Drupal::service("extension.list.module")->getPath("noahs_page_builder");' 2>/dev/null | tail -1)"
  echo "$NOAHS_WEB/$rel"
}

site_url() {
  if [[ -n "${NOAHS_URL:-}" ]]; then echo "${NOAHS_URL%/}"; return; fi
  D status --field=uri 2>/dev/null | tail -1 | sed 's#/$##'
}

cmd="${1:-help}"; shift || true
case "$cmd" in
  env)
    php_script env.php ;;

  defaults)
    node_ready
    php_script widget-forms.php
    node "$KIT/node/defaults.js" "$WORK/widget-forms.json" "$(module_dir)" "$WORK/defaults.json"
    rm -f "$WORK/widget-forms.json" ;;

  run)
    [[ $# -ge 1 ]] || { echo "Usage: noahs.sh run <script.php> [args]" >&2; exit 1; }
    s="$1"; shift
    D php:script "$s" -- "$@" ;;

  settings)
    [[ $# -ge 1 ]] || { echo "Usage: noahs.sh settings <spec.json> [dry]" >&2; exit 1; }
    cp "$1" "$WORK/settings-spec.json"
    php_script settings.php settings-spec.json "${2:-apply}" ;;

  global-styles)
    [[ $# -ge 1 ]] || { echo "Usage: noahs.sh global-styles <spec.json> [dry]" >&2; exit 1; }
    cp "$1" "$WORK/global-styles-spec.json"
    php_script global-styles.php global-styles-spec.json "${2:-apply}" ;;

  css)
    [[ $# -ge 2 ]] || { echo "Usage: noahs.sh css <entity_type> <id> [langcode]" >&2; exit 1; }
    node_ready
    et="$1"; id="$2"; lang="${3:-}"
    [[ -n "$lang" ]] || lang="$(D php:eval 'echo \Drupal::languageManager()->getDefaultLanguage()->getId();' 2>/dev/null | tail -1)"
    key="$(echo "$et-$id-$lang" | tr ':/' '__')"
    php_script export-structure.php "$et" "$id" "$lang" "$key"
    url="$(site_url)"
    case "$et" in
      popups) editor="$url/noahs-admin/noahs-popup/popup-builder/$id" ;;
      pro_theme) editor="$url/noahs-admin/theme_builder/pro_theme/$id" ;;
      pro_display) IFS=: read -r pet pbundle pvm <<<"$id"; editor="$url/noahs-admin/display_builder/$pbundle/$pet/$pvm" ;;
      *) editor="$url/noahs_edit/$et/$id" ;;
    esac
    # Local dev certificates (DDEV/Lando/mkcert) are usually not trusted by curl/Node: allow them for local hosts.
    case "$url" in *.ddev.site*|*.lndo.site*|*localhost*|*127.0.0.1*|*.test|*.test/*|*.local|*.local/*) NOAHS_INSECURE="${NOAHS_INSECURE:-1}" ;; esac
    jar="$WORK/cookies.txt"; rm -f "$jar"
    k=(); [[ "${NOAHS_INSECURE:-}" == 1 ]] && k=(-k)
    uli="$(D uli --no-browser --uri="$url" 2>/dev/null | tail -1)"
    curl -s ${k[@]+"${k[@]}"} -L -c "$jar" -b "$jar" -o /dev/null "$uli"
    curl -s ${k[@]+"${k[@]}"} -L -c "$jar" -b "$jar" -o "$WORK/editor-$key.html" "$editor"
    cookie="$(awk -F'\t' 'NF>=7 && $6 ~ /^S?SESS/ {print $6"="$7}' "$jar" | head -1)"
    rm -f "$jar"
    tls=(); [[ "${NOAHS_INSECURE:-}" == 1 ]] && tls=(env NODE_TLS_REJECT_UNAUTHORIZED=0)
    ${tls[@]+"${tls[@]}"} env NOAHS_BASE_URL="$url" NOAHS_COOKIE="$cookie" NOAHS_MODULE_DIR="$(module_dir)" \
      node "$KIT/node/page-css.js" "$WORK/editor-$key.html" "$WORK/struct-$key.json" "$WORK/css-$key.css" "$WORK/ps-$key.json"
    php_script save-css.php "$et" "$id" "$lang" "$key"
    rm -f "$WORK/editor-$key.html" ;;

  *)
    sed -n '2,24p' "${BASH_SOURCE[0]}" | sed 's/^# \{0,1\}//' ;;
esac
