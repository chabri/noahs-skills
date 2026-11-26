---
name: noahs-page
description: Build or rebuild one Drupal page with Noah's Page Builder widgets, section by section, through an idempotent PHP script run with drush, then generate its CSS exactly like the editor. Use for "create the home page", "add a pricing page", "recreate this section/page with Noahs", or to edit an existing Noahs page by code. Works free or Pro (Pro widgets and animations only when noahs_page_builder_pro is enabled).
---

# Build a page with Noahs widgets

Plugin files: `${CLAUDE_PLUGIN_ROOT}` (standalone zip: this skill's folder). Before writing code read
`references/data-model.md` §2–3 and the relevant rows of `references/widgets-catalogue.md`; Pro →
`references/pro-features.md`; patterns → `references/design-tips.md`.

## Prerequisites

The kit is installed in the project and defaults exist (see the `noahs-site` skill, Step 0):
`noahs-build/kit/noahs.sh env` works and `noahs-build/kit/work/defaults.json` exists. If not, set them up first.
Settings and Global styles should already be applied (pages inherit fonts, colors and buttons from them).

## Rules

- **No custom CSS**: only widget controls (normal/hover, desktop/tablet/mobile). Never fill `custom_css`.
- **FREE widgets only** unless `noahs.sh env` says `"pro": true`. Wrap Pro-only additions in `if (noahs_is_pro())`
  when a script must work on both.
- Every visible text is set explicitly (defaults contain lorem ipsum). Content in English unless asked otherwise.
- Field paths come from `work/defaults.json` — look a widget up before using it:
  `python3 -c "import json;d=json.load(open('noahs-build/kit/work/defaults.json'));print(json.dumps(d['noahs_button'],indent=1))" | head -80`
- Do not overwrite a page the user built by hand without asking: build into a new alias (or back up its row first:
  `drush sqlq "SELECT settings FROM noahs_page_builder_page WHERE entity_type='node' AND noahs_id='<nid>'" > backup.json`).

## Procedure

1. **Plan the sections** (from the brief or a reference): for each section list layout (columns per breakpoint),
   widgets, texts, images, colors. Prefer patterns from `design-tips.md`.
2. **Write the script** `noahs-build/pages/<slug>.php` (copy the shape of `examples/create-page.php`):
   ```php
   <?php
   require_once __DIR__ . '/../kit/php/lib.php';
   $node = noahs_ensure_node('/pricing', 'Pricing', 'page');        // find-or-create by alias
   $s = 'pricing';                                                     // seed prefix, unique per design
   $sections[] = noahs_section("$s-intro", [
     noahs_column("$s-intro-c", [
       noahs_widget('noahs_heading', "$s-intro-h", ['heading_text.text' => 'Simple pricing', 'heading_type' => 'h1',
         'css.desktop.default.text_align' => 'center'] + noahs_font('font', ['font_size' => '40px'], 'mobile')),
     ], ['100%']),
   ], noahs_resp('padding', [noahs_box('padding', '90px', '15px', '60px', '15px'), NULL, noahs_box('padding', '56px', '15px', '40px', '15px')]));
   noahs_save_page('node', $node->id(), $sections, $node->language()->getId());
   echo "nid=" . $node->id() . PHP_EOL;
   ```
   Helpers (all in `kit/php/lib.php`): `noahs_widget(type, seed, [dotted.path => value])`, `noahs_column(seed,
   elements, [desktop, tablet, mobile widths], set)`, `noahs_section(seed, columns, set)`, `noahs_inner()` (nested
   row), `noahs_resp()`, `noahs_font()`, `noahs_box()`, `noahs_border()`, `noahs_radius()`, `noahs_shadow()`,
   `noahs_url_value()`, `noahs_media_from_file()` + `noahs_image_value()` / `noahs_background_value()`,
   `noahs_save_block()` (reusable block), Pro: `noahs_motion_enter/cascade/scroll/float/hero_shrink()`,
   `noahs_hover_transform()`, `noahs_save_theme()`, `noahs_save_design()`.
   A reusable section builder is in `examples/hero-section.php`.
3. **Images**: put files in the project (e.g. `noahs-build/images/`), create media with
   `noahs_media_from_file(dirname(DRUPAL_ROOT) . '/noahs-build/images/x.webp', 'Name', 'Alt text')` (drush runs inside
   the docroot; build absolute paths from `DRUPAL_ROOT`). Only original or licensed images.
4. **Run** (from the project root): `noahs-build/kit/noahs.sh run noahs-build/pages/<slug>.php`
5. **CSS**: `noahs-build/kit/noahs.sh css node <nid>` (logs in with `drush uli`, runs the module's own CSS
   generator, saves `public://noahs/node/noahs_<nid>_<lang>.css`). Re-run after every structure change.
   Blocks: `css block_content <id>`; popups: `css popups <noahs_id>`; Pro: `css pro_theme <type>`,
   `css pro_display <entity_type>:<bundle>:<view_mode>`.
6. **Verify**:
   - `curl -sL -o page.html -w '%{http_code}\n' "$NOAHS_URL/node/<nid>"` → 200; texts present; `grep -c widget-id- page.html`.
   - CSS file linked (`grep -o 'noahs_<nid>_[a-z]*\.css' page.html`) or present in an aggregated `css_*.css`.
   - No new errors: `$NOAHS_DRUSH ws --count=10 --severity=Error`.
   - No placeholders: `grep -E 'Lorem|My button|Elit Eiusmod' page.html` → nothing.
   - Pro motion: attributes `data-noahs-motion-*` are delivered in `drupalSettings.noahs_page_builder.attributes`
     (and sometimes directly in the HTML) and applied by the front-end JS.
   - If a screenshot tool is available, check 1440 / 768 / 390 px widths.
7. Tell the user the URL, the editor URL (`/noahs_edit/node/<nid>`) and the script path.

## Common section recipes

- Hero: `examples/hero-section.php` (background image + gradient overlay + min-height + centered copy + 2 buttons).
- Feature grid: `examples/create-page.php` section 2 (3 columns `calc(33.333% - 20px)` / 50% / 100%, gap 30px,
  `columns_position: stretch`, card look via column background, border and radius).
- CTA band: full-color row, centered heading and button (`button_horizontal_align: center`).
- Listing: `noahs_widget('noahs_drupal_views', …, ['drupal_block' => json_encode(['blog', 'block_1'])])`.
- FAQ: `noahs_accordion` with `accordion_items.element_N`.

## Removing a test page

`noahs-build/kit/noahs.sh run noahs-build/examples/delete-page.php /alias ["media name"]` deletes the node, its Noahs
row, its CSS file and optionally the media created for it.
