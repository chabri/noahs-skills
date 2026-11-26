<?php

/**
 * @file
 * Prints a JSON summary of the site: versions, Noahs modules (free / Pro), where Noahs is enabled, widgets.
 * Usage: noahs.sh env
 */

$mh = \Drupal::moduleHandler();
$version = function (string $package): ?string {
  if (class_exists('\Composer\InstalledVersions') && \Composer\InstalledVersions::isInstalled($package)) {
    return \Composer\InstalledVersions::getPrettyVersion($package);
  }
  return NULL;
};
$cfg = \Drupal::config('noahs_page_builder.settings');

$widgets = [];
if (\Drupal::hasService('plugin.manager.widget') && $mh->moduleExists('noahs_page_builder')) {
  foreach (\Drupal::service('plugin.manager.widget')->getDefinitions() as $id => $def) {
    $widgets[$def['provider'] ?? 'unknown'][] = $id;
  }
  foreach ($widgets as &$list) {
    sort($list);
  }
}

$modules = [];
foreach (['noahs_page_builder', 'noahs_page_builder_pro', 'noahs_popups', 'noahs_popups_pro', 'noahs_gallery', 'noahs_mail', 'noahs_ai', 'commerce', 'webform', 'language'] as $m) {
  $modules[$m] = $mh->moduleExists($m);
}

echo json_encode([
  'drupal' => \Drupal::VERSION,
  'noahs_version' => $version('drupal/noahs'),
  'pro' => $modules['noahs_page_builder_pro'],
  'popups_pro' => $modules['noahs_popups_pro'],
  'modules' => $modules,
  'default_theme' => \Drupal::config('system.theme')->get('default'),
  'default_langcode' => \Drupal::languageManager()->getDefaultLanguage()->getId(),
  'noahs_enabled_on' => [
    'node_types' => array_keys(array_filter((array) $cfg->get('use_in_ctype'))),
    'vocabularies' => array_keys(array_filter((array) $cfg->get('use_in_vtype'))),
    'product_types' => array_keys(array_filter((array) $cfg->get('use_in_products'))),
    'block_types' => array_keys(array_filter((array) $cfg->get('use_in_btypes'))),
  ],
  'colors' => ['primary' => $cfg->get('principal_color'), 'secondary' => $cfg->get('secondary_color')],
  'fonts' => ['heading' => $cfg->get('heading_font'), 'body' => $cfg->get('general_font')],
  'widgets' => $widgets,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
