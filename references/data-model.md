# Noah's Page Builder — data model

How Noahs stores pages, styles and templates, and how to write them by code. Verified against Noahs 4.0.x
(free) and the matching Noahs Pro. If the installed version differs, run the `noahs-discover` skill before trusting a
detail here.

## Modules

| Machine name | Project | Notes |
|---|---|---|
| `noahs_page_builder` | `drupal/noahs` (folder `modules/contrib/noahs`) | The builder. The folder name differs from the machine name. |
| `noahs_theme` | `drupal/noahs_builder_theme` | Base theme with header/footer regions. Optional but recommended for free sites. |
| `noahs_popups` | `drupal/noahs_popups` | Popups built with Noahs. |
| `noahs_gallery` | submodule of noahs | Media gallery widget. |
| `noahs_page_builder_pro` | commercial (noahs-creator.com) | Theme builder, Display builder, Pro widgets, motion extras. |
| `noahs_popups_pro` | commercial | Extra triggers, scheduling, audience, analytics for popups. |

Detect what is installed with `noahs.sh env` (prints `pro: true|false` and the widget list per provider).

## 1. Where a page is stored

Table **`noahs_page_builder_page`**:

| Column | Content |
|---|---|
| `page_id` | serial |
| `noahs_id`, `entity_id` | entity id (e.g. the nid); `noahs_settings` for Global styles |
| `entity_type` | `node`, `block_content`, `taxonomy_term`, `commerce_product`… or `noahs_settings` |
| `langcode` | `en`, … or `no_language` (Global styles) |
| `uid` | author |
| `settings` | **JSON string with the structure** |
| `page_settings` | JSON string with the page settings form (`{page_settings, page_fields}`) |
| `modified_date` | timestamp |

Noahs renders a design only for bundles enabled in Settings: `use_in_ctype` (node types), `use_in_vtype`
(vocabularies), `use_in_products` (product types), `use_in_btypes` (block types). When a design exists, Noahs replaces
the whole content region (page title and messages included).

## 2. Structure format

An array of sections. Every node is the widget's flat settings object `{wid, type, element}` plus `columns` (on rows)
or `elements` (on columns). A nested section is just another element of a column.

```json
[ { "wid": "n1a2b3c4d5", "type": "noahs_row",
    "element": { "class": {"section_container": "n-container"}, "css": {"desktop": {"default": {"padding": {…}}}} },
    "columns": [ { "wid": "n6e7f8a9b0", "type": "noahs_column",
                   "element": { "css": { "desktop": {"default": {"column_width": "calc(50% - 15px)"}}, "mobile": {"default": {"column_width": "100%"}} } },
                   "elements": [ { "wid": "nc1d2e3f4a", "type": "noahs_heading",
                                   "element": { "heading_text": {"text": "Hello"}, "heading_type": "h2" } } ] } ] } ]
```

- `wid`: any string valid in a CSS id. Use a stable hash of a seed (`noahs_wid('home-hero-title')`) so re-running a
  script keeps ids and CSS selectors. Every widget renders with `id="widget-id-<wid>"` (usable as an anchor).
- An older format wrapped settings in `"settings": {"element": …}` (seen in bundled default structures). It is still
  read, but always write the flat format.
- A column placed directly inside another column's `elements` is **not rendered**: wrap it in a nested row.

### `element` = the serialized widget form

The editor serializes the widget form (`name="element[...]"`). Rules:

- Plain fields: `element.<field>`. `text` controls store `{text, extra_value}`; `textarea` / `select` store a string.
- `style_type: style` fields: `element.css.<desktop|tablet|mobile>.<default|hover>.<field>` (tablet/mobile only if the
  field is responsive, hover only if it has a hover state).
- `style_type: class` fields: `element.class.<field>` (applied as classes on the front end).
- `style_type: attribute` fields: `element.attribute.<field>` (rendered as `data-*` attributes; used by Pro motion).
- Repeatable items (slides, tabs, list items): `element.<field>.element_N.<subfield>`; their styles live at
  `element.css.desktop.default.<field>.<subfield>.element_N`.

**Always start from the real defaults**: `noahs.sh defaults` writes `work/defaults.json`, built exactly like the
editor does when a widget is dropped (widget form HTML → the module's own `serializejson.js`). Re-run it after
updating Noahs or enabling a module that adds options (Webform, Commerce, Pro). Defaults contain **placeholder copy**
(lorem ipsum, "My button"): always set every visible text.

Field list of any widget: `\Drupal::service('noahs_page_builder.widget_service')->getWidgetFields('<id>')`.

### Common control values

| Control | Value |
|---|---|
| `noahs_font` | `{font_family, font_weight, font_size, line_height, letter_spacing, word_spacing, text_transform, font_style, text_decoration, color, margin_top, margin_bottom, padding_top, padding_bottom}` |
| `noahs_padding` / `noahs_margin` | `{padding_top, padding_right, padding_bottom, padding_left}` / `margin_*` |
| `noahs_border` | `{border_color, border_style, border_top_width, border_right_width, border_bottom_width, border_left_width}` |
| `noahs_radius` | `{border_top_left_radius, border_top_right_radius, border_bottom_right_radius, border_bottom_left_radius}` |
| `noahs_shadows` | `{shadow_x, shadow_y, blur, spread, color}` |
| `noahs_color` | `"#222222"` or `rgba(…)` |
| `noahs_url` | `{source: "custom", url: "/shop", entity_id: "", text: "/shop", url_params: ""}`; `entity_id: "node:12"` links to an entity in the current language |
| `noahs_image` | `{fid: <media id>, url, thumbnail, media_type: "noahs_image", image_style}` |
| `noahs_background_image` | `{background_image: {fid, url, thumbnail, media_type, background_position, background_repeat, background_attachment, background_size}, image_style, …}` |
| `noahs_icon` | `{class: "fa-solid fa-star", fid: "", url: ""}` (Font Awesome 6 Free classes, or an SVG media in `fid`) |

**Images are media entities** of type `noahs_image` (field `field_media_image`): `fid` is the **media id**, not a
file id. Create the media by code (`noahs_media_from_file()`). Background images are resolved server-side.

## 3. CSS of a design

Saving a structure does not create its CSS. The editor generates CSS **in JavaScript** (`generate-css.js`) on Save and
posts it to the server, which writes `public://noahs/<entity_type>/noahs_<id>_<langcode>.css`.

By code, `noahs.sh css <entity_type> <id>` reproduces that exactly: it logs in with `drush uli`, downloads the editor
page (which carries `drupalSettings.noahs_items_fields` and the page settings form), runs the module's own
`generate-css.js` (read from `assets/js/editor.js.map`, which ships `sourcesContent`) in jsdom, and saves the result
with the module's `saveCss()`. Output is byte-identical to pressing Save in the editor.

Alternative without Node: open the editor (`/noahs_edit/<entity_type>/<id>`) and press Save.

Editor URLs: pages `/noahs_edit/<entity_type>/<id>`; popups `/noahs-admin/noahs-popup/popup-builder/<id>`;
Theme builder `/noahs-admin/theme_builder/pro_theme/<type>`; Display builder
`/noahs-admin/display_builder/<bundle>/<entity_type>/<view_mode>`.

## 4. Settings and Global styles (two different things)

| | Settings (step 1) | Global styles (step 2) |
|---|---|---|
| UI | `/admin/structure/noahs/settings` | `/noahs-admin/settings-styles` |
| Storage | config `noahs_page_builder.settings` | row in `noahs_page_builder_page` with `noahs_id = entity_id = entity_type = noahs_settings`, `langcode = no_language` |
| CSS | `public://noahs/noahs_settings/general_settings.css` (generated in PHP on form submit) | `public://noahs/noahs_settings/settings.css` (generated in JS by the editor) |
| By code | `noahs.sh settings spec.json` | `noahs.sh global-styles spec.json` |

Apply Settings first: Global styles map colors equal to the primary/secondary/custom colors to CSS variables.

### Settings keys (`noahs_page_builder.settings`)

| Key | Example | Effect |
|---|---|---|
| `principal_color`, `secondary_color` | `"#2563EB"` | `--noahs-primary-color`, `--noahs-secondary-color` |
| `custom_color` | `[{name: "Ink", hex: "#111827"}]` | `--noahs_page_builder-111827`, offered in every color picker |
| `available_fonts` | `{Inter: Inter, Poppins: Poppins}` | fonts offered and loaded (bundled locally, no Google Fonts) |
| `heading_font`, `general_font` | `"Poppins"` | headings / body font family |
| `custom_fonts` | `[{name, files: [], css: "@font-face{…}"}]` | extra fonts; use relative `url('../fonts/x.woff2')` (→ `public://noahs/fonts/`) so the site stays portable |
| `container_width` (+ `container_small/large/extra_large/extra_extra_large`) | `1200` | `.n-container` max-width (and `-sm/-lg/-xl/-xxl`) |
| `viewport_tablet`, `viewport_mobile` | `959`, `767` | breakpoints of all generated CSS |
| `use_editor` | `"full_html"` | text format of the Text widget (always set it) |
| `use_in_ctype` / `use_in_vtype` / `use_in_products` / `use_in_btypes` | `{page: page}` | where Noahs is enabled (bundles must exist) |
| `logo_width`, `header_*`, `footer_*` | | header/footer styles of `noahs_theme` |
| `noahs_page_builder_custom_css` / `_custom_js` | | global custom code (avoid: prefer controls) |

### Global styles (`noahs_settings` widget)

Useful fields under `element.css.<bp>.<state>`: `font_h1` … `font_h5`, `body`, `body_background_color`,
`button_background_color`, `button_font`, `button_border`, `button_padding`, `button_margin`, `button_shadows`,
`button_radius` (selector `.btn, .btn-theme, .button`), the same with suffix `_style_1` … `_style_4` (button styles the
Button widget offers as `class.button_style = btn-style-N`), `form_label_font`, `forms_input_*`, `pager_*` (Drupal
pager) and `swipers_*` / `swiper_pagination_*` (carousel arrows and bullets). There is no H6 and no link color: set H6
and links per widget.

## 5. Reusable blocks and the free mega menu

- A `block_content` of type `noahs_block_html` (bundle in `use_in_btypes`) is designed like a page:
  row with `entity_type = block_content`, editor `/noahs_edit/block_content/<id>`, CSS with `noahs.sh css block_content <id>`.
  Place it in a theme region like any block. Typical use on free sites: header pieces, footer columns, banners shown on
  every product page.
- Mega menu (free): base field **`noahs_megamenu_block`** on menu links references a reusable Noahs block; the panel is
  only rendered by the **"Noahs Menu" block** (`noahs_menu_block`), not by core's menu block. Block settings:
  `menu_name`, `level`, `depth`, `noahs_menu_responsive` (burger + off-canvas), `noahs_menu_orientation`,
  `noahs_navigation_position` / `noahs_wrapper_position` (`static` = panel spans the header width). Requires all
  database updates applied (`drush updb`).
- The `noahs_drupal_views` (View) widget only lists **block** displays: give every listing a block display. If a page
  alias equals a view page path (`/blog`), remove the page display.

## 6. Popups (`noahs_popups`)

Table `noahs_page_builder_popups`: `name`, `noahs_id` (unique machine id), `langcode`, `settings` (structure, same
format as a page), `page_settings` (serialized popup settings form **without** wrapper:
`{"element": {"css": {"desktop": {"default": {"bg_color": …, "popup_padding": …, "popup_radius": …}}}}}`),
`popup_settings` (JSON), `status`. CSS: `noahs.sh css popups <noahs_id>`.

```json
{"conditions": {"0": {"action": "include", "type": "entire_site|single_page|content_type", "pages": ["12"], "types": ["page"]}},
 "triggers": {"on_load": {"enabled": "true", "delay": "4"}, "on_scroll": {"enabled": "false", "offset": "300"}},
 "close_action": {"delay_value": "1", "delay_unit": "days"}}
```

Buttons open/close popups through the "Popup Actions" fields added to the Button widget (`open_popup` / `close_popup`
with the popup id). Pro additions are in `pro-features.md`.

## 7. Templates on free sites

Free Noahs stores one design **per entity**; there are no per-bundle templates. For product / article detail pages on
a free site: a Twig template in your sub-theme for the skeleton plus reusable Noahs blocks placed in the content region
with bundle visibility, and the View widget for related content. Per-bundle templates = Pro Theme builder.

Entity-aware free widgets: `noahs_drupal_title`, `noahs_drupal_token` and Heading tokens (node of the current route),
`noahs_drupal_views` with `contextual_arguments`, `noahs_insert_node`, `noahs_drupal_block`, `noahs_block`.

## 8. Multilingual

`noahs_page_builder_load()` loads the default-language row and, for another language, only overlays the
**translatable** control values of that language's row (same wids). Without a translated row the default design is
shown. CSS of the default language is reused unless the translation was saved in "custom" layout mode.
Use language-neutral aliases (`und`) when nodes are not translated, otherwise `/es/shop` returns 404.

## 9. Pro storage (summary — details in `pro-features.md`)

| Feature | Table / config | CSS |
|---|---|---|
| Theme builder (header, footer, page layout, per-bundle and per-route templates) | `noahs_page_builder_pro_themes` (`type`, `entity_type = pro_theme`, `langcode`, `settings`, `page_settings`) + config `noahs_page_builder_pro.theme_assignments` | `public://noahs/pro_theme/noahs_<type>_<lang>.css` |
| Display builder (view modes: teasers, cards) | `noahs_page_builder_pro_designs` (`entity` = bundle, `entity_type`, `display` = view mode, `langcode`) | `public://noahs/pro_display/noahs_<et>_<bundle>_<vm>_<lang>.css` |
| Revisions | `noahs_page_builder_revisions` (editor saves only) | — |
