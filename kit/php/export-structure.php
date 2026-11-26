<?php

/**
 * @file
 * Writes the saved structure (settings JSON) of a Noahs design to work/struct-<key>.json, for node/page-css.js.
 * Usage (via noahs.sh css): php:script export-structure.php -- <entity_type> <id> <langcode> <key>
 */

[$entity_type, $id, $langcode, $key] = $extra;
$db = \Drupal::database();

switch ($entity_type) {
  case 'popups':
    $q = $db->select('noahs_page_builder_popups', 'p')->fields('p', ['settings'])
      ->condition('noahs_id', $id)->condition('langcode', $langcode);
    break;

  case 'pro_theme':
    $q = $db->select('noahs_page_builder_pro_themes', 't')->fields('t', ['settings'])
      ->condition('type', $id)->condition('langcode', $langcode);
    break;

  case 'pro_display':
    [$et, $bundle, $vm] = explode(':', $id) + [NULL, NULL, NULL];
    $q = $db->select('noahs_page_builder_pro_designs', 'd')->fields('d', ['settings'])
      ->condition('entity_type', $et)->condition('entity', $bundle)->condition('display', $vm)
      ->condition('langcode', [$langcode, ''], 'IN')->orderBy('langcode', 'DESC');
    break;

  default:
    $q = $db->select('noahs_page_builder_page', 'd')->fields('d', ['settings'])
      ->condition('noahs_id', $id)->condition('entity_type', $entity_type)->condition('langcode', $langcode);
}

$json = $q->range(0, 1)->execute()->fetchField();
if (!$json) {
  throw new \RuntimeException("No saved Noahs design for $entity_type $id ($langcode). Save the structure first.");
}
file_put_contents(dirname(__DIR__) . "/work/struct-$key.json", $json);
echo "Structure exported: " . strlen($json) . " bytes." . PHP_EOL;
