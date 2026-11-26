# Noah's Page Builder skills for Claude

A Claude Code plugin that teaches Claude to build Drupal sites with
[Noah's Page Builder](https://www.drupal.org/project/noahs) (machine name `noahs_page_builder`) — the free module
from drupal.org and, when installed, **Noahs Pro** — directly by code:

- Settings (brand colors, fonts, container) and Global styles (headings, body, buttons, forms)
- Header and footer (free: Noahs blocks in theme regions · Pro: Theme builder, sticky header, mega menu)
- Templates for listings and detail pages (free: View widget + blocks · Pro: Theme builder and Display builder)
- Pages built section by section with real widgets, saved exactly as the visual editor saves them, so everything
  stays editable in the Noahs editor afterwards
- QA: status codes, PHP errors, placeholder text, free/Pro compliance, responsive checks

The CSS of every design is generated with **the module's own editor code** (read from the installed version), so the
result is identical to pressing Save in the editor.

More about Noahs: **https://noahs-creator.com**

## Skills

| Skill | What it does |
|---|---|
| `noahs-site` | Full flow: brief → Settings → Global styles → header/footer → templates → pages → QA |
| `noahs-page` | Builds one page with widgets through an idempotent PHP script run with drush, then its CSS |
| `noahs-discover` | Reads the installed Noahs code to verify formats and field names (never guesses) |

They load the reference docs in `references/` only when needed, and use the small build kit in `kit/`.

## Requirements

- Drupal 10.3+ or 11 with `drupal/noahs` 4.x enabled (optional: `noahs_builder_theme`, `noahs_popups`,
  Noahs Pro and Popups Pro from noahs-creator.com)
- Shell access with **drush**: DDEV, Lando, a plain server or a multisite (`drush @alias` / `-l`) all work
- **Node.js 18+** and npm (for widget defaults and CSS generation; `jsdom` is installed locally in the kit)
- `curl`, `bash`
- Claude Code

## Install (Claude Code)

```text
/plugin marketplace add chabri/noahs-skills
/plugin install noahs@noahs-skills
```

or from a terminal: `claude plugin marketplace add chabri/noahs-skills && claude plugin install noahs@noahs-skills`.

Then open Claude Code in your Drupal project root (the folder with `composer.json`). The skills copy the kit into
`noahs-build/` of your project, because containers (DDEV, Lando) only see project files.

### Other Claude surfaces (claude.ai)

Skills can also be uploaded as zip files where your Claude plan supports custom skills (on claude.ai:
*Settings → Capabilities → Skills*). Build self-contained zips with `scripts/build-skill-zips.sh` (output in
`dist/`). Note that these skills run drush and Node commands, so they are most useful where Claude has a shell on the
machine that runs your Drupal site, i.e. Claude Code.

## Example prompts

- "Build a site for a small coffee roastery with Noahs: home, shop, blog, about and contact. Use my DDEV project."
- "Apply a brand palette (#0F766E, #F59E0B) and Inter/Playfair Display to Noahs Settings and Global styles."
- "Create a pricing page with three plans, an FAQ and a call to action, using only free widgets."
- "Recreate the structure of this landing page (screenshot attached) with Noahs, with original copy."
- "We have Noahs Pro: make a sticky header with the main menu, cart and a CTA, and test it on /header-test first."
- "Add a fade-up entrance and a staggered cascade to the features section." (Pro)
- "Check how my installed Noahs version stores popups before you create one."

## Free vs Pro

| | Free (`drupal/noahs`) | Pro (`noahs_page_builder_pro`) |
|---|---|---|
| Widgets | ~40 layout, content, slider, card and Drupal widgets | + menus, cart/checkout, product and node fields, account forms, animated heading, reviews, pricing, timeline… |
| Header / footer | Blocks in theme regions designed with Noahs, Noahs Menu block (mega menu) | Theme builder templates, variants with conditions, sticky / on-scroll styles |
| Templates | One design per entity; Twig + blocks for detail pages | Per-bundle and per-route templates, Display builder for teasers |
| Effects | Hover styles, backgrounds, gradients | Entrance, cascade, scroll-linked, parallax, floating, hero scene, section snap, transforms |
| Popups | `noahs_popups` | + exit intent, schedule, audience, frequency, analytics (Popups Pro) |

The skills detect Pro (`noahs.sh env`) and only use Pro features when the module is enabled. This plugin contains no
code from the Pro modules; it only documents how their data is stored so Claude can fill it in.

## House rules the skills follow

- No custom CSS: everything through widget controls, Settings and Global styles.
- Idempotent scripts with stable widget ids; safe to re-run.
- Original content (English by default); recreating a reference site copies structure and style, never its content.
- Ask before replacing designs the user already has.

## The kit

```text
kit/noahs.sh env | defaults | run <script.php> | settings <spec> [dry] | global-styles <spec> [dry] | css <entity_type> <id>
kit/php/lib.php      helpers: noahs_widget(), noahs_section(), noahs_column(), noahs_save_page(), noahs_save_theme()…
references/examples/ create-page.php, hero-section.php, pro-header.php, settings.json, global-styles.json, delete-page.php
```

Set `NOAHS_DRUSH` (e.g. `ddev drush`, `lando drush`, `vendor/bin/drush -l example.com`) and `NOAHS_URL` when the
defaults do not fit.

## License

GPL-2.0-or-later, like Drupal and Noah's Page Builder. See `LICENSE`.
