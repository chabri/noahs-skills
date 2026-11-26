---
name: noahs-discover
description: Inspect the installed Noah's Page Builder code (free, and Pro / Popups if present) to verify or refresh how pages, styles, templates and widgets are stored before building by code - never guess a format. Use when the references do not match the site, after updating Noahs, when a widget or field is unknown, or when the user asks "how does Noahs store X".
---

# Discover how the installed Noahs version works

Plugin files: `${CLAUDE_PLUGIN_ROOT}` (standalone zip: this skill's folder). The references in
`references/` describe Noahs 4.0.x. This skill checks them against the code actually installed and tells the user
(or updates the project notes) what differs. **Read code; never invent formats.**

## Rules

- Read-only: do not edit module code (`modules/contrib`, Pro modules) and do not change site content while
  investigating. Use `drush php:eval` for read-only calls only.
- Pro modules are commercial: you may read them on the user's site to learn field names and stored formats, but
  never copy their code into answers, files or the user's scripts. Describe behaviour in your own words.
- Record findings for the project (e.g. `noahs-build/NOTES.md`), not only in the conversation.

## 1. Versions and modules

```bash
noahs-build/kit/noahs.sh env      # versions, pro / popups_pro, enabled bundles, widgets per provider
$NOAHS_DRUSH php:eval 'echo \Drupal::service("extension.list.module")->getPath("noahs_page_builder");'
$NOAHS_DRUSH updatedb:status       # pending updates change fields (e.g. noahs_megamenu_block)
```
Locate Pro on disk: `$NOAHS_DRUSH php:eval 'echo \Drupal::service("extension.list.module")->getPath("noahs_page_builder_pro");'`

## 2. Storage

- Tables: `$NOAHS_DRUSH sqlq "SHOW TABLES LIKE 'noahs%'"`; schema in `noahs_page_builder.install` (and the Pro / Popups
  `.install` files): `noahs_page_builder_page`, `noahs_page_builder_popups`, `noahs_page_builder_pro_themes`,
  `noahs_page_builder_pro_designs`, `noahs_page_builder_revisions`.
- Read a real design made in the editor (best ground truth):
  `$NOAHS_DRUSH sqlq "SELECT settings FROM noahs_page_builder_page WHERE entity_type='node' AND noahs_id='<nid>'" | python3 -m json.tool | head -80`
- Save endpoints: grep the routing files for `save_page`, `save_theme`, `save_display` and read the controller
  methods they point to (what is stored, how CSS is saved: `saveCss()` and the file path).

## 3. Widgets and fields

- List: `noahs.sh env` → `widgets` (provider = module). Plugin classes: `src/Plugin/Widget/Widget*.php`.
- Fields of one widget (types, `style_type`, selectors, defaults):
  `$NOAHS_DRUSH php:eval 'print_r(array_map(fn($f) => array_intersect_key($f, array_flip(["type","title","style_type","style_selector","style_css","responsive","style_hover","default_value"])), \Drupal::service("noahs_page_builder.widget_service")->getWidgetFields("noahs_button")));'`
- Real defaults: `noahs.sh defaults` (re-run after updates) and inspect `work/defaults.json`.
- Control value shapes: `src/Plugin/Control/ControlNoahs*.php` (sub-keys of font, border, padding, radius, shadows…).
- Templates (Twig) under `templates/widgets/` show how values are rendered (useful when something does not show).

## 4. CSS generation

- The CSS of designs comes from the editor JS. Confirm the kit can still read the generator:
  `python3 -c "import json;m=json.load(open('<module>/assets/js/editor.js.map'));print([s for s in m['sources'] if 'generate-css' in s], bool(m.get('sourcesContent')))"`
- Settings CSS is PHP (`NoahsSettingsForm::submitForm()`); Global styles CSS is JS (the kit ports it in
  `kit/php/global-styles.php` — compare with `dry` against `public://noahs/noahs_settings/settings.css` after saving
  once in the UI).

## 5. Pro specifics (only if installed)

- Theme builder catalogue and conditions: the Pro theme template manager service and the config
  `noahs_page_builder_pro.theme_assignments` (`$NOAHS_DRUSH cget noahs_page_builder_pro.theme_assignments`).
- Template settings fields (header layout, on-scroll styles): open `/noahs-admin/theme_builder/pro_theme/header` and
  read the settings form field names, or read the stored `page_settings` of an existing header row.
- Motion extras: look for `motion_` fields in `work/defaults.json` (e.g. `noahs_row.attribute`) to know which
  effects this version offers; new widgets (marquee, counter…) appear in `env`.

## 6. Report

Summarize: version, differences from `references/` (renamed fields, new widgets, changed storage), and the exact
values to use. If a difference affects the kit (`kit/php/*.php`, `kit/node/*.js`), explain the fix to the user.
