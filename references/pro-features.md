# Noahs Pro features

Use these **only when `noahs.sh env` reports `"pro": true`** (module `noahs_page_builder_pro`; popups extras need
`noahs_popups_pro`). A free site must never depend on anything in this file. Names below are the stored field names;
check them against `work/defaults.json` of your installed version.

## 1. Theme builder

Designs stored in `noahs_page_builder_pro_themes` (one row per `type` + `langcode`; `entity_type = pro_theme`;
`settings` = structure in the page format; `page_settings` = template settings form). Helper: `noahs_save_theme()`.
CSS: `noahs.sh css pro_theme <type>`.

| `type` | Applies to |
|---|---|
| `header`, `footer` | every front-end route |
| `noahs_page` | full page layout; must contain *Main Page Content* (`noahs_main_page_content`) or *Theme Content* (`noahs_theme_content` with `noahs_theme = header / noahs_entity_html / footer`) slots |
| `noahs_maintenance` | maintenance page |
| `noahs_login`, `noahs_register`, `noahs_user_pass`, `noahs_user_entity` | account routes (replace page content) |
| `commerce_cart`, `commerce_checkout_oder_info` (sic) | cart and checkout (widgets Cart Block, Checkout, Checkout Progress) |
| `<node type>`, `<vocabulary>`, `commerce_<product type>` | **per-bundle template** for the canonical route; only bundles enabled in Settings (`use_in_*`) appear |
| custom entities (`/admin/structure/noahs_page_builder_pro/custom-theme-entity`) | any Drupal route |

Per-bundle templates replace the page content with the entity rendered through the design, so entity widgets receive
the entity (Node Field, Commerce Field, Variation Field, Product Price, Add to cart). Do not give the `page` bundle a
template if pages have their own designs (or tick "Override default builder" on each node).

**Conditions and variants** — config `noahs_page_builder_pro.theme_assignments`, `templates.<type> = {label, kind,
entity_type, enabled, priority, variant, assignment_version: 1, conditions: {include_paths, exclude_paths,
include_routes, exclude_routes, roles[], languages[], bundles[]}}` (path/route patterns one per line, `*` wildcard,
`<front>`; bundles like `node:blog`). Without an entry a type applies everywhere. Extra headers/footers are *variants*
(any type id, `variant: TRUE`, `kind: header|footer`): the highest priority whose conditions match wins. Helper:
`noahs_theme_conditions()`. UI: `/admin/structure/noahs_page_builder/themes`.

**Header settings** (`page_settings.page_settings.element`): `class.header_layout` = `''` (fixed with spacer),
`hedaer_overlap` (sic, over the content), `header_sticky`, `static`; `hide_on_scroll_down` = `on|off`;
`header_sticky_scroll_offset.text`; styles `css.<bp>.default.header_*` (bg color, gradient, border, padding, shadow,
radius, max width) and `header_on_scroll_*` (applied when the page has scrolled: translucent background, margin,
radius, shadow, padding). Footer settings: max width / height.

Header building blocks: `noahs_drupal_site_logo` (`logo_width`), `noahs_drupal_nav` (Menu), `noahs_drupal_nav_icon`
(icon opening a menu, e.g. account), `noahs_drupal_cart` (cart icon + AJAX side cart; icon under `class.icon`),
`noahs_drupal_lang_switcher`, `noahs_button`. See `examples/pro-header.php`.

**Mega menu in a Pro header**: the Menu widget renders the panels of links that reference a Noahs block
(`noahs_megamenu_block`). Set `css.desktop.default.navigation_position` and `navigation_wrapper_position` to
`static`, and `element_position: static` on the widget and its column, so the panel spans the header width. Style
links with `group_links_*` / `group_sub_links_*`, the panel container with `submenu_*`, the burger with `burger_*`,
and the drawer breakpoint with `menu_breakpoint`.

## 2. Display builder (view modes)

Table `noahs_page_builder_pro_designs`: `entity` = bundle, `entity_type` (`node`, `taxonomy_term`,
`commerce_product`), `display` = view mode (`teaser`, `card`…), `langcode` (`''` = any). Helper: `noahs_save_design()`.
CSS: `noahs.sh css pro_display <entity_type>:<bundle>:<view_mode>`. The design replaces the entity's render in that
view mode **everywhere**, including Views using "Rendered entity". The view mode must exist and its display should be
enabled for the bundle (widgets use its formatters). Editor: `/admin/structure/noahs_page_builder_pro/designs/add`.

Teaser widgets: `noahs_node_field` (`drupal_field`, `node_view.text` = formatter view mode, `wrapp_element`),
`noahs_commerce_field` (`add_entity_url` links to the product), `noahs_drupal_product_price`
(`noahs_commerce_product: current_product`, `noahs_commerce_list_price` for strike-through), `noahs_add_to_cart`.

Known limits: classes, `hidden_element` and motion attributes of widgets inside a teaser may be lost (CSS, hover
transforms, shadows and radius work) — put animations on the View widget or its section; link a teaser with an image
formatter "link to content" or Heading tokens (`[node:url]`, recent Pro resolves tokens per card).

## 3. Motion extras ("Extras" tab of every widget)

Stored as attributes (`element.attribute.<field>`), output as `data-noahs-motion-*` on `#widget-id-<wid>` and run by the
Pro front-end JS. They generate no CSS, do not run inside the editor canvas and respect `prefers-reduced-motion`.
Values equal to the default are not emitted. Helpers: `noahs_motion_*()` in `kit/php/lib.php`.

| Group (widgets) | Fields | Values (default) |
|---|---|---|
| Entrance (all) | `motion_enter`, `motion_enter_opacity`, `motion_duration` ms (700), `motion_delay` ms, `motion_repeat` (`once`/`repeat`), `motion_ease`, `motion_devices` (`all`/`desktop`/`no-mobile`) | `fade`, `fade-up`, `fade-down`, `fade-left`, `fade-right`, `zoom-in`, `zoom-out`, `flip-up` |
| Scroll-linked (all) | `motion_scroll`, `motion_intensity` %, `motion_start`/`motion_end` (% of viewport), `motion_smoothing` ms, `motion_scroll_devices`; `custom` uses `motion_{x,y,scale,rotate,opacity}_{from,to}` | `rise`, `drift`, `zoom`, `reveal`, `depth` (parallax), `soft-reveal`, `fade-out`, `rotate-in`, `custom` |
| Floating (widgets, not rows/columns) | `motion_float: on`, `motion_float_distance` px, `motion_float_duration` s, `motion_float_speed`, `motion_float_devices` | |
| Cascade (row, column) | `motion_cascade` (entrance effects), `motion_cascade_gap` ms, `_order` (`forward`/`reverse`/`center`), `_duration`, `_delay`, `_opacity`, `_ease`, `_repeat`, `_devices` | animates direct children one after another |
| Animate children (Card Grid, Card Grid Scroll) | `motion_children: on`, `motion_children_gap` | needs `motion_enter` |
| Hero scroll scene (row) | `motion_hero` = `shrink` / `expand-column`, `_scale`, `_opacity`, `_radius`, `_column`, `_width`, `_fade`, `_distance`, `_smoothing`, `_devices` (default `no-mobile`) | |
| Section snap (row) | `motion_snap: on`, `motion_snap_offset`, `motion_snap_devices` | |
| Transform (all) — **CSS** | `css.desktop.{default,hover}.noahs_element_transform` = `{scale, rotate_x, rotate_y, rotate_z, translate_x, translate_y, skew_x, skew_y}`, `noahs_element_perspective` | hover lifts and zooms |

Tips: use smoothing `0` for scroll-linked effects (native scroll timeline, no lag); horizontal entrances
(`fade-left/right`, `drift`) on wide columns can cause horizontal scroll on phones — use `fade-up` or `no-mobile`.

Other Pro extras: `cookie_control`, `param_controls` / `param_controls_value` / `param_controls_default` (show a
widget depending on a URL parameter). They lack cache contexts in some versions: avoid on cached public pages.

### Newer scroll effects (recent Pro releases)

Some Pro versions add: **Text reveal** on Heading/Text (`motion_text_reveal` = `lines` / `words`, `motion_text_stagger`,
`_duration`, `_delay`, `_ease`, `_repeat`, `_devices`), **Horizontal scroll** on rows (`motion_hscroll: on`,
`_rail`, `_offset`, `_smoothing`, `_devices`; columns must add up to more than 100 %), **Page tint** on rows
(`motion_tint.text` = body color while the row is centered), and widgets **Marquee** (`noahs_marquee`), **Counter**
(`noahs_counter`) and **Scroll progress** (`noahs_scroll_progress`). Only use them if they appear in `noahs.sh env` /
`work/defaults.json`.

## 4. Pro widgets

| Widget | Use |
|---|---|
| `noahs_drupal_nav` (Menu), `noahs_drupal_nav_icon`, `noahs_drupal_lang_switcher` | navigation |
| `noahs_drupal_cart`, `noahs_drupal_cart_block`, `noahs_drupal_checkout`, `noahs_drupal_checkout_progress` | Commerce cart and checkout |
| `noahs_drupal_product`, `noahs_drupal_products`, `noahs_drupal_product_price`, `noahs_add_to_cart`, `noahs_commerce_field`, `noahs_commerce_variation_field` | products and product templates |
| `noahs_node_field`, `noahs_drupal_nodes`, `noahs_drupal_nodes_slides` | node templates, hand-picked posts (grid / carousel) |
| `noahs_main_page_content`, `noahs_theme_content`, `noahs_drupal_region`, `noahs_maintenance_message` | layout slots |
| `noahs_drupal_user_login`, `noahs_drupal_register_form`, `noahs_reset_pass_form`, `noahs_drupal_user`, `noahs_drupal_tabs` | account templates |
| `noahs_drupal_contact_form`, `noahs_drupal_pro_webform` | forms |
| `noahs_heading_animated` (`animate_type`, `text_before`, `animated_text` words separated by `;`, `text_after`, `animated_font`) | rotating / accent words in a title |
| `noahs_icon_step_pro` (Steps), `noahs_timeline`, `noahs_price_list`, `noahs_review_pro` (Reviews), `noahs_feature_blocks`, `noahs_video_button_pro` | marketing sections |

## 5. Popups Pro

Adds `popup_settings.pro`:

```json
"pro": {
  "triggers":  {"exit_intent": {"enabled": "true"}, "inactivity": {"seconds": "20"}, "scroll_percent": {"percent": "50"}, "click": {"selector": ".open-newsletter"}},
  "schedule":  {"start": "2026-01-10T00:00", "end": "2026-01-31T23:59", "weekdays": "1,2,3,4,5", "daily_start": "09:00", "daily_end": "21:00"},
  "audience":  {"device": "all|desktop|tablet|mobile", "user_status": "all|anonymous|authenticated", "roles": "", "url_contains": "", "utm_source": "", "session_seconds": "0", "page_views": "0"},
  "frequency": {"max_views": "0", "period_days": "30"},
  "animation": {"entry": "fade|slide-up|slide-down|zoom", "exit": "fade|slide-down|zoom", "duration": "350"},
  "analytics": {"enabled": "true", "conversion_selector": ".btn-claim"}
}
```

Analytics events go to `noahs_popups_pro_events` (report at `/admin/structure/noahs/popup-analytics`). Empty that
table before handing a site over.
