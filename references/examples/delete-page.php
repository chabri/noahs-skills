<?php

/**
 * @file
 * Example: delete a page created by code (node + Noahs design + CSS) and the media created for it.
 * Usage: noahs.sh run noahs-build/examples/delete-page.php /example-landing ["Example landing hero"]
 */

require_once __DIR__ . '/../kit/php/lib.php';

$alias = $extra[0] ?? '';
$media_name = $extra[1] ?? '';
$path = $alias ? \Drupal::service('path_alias.repository')->lookupByAlias($alias, 'en') : NULL;
if ($path && preg_match('#^/node/(\d+)$#', $path['path'], $m)) {
  noahs_delete_page('node', $m[1]);
  \Drupal::entityTypeManager()->getStorage('node')->load($m[1])?->delete();
  echo "Deleted node {$m[1]} ($alias)." . PHP_EOL;
}
else {
  echo "No node at $alias." . PHP_EOL;
}
if ($media_name !== '') {
  foreach (\Drupal::entityTypeManager()->getStorage('media')->loadByProperties(['bundle' => 'noahs_image', 'name' => $media_name]) as $media) {
    $file = $media->get('field_media_image')->entity;
    $media->delete();
    $file?->delete();
    echo "Deleted media '$media_name'." . PHP_EOL;
  }
}
