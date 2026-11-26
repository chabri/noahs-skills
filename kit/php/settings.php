<?php

/**
 * @file
 * Applies Noahs Settings (config noahs_page_builder.settings) and regenerates general_settings.css.
 * Usage: noahs.sh settings <spec.json> [dry]   — spec = keys of noahs_page_builder.settings (see references/data-model.md).
 *
 * 1. Writes the spec keys into the config (lists such as custom_color / custom_fonts are replaced as a whole).
 * 2. Submits NoahsSettingsForm programmatically with no input and op = "Save configuration": every field falls back
 *    to its #default_value (read from the config), so submitForm() rebuilds the CSS exactly like the UI does.
 */

use Drupal\Core\Form\FormState;
use Drupal\noahs_page_builder\Form\NoahsSettingsForm;

$spec = json_decode(file_get_contents(dirname(__DIR__) . '/work/' . ($extra[0] ?? 'settings-spec.json')), TRUE);
if (!is_array($spec)) {
  throw new \RuntimeException('Settings spec is not valid JSON.');
}

$config = \Drupal::configFactory()->getEditable('noahs_page_builder.settings');
$data = $config->getRawData();
foreach ($spec as $key => $value) {
  $data[$key] = $value;
}
// The Text widget needs an editor format, otherwise the programmatic submit may fail validation.
$data['use_editor'] = $data['use_editor'] ?? 'full_html';
if (($extra[1] ?? 'apply') === 'dry') {
  echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
  return;
}
$config->setData($data)->save();

$form_state = new FormState();
$form_state->setValues(['op' => (string) t('Save configuration')]);
\Drupal::formBuilder()->submitForm(NoahsSettingsForm::class, $form_state);
if ($errors = $form_state->getErrors()) {
  foreach ($errors as $name => $error) {
    echo "ERROR [$name]: " . strip_tags((string) $error) . PHP_EOL;
  }
  throw new \RuntimeException('NoahsSettingsForm did not validate: check the spec values.');
}
$css = 'public://noahs/noahs_settings/general_settings.css';
echo 'Settings saved. general_settings.css: ' . (file_exists($css) ? filesize($css) . ' bytes' : 'MISSING') . PHP_EOL;
