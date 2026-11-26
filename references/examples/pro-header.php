<?php

/**
 * @file
 * Example (PRO): a sticky site header with the Theme builder — logo, main menu (Menu widget, mega-menu ready),
 * cart (if Commerce is enabled) and a call-to-action button. Uses only widget controls.
 *
 * Usage:
 *   noahs.sh run noahs-build/examples/pro-header.php                 → saves the site-wide "header" template
 *   noahs.sh css pro_theme header
 *
 *   noahs.sh run noahs-build/examples/pro-header.php /some-path      → saves a header VARIANT shown only on /some-path
 *   noahs.sh css pro_theme example_header_variant                       (handy to try it without touching the real header)
 *
 * Remove the variant again with: noahs.sh run noahs-build/examples/pro-header.php --delete
 */

require_once __DIR__ . '/../kit/php/lib.php';
noahs_require_pro();

$arg = $extra[0] ?? '';
$variant_type = 'example_header_variant';
if ($arg === '--delete') {
  noahs_delete_theme($variant_type);
  noahs_theme_conditions_delete($variant_type);
  echo "Header variant removed." . PHP_EOL;
  return;
}

$ink = '#111827';
$accent = '#2563EB';
$seed = $arg ? 'hdrv' : 'hdr';

$logo = noahs_widget('noahs_drupal_site_logo', "$seed-logo", noahs_resp('logo_width.text', ['140px', NULL, '116px']));

$nav = noahs_widget('noahs_drupal_nav', "$seed-nav", [
  'menu' => 'main',
  'menu_responsive' => 'true',
  'menu_breakpoint' => '960',
  // "static" positions let mega-menu panels span the header width (the header is the positioned ancestor).
  'css.desktop.default.navigation_position' => 'static',
  'css.desktop.default.navigation_wrapper_position' => 'static',
  'css.desktop.default.element_position' => 'static',
  'css.desktop.default.menu_horizontal_align' => 'center',
  'css.desktop.default.burger_horizontal_align' => 'flex-end',
  'css.desktop.default.group_links_gap.text' => '4px',
  'css.desktop.default.group_links_btn_padding' => noahs_box('padding', '10px', '14px', '10px', '14px'),
  'css.desktop.default.group_links_active_color' => $accent,
] + noahs_font('group_links_font', ['font_size' => '16px', 'font_weight' => '500', 'color' => $ink])
  + noahs_font('group_links_font', ['color' => $accent], 'desktop', 'hover'));

$actions = [];
if (\Drupal::moduleHandler()->moduleExists('commerce_cart') && noahs_has_widget('noahs_drupal_cart')) {
  $actions[] = noahs_widget('noahs_drupal_cart', "$seed-cart", [
    // The cart icon lives under class.icon (not "icon").
    'class.icon' => ['class' => 'fa-solid fa-bag-shopping', 'fid' => '', 'url' => 'fa-solid fa-bag-shopping'],
    'css.desktop.default.icon_size.text' => '20px',
    'css.desktop.default.icon_color' => $ink,
    'css.desktop.default.count_background_color' => $accent,
    'css.desktop.default.count_color' => '#FFFFFF',
  ]);
}
$actions[] = noahs_widget('noahs_button', "$seed-cta", [
  'button_title.text' => 'Get started',
  'button_url' => noahs_url_value('/contact'),
  'css.desktop.default.button_horizontal_align' => 'flex-end',
  'css.desktop.default.btn_padding' => noahs_box('padding', '10px', '22px', '10px', '22px'),
  // Hide the button below the tablet breakpoint (the burger takes its place).
  'css.tablet.default.display_element' => 'none',
]);

$row = noahs_section("$seed-row", [
  noahs_column("$seed-c-logo", [$logo], ['calc(20% - 16px)', 'calc(40% - 12px)', 'calc(50% - 8px)'], [
    'css.desktop.default.elements_align' => 'center',
  ]),
  noahs_column("$seed-c-nav", [$nav], ['calc(55% - 16px)', 'calc(20% - 12px)', 'calc(20% - 8px)'], [
    'css.desktop.default.elements_align' => 'center',
    'css.desktop.default.element_position' => 'static',
  ]),
  noahs_column("$seed-c-actions", $actions, ['calc(25% - 16px)', 'calc(40% - 12px)', 'calc(30% - 8px)'], [
    // Widgets in one line, aligned to the right. Set wrap on every breakpoint to avoid overflow on phones.
    'css.desktop.default.column_elements_inline' => 'row',
    'css.desktop.default.elements_align' => 'flex-end',
    'css.desktop.default.elements_vertical_align' => 'center',
    'css.desktop.default.elements_gap.text' => '18px',
  ]),
], [
  'css.desktop.default.section_columns_gap.text' => '24px',
  'css.mobile.default.section_columns_gap.text' => '12px',
  'css.desktop.default.columns_position' => 'center',
  'css.desktop.default.padding' => noahs_box('padding', '14px', '15px', '14px', '15px'),
]);

// Template settings: sticky header that gets a translucent background and a soft shadow once the page scrolls.
$settings = [
  'class' => ['header_layout' => 'header_sticky'],
  'hide_on_scroll_down' => 'off',
  'header_sticky_scroll_offset' => ['text' => '40px', 'extra_value' => ''],
  'css' => ['desktop' => ['default' => [
    'header_bg_color' => '#FFFFFF',
    'header_border' => noahs_border('#E5E7EB', '0px', '0px', '1px', '0px'),
    'header_on_scroll_bg_color' => 'rgba(255,255,255,0.92)',
    'header_on_scroll_box_shadows' => noahs_shadow('0px', '8px', '24px', '0px', 'rgba(17,24,39,0.08)'),
  ]]],
];

if ($arg) {
  noahs_save_theme($variant_type, [$row], $settings);
  noahs_theme_conditions($variant_type, 'Example header (variant)', 'header', ['include_paths' => $arg], 10, TRUE);
  echo "Saved header variant '$variant_type' for $arg. Next: noahs.sh css pro_theme $variant_type" . PHP_EOL;
}
else {
  noahs_save_theme('header', [$row], $settings);
  echo "Saved site header. Next: noahs.sh css pro_theme header" . PHP_EOL;
}
