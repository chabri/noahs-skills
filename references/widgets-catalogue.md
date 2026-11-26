# Widgets catalogue

Widget ids as stored in `type`. **FREE** = `noahs_page_builder` (drupal.org). **PRO** = `noahs_page_builder_pro`:
never use a PRO widget on a site where `noahs.sh env` reports `"pro": false`. Snapshot of Noahs 4.0.x; the
authoritative list for a given site is `noahs.sh env` → `widgets`, and fields come from `work/defaults.json`.

## Layout

| id | Label | License | Key paths in `element` |
|---|---|---|---|
| `noahs_row` | Section | FREE | `class.section_container` (`n-container` boxed / `n-container-fluid` full width), `css.<bp>.default.padding`, `section_columns_gap.text`, `section_min_height.text`, `columns_position` (`''`, `stretch`, `center`, `flex-start`, `flex-end`: vertical alignment of columns), `columns_horizontal_align`, `bg_color`, `bg_image`, `bg_image_overlay`, `bg_gradient`, `overflow`, `border`, `widget_border_radius` |
| `noahs_column` | Column | FREE | `css.<bp>.default.column_width` (string, `calc(50% - 15px)`, `auto`), `section_min_height.text`, `column_elements_inline` (`row` = widgets side by side), `column_elements_wrap`, `elements_align`, `elements_vertical_align`, `horizontal_align` (text-align), `elements_gap.text`, `bg_color`, `bg_image`, `bg_image_overlay`, `bg_gradient`, `padding`, `border`, `widget_border_radius`, `element_position` |

Every widget also has: `margin`, `padding`, `border`, `display_element` (per breakpoint, `none` hides),
`custom_width_element.text` (max width), `widget_box_shadows`, `widget_border_radius`, `element_position` (+
coordinates), `index`, `class.element_class.text`, and the Pro transform/motion extras.

## Content (FREE)

| id | Label | Key paths / notes |
|---|---|---|
| `noahs_heading` | Heading | `heading_text.text` (tokens allowed), `heading_type` (`h1`…`h6`, `div`), `css.desktop.default.text_align`, `horizontal_align`, `font` (responsive), `class.heading_custom_class.text`. Use `heading_type: div` for centered paragraphs (Text has no alignment control). |
| `noahs_text` | Text | `text` (HTML, rendered as is), `font` (+ `font.styles.<h1…h5,p,a…>`), `text_column` |
| `noahs_plain_text` | Plain Text | plain text |
| `noahs_button` | Button | `button_title.text`, `button_url` (noahs_url), `class.button_style` (`btn-style-1…4` from Global styles), `button_icon`, `css.<bp>.default.button_horizontal_align`, `background_color` (+ hover), `font`, `button_border`, `btn_padding`, `button_radius`, `attribute.button_target` |
| `noahs_image` | Image | `image` (noahs_image), alignment, width, radius |
| `noahs_icon` | Icon | `icon` (`{class, fid, url}`), `icon_url`, `css.desktop.default.color`, `horizontal_align`, `icon_size.text`, `box_width/height`, `background_color`. The icon link has a default bottom margin: set `margin` to 0 when icons sit in a row. |
| `noahs_icon_list` | Icon List | `icon_list.element_N = {icon_title.text, icon_text, icon}`, `icon_size.text`, `icon_color`, `font_title`, `font_text` |
| `noahs_network_icons` | Social Icons | repeatable networks; note `icon_list_vertical` checked = **horizontal** list |
| `noahs_separator` | Separator | line / spacer |
| `noahs_video` | Video | YouTube/Vimeo/local |
| `noahs_plain_code` | Code | raw HTML/embed |
| `noahs_stars` | Stars | rating |
| `noahs_bars` | Skill Bars | progress bars |
| `noahs_countdown` | Countdown | `countdown_time` (`2026-12-31 23:59:00`; empty = today + 2 days), labels `countdown_days/hours/minutes/seconds.text` |
| `noahs_accordion` | Accordion | `accordion_items.element_N = {accordion_title.text, accordion_text}`, `heading_font`, `text_font`; center with `custom_width_element` + auto margins |
| `noahs_tabs` | Tabs | repeatable tabs |
| `noahs_image_comparer` | Image Comparer | before / after |

## Sliders and cards (FREE)

| id | Label | Key paths / notes |
|---|---|---|
| `naohs_slideshow` (sic) | Slideshow | `slideshow_items.element_N.{slideshow_title.text, slideshow_text, slideshow_link_text.text, slideshow_url}`; per-slide background `css.desktop.default.slideshow_items.slideshow_image.element_N` (noahs_background_image), overlay `…bg_image_overlay.element_N`, alignment `…content_text_align.element_N` / `…content_horizontal_align.element_N`; `slideshow_height.text`, `slideshow_container.text`, `slideshow_content_width.text`; fonts `heading_font`, `text_font`, `btn_font`; own button styles `btn_*`. The id typo is real: keep it. The title renders **before** the text (use it as eyebrow). |
| `noahs_carousel` | Carousel | image carousel |
| `noahs_services_slide` | Slideshow Services | service cards slider |
| `noahs_card` | Card | `card_style` (`overlay`…), `image`, `card_title.text`, `text` (set it: empty shows sample text), `link_text.text`, `link_url` |
| `noahs_card_grid` | Cards Grid | `slideshow_items.element_N = {card_image, slideshow_title.text, slideshow_text.text, button_title.text, slideshow_url, single_slideshow_icon}`, `carousel_type` (`grid`/`slide`/`overlay`), `carousel_grid_columns(_tablet/_mobile)`, `section_grid_gapx/gapy.text`, `heading_font`, `text_font` |
| `noahs_card_grid_scroll` | Card Grid Scroll | horizontally scrolling cards |
| `noahs_gallery` | Image Gallery | `gallery_items = {"0": {fid, url, description}}`, `gallery_type_columns(_tablet/_mobile)`, `remove_fancybox: "1"`; a link per item only with an absolute URL |

## Drupal integration (FREE)

| id | Label | Key paths / notes |
|---|---|---|
| `noahs_drupal_views` | Drupal Views | `drupal_block` = JSON string `["view_id","block_display_id"]`, `contextual_arguments` (tokens), `show_items`, `hide_pagination`, `hide_exposed_filter`. Block displays only; exposed filters and pager work via the query string of the current URL. |
| `noahs_drupal_block` | Drupal Block | `drupal_block = "configured:<block id>"` or a plugin id |
| `noahs_drupal_block_content` / `noahs_block` | Block content / Noahs Block | embed a content block / a reusable Noahs block |
| `noahs_insert_node` | Insert Node | render another node |
| `noahs_drupal_title`, `noahs_drupal_site_title`, `noahs_drupal_site_logo`, `noahs_drupal_token` | titles, logo (`logo_width.text`), tokens | |
| `noahs_drupal_webform` | Webform | `form` = webform id (field appears only with Webform installed: regenerate defaults) |
| `noahs_paragraph_select` | Paragraph Select | paragraphs |
| `noahs_settings` | Noahs Settings | internal: the Global styles widget |

## PRO widgets

| id | Label | Use |
|---|---|---|
| `noahs_drupal_nav` | Drupal Menu | `menu`, `menu_responsive`, `menu_breakpoint`, `group_links_*`, `group_sub_links_*`, `submenu_*`, `burger_*`, `navigation_position` / `navigation_wrapper_position` (mega menu: `static`) |
| `noahs_drupal_nav_icon` | Drupal Icon Menu | icon that opens a menu (`icon`, `menu`, `box_links_*`) |
| `noahs_drupal_lang_switcher` | Language Switcher | `menu_lang_code`, `menu_dropdown`, `hide_current`, `font`, `dropdown_*` |
| `noahs_drupal_cart` | Cart | `class.icon`, `show_total`, `icon_*`, `count_*` (+ `*_sticky`), side cart `title/price/btn/box_*` |
| `noahs_drupal_cart_block`, `noahs_drupal_checkout`, `noahs_drupal_checkout_progress` | Cart Block, Checkout Flow, Checkout progress | cart and checkout templates |
| `noahs_drupal_product`, `noahs_drupal_products` | Product, Multiple Products | hand-picked products (`noahs_commerce_products`, `section_grid`) |
| `noahs_drupal_product_price`, `noahs_add_to_cart`, `noahs_commerce_field`, `noahs_commerce_variation_field` | product template / teaser pieces | |
| `noahs_node_field` | Drupal Node Field | `drupal_field`, `node_view.text`, `wrapp_element`, `preview_options` |
| `noahs_drupal_nodes`, `noahs_drupal_nodes_slides` | Multiple Nodes (+ Slideshow) | hand-picked nodes, grid or carousel |
| `noahs_main_page_content`, `noahs_theme_content`, `noahs_drupal_region`, `noahs_maintenance_message` | layout slots | |
| `noahs_drupal_user_login`, `noahs_drupal_register_form`, `noahs_reset_pass_form`, `noahs_drupal_user`, `noahs_drupal_tabs` | account templates | |
| `noahs_drupal_contact_form`, `noahs_drupal_pro_webform` | Contact Form, Advanced Webform | |
| `noahs_heading_animated` | Heading Animated | `animate_type` (`rotate-1`, `letters type`, `push`, `clip is-full-width`, `zoom`…), `text_before.text`, `animated_text` (`;`-separated), `text_after.text`, `font`, `animated_font` — also the no-CSS way to style one accent word differently |
| `noahs_icon_step_pro` | Icon Steps | `icon_list` (repeatable), `section_grid` |
| `noahs_timeline` | Timeline | `timeline_list` |
| `noahs_price_list` | Price List | `design` (`design_1..3`), tag, price/currency/period, features, button |
| `noahs_review_pro` | Reviews | `testimonial_items`, `testimonial_type` (`slider`/`grid`), columns |
| `noahs_feature_blocks` | Feature Blocks | features around a central image |
| `noahs_video_button_pro` | Video Button | play button with pulse (`video_url`, `video_animations`) |

Recent Pro versions may also include `noahs_marquee`, `noahs_counter`, `noahs_scroll_progress` (see `pro-features.md`).
