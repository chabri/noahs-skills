<?php

/**
 * @file
 * Dumps the HTML form of every installed widget, exactly as the editor requests it when a widget is dropped
 * (GET /noahs-admin/widget-form/{id}). node/defaults.js serializes those forms into real default values.
 * Usage: noahs.sh defaults
 */

$work = dirname(__DIR__) . '/work';
$ws = \Drupal::service('noahs_page_builder.widget_service');
$modal = \Drupal::service('noahs_page_builder.modal_form');
$forms = [];
foreach (array_keys($ws->getAllWidgetsList()) as $id) {
  $form = $modal->renderForm($ws->getWidgetFields($id), ['widget_id' => $id]);
  $forms[$id] = '<form>' . (is_array($form) ? reset($form) : $form) . '</form>';
}
file_put_contents("$work/widget-forms.json", json_encode($forms));
echo count($forms) . ' widget forms dumped.' . PHP_EOL;
