#!/usr/bin/env bash
# Builds one self-contained zip per skill in dist/ (skill folder + references/ + kit/), for uploading the skills
# to claude.ai or to other agents that read SKILL.md (GitHub Copilot, Codex, Cursor...). Also builds
# noahs-skills-all.zip with the three folders, ready to unzip into a skills directory. Claude Code users do not
# need this: install the plugin instead.
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
OUT="$ROOT/dist"
rm -rf "$OUT" && mkdir -p "$OUT"
for dir in "$ROOT"/skills/*/; do
  name="$(basename "$dir")"
  tmp="$(mktemp -d)"
  mkdir -p "$tmp/$name"
  cp -R "$dir". "$tmp/$name/"
  cp -R "$ROOT/references" "$ROOT/kit" "$tmp/$name/"
  rm -rf "$tmp/$name/kit/work" "$tmp/$name/kit/node/node_modules"
  # Inside a standalone zip the plugin root is the skill folder itself.
  sed -i.bak 's#\${CLAUDE_PLUGIN_ROOT}#<this skill folder>#g' "$tmp/$name/SKILL.md" && rm -f "$tmp/$name/SKILL.md.bak"
  (cd "$tmp" && zip -qr "$OUT/$name.zip" "$name")
  mkdir -p "$OUT/all" && cp -R "$tmp/$name" "$OUT/all/"
  rm -rf "$tmp"
  echo "dist/$name.zip"
done
(cd "$OUT/all" && zip -qr "$OUT/noahs-skills-all.zip" .) && rm -rf "$OUT/all"
echo "dist/noahs-skills-all.zip"
