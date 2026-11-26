<?php

/**
 * @file
 * Example: a reusable hero section (FREE widgets only). Included by create-page.php.
 *
 * Full-width section with a background image and dark overlay, a boxed column with eyebrow, H1, intro text and
 * two buttons. Every value is set through widget controls (no custom CSS).
 */

/**
 * @param string $seed  Unique prefix for widget ids (one per page/design).
 * @param \Drupal\media\Entity\Media|null $media  Background image (noahs_image media) or NULL for a solid color.
 */
function example_hero(string $seed, array $copy, $media = NULL, bool $pro = FALSE): array {
  $white = '#FFFFFF';

  $eyebrow = noahs_widget('noahs_heading', "$seed-eyebrow", [
    'heading_text.text' => $copy['eyebrow'],
    'heading_type' => 'div',
    'css.desktop.default.text_align' => 'center',
  ] + noahs_font('font', ['font_size' => '14px', 'font_weight' => '600', 'letter_spacing' => '3px', 'text_transform' => 'uppercase', 'color' => $white]));

  $title = noahs_widget('noahs_heading', "$seed-title", [
    'heading_text.text' => $copy['title'],
    'heading_type' => 'h1',
    'css.desktop.default.text_align' => 'center',
  ] + noahs_font('font', ['font_size' => '64px', 'line_height' => '1.1', 'color' => $white])
    + noahs_font('font', ['font_size' => '46px'], 'tablet')
    + noahs_font('font', ['font_size' => '36px'], 'mobile')
    + ($pro ? noahs_motion_enter('fade-up', 900) : []));

  $intro = noahs_widget('noahs_heading', "$seed-intro", [
    // Heading with tag "div" for a centered paragraph: the Text widget has no text-align control.
    'heading_text.text' => $copy['intro'],
    'heading_type' => 'div',
    'css.desktop.default.text_align' => 'center',
    'css.desktop.default.custom_width_element.text' => '640px',
    'css.desktop.default.margin' => noahs_box('margin', '0px', 'auto', '0px', 'auto'),
  ] + noahs_font('font', ['font_size' => '19px', 'line_height' => '1.6', 'color' => 'rgba(255,255,255,0.85)'])
    + ($pro ? noahs_motion_enter('fade-up', 900, 150) : []));

  $primary = noahs_widget('noahs_button', "$seed-btn1", [
    'button_title.text' => $copy['primary_label'],
    'button_url' => noahs_url_value($copy['primary_url']),
  ]);
  $secondary = noahs_widget('noahs_button', "$seed-btn2", [
    'button_title.text' => $copy['secondary_label'],
    'button_url' => noahs_url_value($copy['secondary_url']),
    'css.desktop.default.background_color' => 'rgba(255,255,255,0)',
    'css.desktop.hover.background_color' => $white,
    'css.desktop.default.button_border' => noahs_border($white, '2px', '2px', '2px', '2px'),
  ] + noahs_font('font', ['color' => $white]) + noahs_font('font', ['color' => '#111111'], 'desktop', 'hover'));

  // Buttons side by side: a nested row with two auto-width columns, centered.
  $buttons = noahs_inner("$seed-buttons", [
    noahs_column("$seed-buttons-c1", [$primary], ['auto', 'auto', '100%']),
    noahs_column("$seed-buttons-c2", [$secondary], ['auto', 'auto', '100%']),
  ], [
    'css.desktop.default.section_columns_gap.text' => '16px',
    'css.desktop.default.columns_horizontal_align' => 'center',
    'css.desktop.default.margin' => noahs_box('margin', '32px', '0px', '0px', '0px'),
  ]);

  $set = [
    'class.section_container' => 'n-container-fluid',
    'css.desktop.default.bg_color' => '#111827',
    'css.desktop.default.columns_position' => 'center',
  ] + noahs_resp('section_min_height.text', ['640px', '560px', '480px'])
    + noahs_resp('padding', [
      noahs_box('padding', '120px', '24px', '120px', '24px'),
      noahs_box('padding', '90px', '24px', '90px', '24px'),
      noahs_box('padding', '70px', '20px', '70px', '20px'),
    ]);
  if ($media) {
    $set['css.desktop.default.bg_image'] = noahs_background_value($media);
    $set['css.desktop.default.bg_image_overlay'] = ['overlay' => [
      'type' => 'vertical',
      'start' => ['color' => 'rgba(17,24,39,0.55)', 'location' => '0'],
      'end' => ['color' => 'rgba(17,24,39,0.85)', 'location' => '100'],
    ]];
  }

  return noahs_section("$seed-section", [
    noahs_column("$seed-col", [$eyebrow, $title, $intro, $buttons], ['100%'], [
      'css.desktop.default.custom_width_element.text' => '960px',
      'css.desktop.default.margin' => noahs_box('margin', '0px', 'auto', '0px', 'auto'),
    ]),
  ], $set);
}
