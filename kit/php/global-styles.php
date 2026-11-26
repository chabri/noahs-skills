<?php

/**
 * @file
 * Applies Noahs Global styles (the "noahs_settings" widget edited at /noahs-admin/settings-styles).
 * Usage: noahs.sh global-styles <spec.json> [dry]
 *
 * spec = {"type": "noahs_settings", "element": {"css": {"desktop": {"default": {...}, "hover": {...}}, "tablet": …, "mobile": …}}}
 *
 * The CSS is produced by a small PHP port of the editor's CSS generator, limited to the control types used by the
 * noahs_settings widget (font, color, border, padding, margin, radius, shadows, select/number/text). Apply Settings
 * first: colors equal to the primary/secondary/custom colors are written as CSS variables.
 * Afterwards the styles stay editable in the UI (open /noahs-admin/settings-styles and press Save).
 */

use Drupal\Core\Cache\Cache;
use Drupal\noahs_page_builder\Controller\NoahsController;

$settings = json_decode(file_get_contents(dirname(__DIR__) . '/work/' . ($extra[0] ?? 'global-styles-spec.json')), TRUE);
$mode = $extra[1] ?? 'apply';
if (!is_array($settings)) {
  throw new \RuntimeException('Global styles spec is not valid JSON.');
}
$settings['type'] = 'noahs_settings';
$settings['wid'] = 'no_id';
$settings['noahs_id'] = 'noahs_settings';

$fields = \Drupal::service('noahs_page_builder.widget_service')->getWidgetFields('noahs_settings');
$config = \Drupal::config('noahs_page_builder.settings');
$breakpoints = ['tablet' => $config->get('viewport_tablet') ?: '959', 'mobile' => $config->get('viewport_mobile') ?: '767'];

$vars = [];
foreach ((array) $config->get('custom_color') as $c) {
  if (!empty($c['hex'])) {
    $vars['--noahs_page_builder-' . ltrim($c['hex'], '#')] = $c['hex'];
  }
}
$vars['--noahs-primary-color'] = $config->get('principal_color') ?: '#2389ab';
$vars['--noahs-secondary-color'] = $config->get('secondary_color') ?: '#4a4a4a';
$color = fn($v) => ($k = array_search($v, $vars, TRUE)) !== FALSE ? "var($k)" : $v;

$filled = function ($v) use (&$filled): bool {
  if (is_string($v)) {
    return trim($v) !== '';
  }
  if (is_array($v)) {
    foreach ($v as $k => $item) {
      if ($k !== 'input_linked' && $filled($item)) {
        return TRUE;
      }
    }
    return FALSE;
  }
  return is_int($v) || is_float($v);
};

$rules = function (string $state, array $values) use ($fields, $filled, $color): string {
  $out = [];
  foreach ($values as $name => $value) {
    $field = $fields[$name] ?? NULL;
    if (!$field || ($field['style_type'] ?? '') !== 'style' || empty($field['style_selector']) || !$filled($value)) {
      continue;
    }
    // Same selector building as the editor, then the "#widget-id-no_id" prefix is dropped (global selectors).
    $sel = $field['style_selector'];
    $prefix = '#widget-id-no_id';
    if (str_contains($sel, ',')) {
      $selector = implode(', ', array_map(fn($part) => "$prefix $part", explode(',', $sel)));
      $selector = preg_replace('/#widget-id-no_id\s*/', '', $selector);
    }
    else {
      $selector = str_contains($sel, '[widget]') ? str_replace('[widget]', $prefix, $sel) : ($sel === 'widget' ? $prefix : "$prefix $sel");
      $selector = preg_replace('/#widget-id-no_id\s*/', '', $selector, 1);
    }
    $selector = preg_replace('/\s*:\s*(before|after)/', ':$1', $selector);
    if ($state === 'hover') {
      $selector = implode(', ', array_map(fn($p) => "$p:hover", explode(',', $selector)));
    }
    $decl = '';
    switch ($field['type']) {
      case 'noahs_margin':
      case 'noahs_padding':
      case 'noahs_radius':
      case 'noahs_border':
        foreach ($value as $prop => $v) {
          if ($prop !== 'input_linked' && $filled($v)) {
            $decl .= str_replace('_', '-', $prop) . ': ' . (is_numeric($v) ? $v . 'px' : $v) . '; ';
          }
        }
        break;

      case 'noahs_font':
        foreach ($value as $prop => $v) {
          if ($filled($v)) {
            $decl .= str_replace('_', '-', $prop) . ': ' . ($prop === 'color' ? $color($v) : $v) . '; ';
          }
        }
        break;

      case 'noahs_shadows':
        $parts = array_map(fn($k) => trim((string) ($value[$k] ?? '')), ['shadow_x', 'shadow_y', 'blur', 'spread', 'color']);
        foreach ($parts as $i => $p) {
          $parts[$i] = $p !== '' ? $p : ($i === 4 ? 'black' : '0');
        }
        $decl .= 'box-shadow: ' . implode(' ', $parts) . '; ';
        break;

      case 'noahs_color':
        $decl .= $field['style_css'] . ': ' . $color($value) . '; ';
        break;

      case 'select':
      case 'number':
      case 'checkbox':
      case 'noahs_width':
        $decl .= $field['style_css'] . ": $value" . ($field['style_suffix'] ?? '') . '; ';
        break;

      case 'text':
        if (!empty($field['style_css']) && $filled($value['text'] ?? '')) {
          $decl .= $field['style_css'] . ': ' . $value['text'] . ($field['style_suffix'] ?? '') . '; ';
        }
        break;
    }
    if ($decl !== '') {
      $out[$selector][] = $decl;
    }
  }
  $css = '';
  foreach ($out as $selector => $decls) {
    $css .= "$selector { " . implode(' ', $decls) . ' } ';
  }
  return $css;
};

$css = '';
foreach (['desktop', 'tablet', 'mobile'] as $bp) {
  $block = '';
  foreach (($settings['element']['css'][$bp] ?? []) as $state => $values) {
    $block .= $rules($state, (array) $values);
  }
  if ($block !== '') {
    $css .= $bp === 'desktop' ? $block : "@media (max-width: {$breakpoints[$bp]}px) {{$block}}";
  }
}

if ($mode === 'dry') {
  echo $css . PHP_EOL;
  return;
}

$db = \Drupal::database();
$row = ['settings' => json_encode($settings, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 'modified_date' => time()];
$page_id = $db->select('noahs_page_builder_page', 'd')->fields('d', ['page_id'])
  ->condition('noahs_id', 'noahs_settings')->condition('entity_type', 'noahs_settings')
  ->condition('langcode', 'no_language')->execute()->fetchField();
if ($page_id) {
  $db->update('noahs_page_builder_page')->fields($row)->condition('page_id', $page_id)->execute();
}
else {
  $db->insert('noahs_page_builder_page')->fields($row + [
    'noahs_id' => 'noahs_settings', 'entity_id' => 'noahs_settings', 'entity_type' => 'noahs_settings',
    'langcode' => 'no_language', 'uid' => 1,
  ])->execute();
}
\Drupal::classResolver(NoahsController::class)->saveCss('noahs_settings', 'noahs_settings', 'no_language', $css);
Cache::invalidateTags(['rendered', 'noahs_theme_templates']);
echo 'Global styles saved (' . strlen($css) . ' bytes of CSS).' . PHP_EOL;
