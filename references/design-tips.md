# Design tips: common patterns with Noahs widgets

**Rule: no custom CSS.** Style everything with widget controls (normal / hover, desktop / tablet / mobile), Settings
and Global styles. Leave the widget "Custom CSS" field and the global custom CSS empty. If a look cannot be reached
with controls, simplify the design or tell the user which control is missing instead of adding CSS.

Where each style belongs, in order of preference:

1. **Settings** — brand colors, custom colors, fonts, container width, breakpoints.
2. **Global styles** — H1–H5, body, buttons and their 4 variants, inputs, labels, pager, carousel arrows.
3. **Widget controls** — backgrounds, gradients, overlays, borders (+ hover), radius, shadows, spacing, fonts,
   alignment, position (sticky/static), visibility per device.
4. **Pro extras** (Pro only) — entrance, cascade, scroll-linked effects, floating, hero scene, hover transform.

## Layout basics

- Boxed content: row `class.section_container = n-container` (width from Settings). Full-bleed: `n-container-fluid`.
- Column widths with a gap: subtract the gap share, e.g. 3 columns + 30px gap → `calc(33.333% - 20px)`; general
  formula `calc(X% - gap*(n-1)/n)`. Always give tablet and mobile widths (`['…', '50%-ish', '100%']`).
- Vertical rhythm: section padding about 90/70/50 px (desktop/tablet/mobile).
- Equal-height cards: row `columns_position: stretch`.
- Side-by-side widgets in one column: `column_elements_inline: row` (+ `elements_gap`, alignment). Set
  `column_elements_wrap: wrap` on **every** breakpoint or phones may overflow.
- Inner layouts: nest a row inside a column (`noahs_inner()`); never put a column directly in a column.
- Anchors: link to `#widget-id-<wid>` of the target section.

## Pattern → widgets

| Pattern | FREE | PRO extra |
|---|---|---|
| Hero with background image + CTA | Row with `bg_image` + `bg_image_overlay` (gradient) + `section_min_height`; column with heading `div` eyebrow, `h1`, heading `div` intro, buttons in a nested row | `motion_enter: fade-up` on titles; `motion_hero: shrink` on the row; `motion_scroll: depth` on images |
| Hero slider | `naohs_slideshow` (title = eyebrow, text = headline; per-slide background and overlay) | |
| Feature grid (3–4) | Columns with icon + heading `h3` + text, background, border, radius | `motion_cascade` on the row; hover `noahs_element_transform` translate_y −4px |
| Logos strip | Row of image widgets, `column_elements_inline: row` | Marquee widget if available |
| Testimonials | `noahs_card_grid` (`carousel_type: slide`) or columns with text + stars | `noahs_review_pro` |
| Pricing table | Columns with headings, icon list and button; highlight one with background + shadow | `noahs_price_list` |
| FAQ | `noahs_accordion` centered (`custom_width_element` 820px + auto margins) | |
| Gallery | `noahs_gallery` (6/3/2 columns) | |
| Contact | Webform widget in a column with background + `noahs_icon_list` with contact data | `noahs_drupal_contact_form` |
| Stats / counters | Headings with big numbers | Counter widget if available |
| Category mosaic (1 big + 3) | Left column with `bg_image` + button; right column with a nested row of 3 background columns (100% + 2×50%) | |
| Blog / product listing | `noahs_drupal_views` with a block display (filters, sort, pager) | Display builder teaser + rendered-entity view |
| Featured post + list | Two block displays of one view (featured: 1 item; list: offset 1 + pager) | |
| Page header (title band) | Row with `bg_image`, column min-height 300/260/220, `h1` + subtitle | page template in Theme builder |
| Header / menu | Blocks in theme regions, "Noahs Menu" block (responsive) | Theme builder header + Menu widget |
| Mega menu | Reusable Noahs block referenced by the menu link (`noahs_megamenu_block`), rendered by the Noahs Menu block with `static` positions | Menu widget with `static` positions |
| Footer | Noahs blocks + menu blocks in footer regions | Theme builder footer |
| Newsletter / promo popup | `noahs_popups`: image + text + button; trigger on load / scroll; close delay | exit intent, schedule, audience, analytics |
| Pill button with gradient (no CSS) | Column `auto` width with `bg_gradient` + radius 999px + `overflow: hidden`, transparent button inside | |
| Accent word in a title (no CSS) | — | `noahs_heading_animated` with `text_before` / `animated_text` / `text_after` and a second font |

## Copy and images

- Write original, specific copy in English (unless asked otherwise). Replace every default text.
- Images: original, AI-generated or properly licensed stock; keep a credits list. Use a consistent style per site.
- Optimize images (WebP, ~1920px wide for heroes, ~800px for cards). Image controls accept an `image_style`.

## Responsive checklist

- Font sizes per breakpoint for big titles (e.g. 64 / 46 / 36 px).
- Hide decorative elements on phones with `display_element: none` in `css.mobile.default`.
- Horizontal entrance animations cause sideways scroll on phones: use `fade-up` or devices `no-mobile`.
