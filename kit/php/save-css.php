<?php

/**
 * @file
 * Saves the CSS (and page settings) produced by node/page-css.js, the same way the editor's Save button does.
 * Usage (via noahs.sh css): php:script save-css.php -- <entity_type> <id> <langcode> <key>
 */

use Drupal\Core\Cache\Cache;
use Drupal\noahs_page_builder\Controller\NoahsController;

[$entity_type, $id, $langcode, $key] = $extra;
$work = dirname(__DIR__) . '/work';
$css = file_get_contents("$work/css-$key.css");
$ps_file = "$work/ps-$key.json";
$ps = is_file($ps_file) ? file_get_contents($ps_file) : NULL;
$db = \Drupal::database();

// The CSS file id used by saveCss(): pro_display designs are stored as <entity_type>_<bundle>_<view_mode>.
$css_id = $entity_type === 'pro_display' ? str_replace(':', '_', $id) : $id;
\Drupal::classResolver(NoahsController::class)->saveCss($entity_type, $css_id, $langcode, $css);

if ($ps !== NULL) {
  switch ($entity_type) {
    case 'popups':
      // Popups store the serialized settings form without the {page_settings, page_fields} wrapper.
      $decoded = json_decode($ps, TRUE);
      $db->update('noahs_page_builder_popups')->fields(['page_settings' => json_encode($decoded['page_settings'] ?? [])])
        ->condition('noahs_id', $id)->condition('langcode', $langcode)->execute();
      break;

    case 'pro_theme':
      $db->update('noahs_page_builder_pro_themes')->fields(['page_settings' => $ps])
        ->condition('type', $id)->condition('langcode', $langcode)->execute();
      break;

    case 'pro_display':
      [$et, $bundle, $vm] = explode(':', $id);
      $db->update('noahs_page_builder_pro_designs')->fields(['page_settings' => $ps])
        ->condition('entity_type', $et)->condition('entity', $bundle)->condition('display', $vm)
        ->condition('langcode', [$langcode, ''], 'IN')->execute();
      break;

    default:
      $db->update('noahs_page_builder_page')->fields(['page_settings' => $ps])
        ->condition('noahs_id', $id)->condition('entity_type', $entity_type)->condition('langcode', $langcode)->execute();
  }
}

Cache::invalidateTags(['rendered', 'noahs_theme_templates', "$entity_type:$id"]);
@unlink("$work/css-$key.css");
@unlink($ps_file);
@unlink("$work/struct-$key.json");
echo "CSS saved: public://noahs/$entity_type/noahs_{$css_id}_$langcode.css (" . strlen($css) . " bytes)." . PHP_EOL;
