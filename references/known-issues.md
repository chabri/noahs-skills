# Known issues and gotchas (Noahs 4.0.x)

Generic pitfalls found while building sites by code. Some may be fixed in newer versions: re-check with
`noahs-discover` when something here does not match.

## Data and rendering

- The Slideshow widget id is `naohs_slideshow` (typo in the module). Keep it.
- Widget defaults contain placeholder copy and sample images: replace every text; search the front end for
  "Lorem", "My button", "Elit" and module image paths (`modules/*/noahs*/assets/img`) during QA.
- A column directly inside another column is not rendered: wrap it in a nested row.
- Widget ids must be unique per HTML page: header, footer, blocks and page are rendered together, so prefix seeds per
  design (`hdr-`, `ftr-`, `home-`).
- Saving a structure by code does not create CSS: run `noahs.sh css …` (or open the editor and Save).
- After installing a module that adds widget options (Webform, Commerce, Pro) or updating Noahs, regenerate defaults.
- Run `drush updb` after updating Noahs (e.g. the menu link field `noahs_megamenu_block` comes from an update hook).
- Settings form: the generated CSS may omit `h3` from the heading font rule — also set `font_h3` in Global styles.
- Global styles have no H6 and no link color; links rendered by Drupal (body fields, Node Field) use the theme color.
- The Text widget has no text alignment: use a Heading with `heading_type: div` for centered paragraphs.
- The Icon widget link has a default bottom margin; in rows of icons set it to 0.
- Social Icons: `icon_list_vertical` checked means a horizontal list.
- Slideshow height, content width and per-slide overlay are not responsive.
- Countdown with empty date counts to "today + 2 days".
- Overlay gradients need numeric `location` values (`"0"`, `"100"`); empty locations produce invalid CSS.

## Drupal integration

- The View widget lists block displays only. If a page alias collides with a view page path, delete the page display.
- Core Contact module owns `/contact`: if `/contact` is a Noahs page, use Webform and uninstall core Contact.
- Untranslated sites with a second language: use `und` aliases, or `/xx/alias` returns 404.
- Pages rendered by Noahs replace the whole content region: page title and status messages are not shown.
- When checking forms by curl (add to cart, cart), BigPipe replaces them with placeholders: send cookie `big_pipe_nojs=1`.
- If logged-in pages suddenly return 500 after updating Noahs files, rebuild caches/router (`drush cr`).

## Free mega menu

- Panels only render with the "Noahs Menu" block, not with core's menu block.
- For full-width panels set the block's navigation and wrapper positions to `static`.
- The referenced block must be reusable, published and have a saved Noahs design.

## Pro

- Display builder teasers may lose widget classes and motion attributes (CSS works). Animate the View widget or its
  section instead.
- Some Pro widgets read their image from the wrong path in some releases (Feature Blocks, Price List `design_2`):
  check the front end and prefer other widgets if the default image shows.
- Header `header_padding` can lose against the Pro header CSS; `header_on_scroll_*` values do apply.
- Widget visibility by URL parameter or cookie (`param_controls`, `cookie_control`) is not cache-aware in some
  versions: do not use it on cached public pages.
- Popups Pro analytics events may be rejected for anonymous users in some releases (popups still work).
- `noahs_popups` "content_type" condition may never match in some releases: use `single_page` with a list of nids.

## Tooling

- DDEV/Lando run drush inside a container that only sees the project: keep the kit and its `work/` folder inside the
  project and run `noahs.sh` from the project root.
- Local HTTPS certificates are not trusted by Node/curl: `noahs.sh` relaxes TLS checks for `*.ddev.site`,
  `*.lndo.site`, `localhost`, `*.test` (or set `NOAHS_INSECURE=1`).
- If the default language has a URL prefix (`/en`), unprefixed URLs redirect; the kit follows redirects.
- Build scripts run in the site's PHP (often 8.1–8.3): avoid newer syntax.
- In bash loops `while read … < file`, drush/ddev read stdin and eat the remaining lines (`noahs.sh` uses `</dev/null`).
