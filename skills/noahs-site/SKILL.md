---
name: noahs-site
description: Build or restyle a complete Drupal site with Noah's Page Builder (drupal/noahs, machine name noahs_page_builder), free or Pro - brief, Settings, Global styles, header and footer, templates, pages and QA - working on any Drupal with drush (DDEV, Lando, plain server). Use when the user asks to create a site, a demo, a landing set, or to "build my site with Noahs"; detects Noahs Pro and uses Pro features only when it is installed.
---

# Build a site with Noah's Page Builder

Files of this plugin live under `${CLAUDE_PLUGIN_ROOT}` (in a standalone skill zip: this skill's folder).
Load references on demand, not all at once:

| When | Read |
|---|---|
| Before writing any structure | `${CLAUDE_PLUGIN_ROOT}/references/data-model.md` |
| Choosing widgets / field paths | `${CLAUDE_PLUGIN_ROOT}/references/widgets-catalogue.md` |
| Pro site only | `${CLAUDE_PLUGIN_ROOT}/references/pro-features.md` |
| Designing sections | `${CLAUDE_PLUGIN_ROOT}/references/design-tips.md` |
| Something behaves oddly | `${CLAUDE_PLUGIN_ROOT}/references/known-issues.md` |
| Step details | `${CLAUDE_PLUGIN_ROOT}/references/workflow.md` |
| Working code | `${CLAUDE_PLUGIN_ROOT}/references/examples/` |

## Rules (always)

1. **No custom CSS.** Use widget controls, Settings and Global styles. Leave "Custom CSS" fields empty. If a look needs
   CSS, simplify it or tell the user which control is missing.
2. **Pro only if installed.** Check `noahs.sh env`. If `"pro": false`, never use PRO widgets, Theme builder, Display
   builder or motion extras (see the catalogue's FREE/PRO marks). Do not suggest pirated or copied Pro code.
3. **Never invent formats.** Field names come from `work/defaults.json` of the installed version and the references.
   If something is missing or contradicts the site, run the `noahs-discover` skill first.
4. **Idempotent scripts.** Every change is a PHP script run with drush that finds-or-creates by alias / name and uses
   stable seeds (`noahs_wid()`), so it can be re-run safely. Keep scripts in `noahs-build/` of the project.
5. **Original content in English** (unless the user asks otherwise). When recreating a reference site copy structure
   and style only — never its texts, images or logos. Replace every default widget text.
6. **Ask before destructive changes** to existing pages, Settings or templates the user already has: show what will
   change, and prefer a new page or a header variant limited to a test path for trials.
7. Commands are run **from the Drupal project root** (folder with `composer.json`). If you cannot run them (no shell
   access to the environment), give the user the exact commands and ask for the output.

## Step 0 — Preflight

1. Detect the environment: `.ddev/` → `ddev drush`; `.lando.yml` → `lando drush`; else `vendor/bin/drush`. For a
   multisite add the alias or `-l <uri>`. Find the site URL (`drush status --field=uri`).
2. Install the kit inside the project (containers only see the project folder):
   ```bash
   mkdir -p noahs-build && cp -R "${CLAUDE_PLUGIN_ROOT}/kit" "${CLAUDE_PLUGIN_ROOT}/references/examples" noahs-build/
   printf 'noahs-build/kit/work/\nnoahs-build/kit/node/node_modules/\n' >> .gitignore
   export NOAHS_DRUSH="ddev drush"            # or "lando drush", "vendor/bin/drush -l example.com", "ddev drush @site"
   export NOAHS_URL="https://mysite.ddev.site"
   noahs-build/kit/noahs.sh env               # JSON: drupal, noahs_version, pro, popups_pro, enabled bundles, widgets
   noahs-build/kit/noahs.sh defaults          # real widget defaults (needs Node 18+ and npm; installs jsdom locally)
   ```
3. Report to the user: Drupal and Noahs versions, **free or Pro**, enabled bundles, theme. Check prerequisites:
   Noahs enabled on the bundles you need, `drush updb` done, theme `noahs_theme` or a sub-theme (recommended for
   free headers/footers), media type `noahs_image` present.

## Step 1 — Brief

Agree on (or derive from a reference URL/screenshots): site name and purpose, page list, sections per page,
palette (primary, secondary, 3–6 custom colors), two fonts from Noahs' bundled list (Inter, Poppins, Montserrat,
Playfair Display, Jost…; others as custom fonts with relative URLs), container width, image needs, Commerce / blog /
news needs. Map each section to widgets (`design-tips.md`), marking FREE vs PRO. Save it as
`noahs-build/brief.md` and confirm with the user before building.

## Step 2 — Settings

Write `noahs-build/settings.json` (start from `examples/settings.json`): colors, `custom_color`, `available_fonts`,
`heading_font`, `general_font`, `container_width`, `viewport_tablet/mobile`, `use_editor: full_html`,
`use_in_ctype` / `use_in_products` / `use_in_btypes` (only bundles that exist). Then:
`noahs.sh settings noahs-build/settings.json dry` → review → `noahs.sh settings noahs-build/settings.json`.

## Step 3 — Global styles

Write `noahs-build/global-styles.json` (start from `examples/global-styles.json`): body, H1–H5 with tablet and mobile
sizes, buttons + hover, up to 4 button variants (`button_*_style_1…4`), inputs and labels, pager, carousel arrows.
`noahs.sh global-styles … dry` to read the CSS, then apply. Settings must be applied first.

## Step 4 — Header and footer

- **Pro**: Theme builder `header` / `footer` (`examples/pro-header.php`): logo, Menu widget (`static` positions for
  mega menus), cart (if Commerce), CTA; template settings for sticky + on-scroll styles. To try a header without
  replacing the live one, save a **variant** limited to a test path (the example supports it), then promote it.
  CSS: `noahs.sh css pro_theme header`.
- **Free**: blocks in `noahs_theme` regions. Design each piece as a reusable Noahs block (`noahs_save_block()` →
  `noahs.sh css block_content <id>`) and place it with the block system; menus with the "Noahs Menu" block
  (responsive burger, mega-menu panels from `noahs_megamenu_block`). Theme-level layout fixes go in a small
  sub-theme only if the user agrees — never in widget custom CSS.

## Step 5 — Templates

For each content type the site needs (blog, news, products, events…):
- Listing page: Noahs page + **View** widget on a **block display** (create one if missing; remove a conflicting
  page display). Pro: design the teaser with the Display builder (`noahs_save_design()`, view mode must exist).
- Detail: free → sub-theme Twig + reusable Noahs blocks shown by bundle; Pro → Theme builder template whose `type`
  is the bundle (`blog`, `commerce_<product type>`) using Node Field / Commerce Field / Product Price / Add to cart.
- Pro + Commerce: `commerce_cart`, `commerce_checkout_oder_info`; account templates if requested.
Create at least 6 sample items per listing so it looks real.

## Step 6 — Pages

Use the `noahs-page` skill for each page (home first). One script per page in `noahs-build/pages/`, then
`noahs.sh css node <nid>`.

## Step 7 — QA (before saying "done")

For every page and template URL:
1. `curl -sL -o /dev/null -w '%{http_code}' <url>` → 200; `drush ws --count=20 --severity=Error` shows nothing new.
2. The design's CSS is linked (`noahs/<entity_type>/noahs_<id>_<lang>.css` or inside the aggregated CSS).
3. No placeholder text or images: grep the HTML for `Lorem`, `My button`, `Elit Eiusmod`, `assets/img/` of Noahs modules.
4. Free site: `grep` the stored structures for PRO widget ids and `motion_` attributes — there must be none.
5. Visual check at 1440, 768 and 390 px if a browser/screenshot tool is available (no horizontal scroll, sizes OK).
6. The editor opens: `/noahs_edit/node/<nid>` loads and shows the sections (the user can keep editing visually).

Finish with a short report: pages built (URLs), what is free vs Pro, scripts written, anything the user must do by
hand (e.g. upload a logo, review copy).
