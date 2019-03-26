<?php

namespace Drupal\entity_extra_field;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\PluginBase;

/**
 * Define extra field type plugin base.
 */
class ExtraFieldTypePluginBase extends PluginBase implements ExtraFieldTypePluginInterface {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(
    array $form, FormStateInterface $form_state
  ) {
    $form['#prefix'] = '<div id="extra-field-plugin">';
    $form['#suffix'] = '</div>';

    $form['#parents'] = ['field_type_config'];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(
    array &$form,
    FormStateInterface $form_state
  ) {
    // Intentionally left empty on base class.
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(
    array &$form,
    FormStateInterface $form_state
  ) {
    $this->configuration = $form_state->cleanValues()->getValues();
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = $configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configuration + $this->defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return [];
  }

  /**
   * Get extra field plugin ajax.
   *
   * @param array $form
   *   An array of form elements.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state instance.
   *
   * @return array
   *   An array of form elements.
   */
  public function extraFieldPluginAjaxCallback(
    array $form,
    FormStateInterface $form_state
  ) {
    return $form['field_type_config'];
  }

  /**
   * Get extra field plugin ajax properties.
   *
   * @return array
   *   An array of common AJAX plugin properties.
   */
  protected function extraFieldPluginAjax() {
    return [
      'wrapper' => 'extra-field-plugin',
      'callback' => [$this, 'extraFieldPluginAjaxCallback'],
    ];
  }

  /**
   * Get plugin form state value.
   *
   * @param string|array $key
   *   The element key.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state instance.
   * @param null $default
   *   The default value if nothing is found.
   *
   * @return mixed|null
   *   The form value; otherwise FALSE if the value can't be found.
   */
  protected function getPluginFormStateValue(
    $key,
    FormStateInterface $form_state,
    $default = NULL
  ) {
    $key = !is_array($key) ? [$key] : $key;

    $inputs = [
      $form_state->cleanValues()->getValues(),
      $this->getConfiguration()
    ];

    foreach ($inputs as $input) {
      $value = NestedArray::getValue($input, $key, $key_exists);

      if (!isset($value) && !$key_exists) {
        continue;
      }

      return $value;
    }

    return $default;
  }
}
