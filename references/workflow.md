# Workflow: building a site with Noahs

Follow the order. Each step relies on the previous one.

## 0. Preflight

1. Find how drush runs: DDEV (`ddev drush`), Lando (`lando drush`), plain server (`vendor/bin/drush`), multisite
   (`… drush @alias` or `-l <uri>`). Find the site URL.
2. Copy the kit into the project (containers only see project files) and generate defaults:
   ```bash
   mkdir -p noahs-build && cp -R "<plugin>/kit" "<plugin>/references/examples" noahs-build/
   echo "noahs-build/kit/work/" >> .gitignore; echo "noahs-build/kit/node/node_modules/" >> .gitignore
   noahs-build/kit/noahs.sh env        # versions, pro true/false, enabled bundles, widgets
   noahs-build/kit/noahs.sh defaults   # real widget defaults → work/defaults.json (needs Node 18+)
   ```
3. Decide **free or Pro** from `env` (`pro`, `popups_pro`). Free → only FREE widgets and free techniques.
4. Make sure Noahs is enabled for the bundles you will design (`noahs_enabled_on`), `drush updb` has run, and the
   default theme is `noahs_theme` or a sub-theme of it (recommended).

## 1. Brief

Pages, sections per page, palette (primary, secondary, 3–6 custom colors), two fonts, container width, tone of copy,
images needed. Map every section to widgets (see `design-tips.md`). Copy is original and in English unless asked.
When recreating a reference site, recreate **structure and style only**: never copy its texts, images or logos.

## 2. Settings

`noahs.sh settings spec.json` (see `examples/settings.json`): colors, custom colors, fonts (`available_fonts`,
`heading_font`, `general_font`), container width, breakpoints, `use_editor`, `use_in_*`. Re-run when new bundles exist.

## 3. Global styles

`noahs.sh global-styles spec.json` (see `examples/global-styles.json`): H1–H5 (+ tablet/mobile sizes), body, buttons
(`button_*` and `button_*_style_1…4` variants), form labels and inputs, pager, carousel arrows. Run `dry` first to see
the CSS. These styles are what every page inherits: get them right before building pages.

## 4. Header and footer

- **Pro**: Theme builder `header` and `footer` templates (`examples/pro-header.php`), with sticky settings and the Menu
  widget (mega-menu ready). Variants for special pages with conditions.
- **Free**: blocks in the theme regions of `noahs_theme` (header_*, footer_*). Each piece is a reusable Noahs block
  (`noahs_save_block()`) or a system block (site branding, "Noahs Menu" block for menus and mega menus, Commerce cart).
  Layout tweaks of the theme belong to a small sub-theme, not to widget custom CSS.

## 5. Templates

Listings and details for the content types the site needs (blog, news, products…):

- Listings: a Noahs page with the **View** widget showing a block display of a Drupal view (filters, sorting and
  pager work). Pro can also design the teaser with the Display builder.
- Details: free → Twig in the sub-theme + reusable Noahs blocks by bundle; Pro → per-bundle Theme builder template
  (`blog`, `commerce_default`…) with Node Field / Commerce Field / Product Price / Add to cart.
- Commerce (Pro): `commerce_cart` and `commerce_checkout_oder_info` templates; account pages `noahs_login`… if wanted.

## 6. Pages

One idempotent PHP script per page (skill `noahs-page`), then `noahs.sh css node <nid>`.

## 7. QA

- Each URL returns 200 with no PHP warnings/errors (`drush ws --severity=Error`), the page CSS file is linked or
  included in the aggregate, no placeholder text ("Lorem", "My button", default widget images) remains.
- Desktop (1440), tablet (768) and phone (390): no horizontal scroll, readable sizes, buttons wrap well.
- The editor still opens the page (`/noahs_edit/node/<nid>`) and the content can be edited visually.
- Free sites: no PRO widget ids in any stored structure; links work; images are original or properly licensed.
