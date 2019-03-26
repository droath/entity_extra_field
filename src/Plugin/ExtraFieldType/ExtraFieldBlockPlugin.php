<?php

namespace Drupal\entity_extra_field\Plugin\ExtraFieldType;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\SubformState;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\entity_extra_field\Annotation\ExtraFieldType;
use Drupal\entity_extra_field\ExtraFieldTypeBase;
use Drupal\entity_extra_field\ExtraFieldTypePluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Define extra field block type.
 *
 * @ExtraFieldType(
 *   id = "block",
 *   label = @Translation("Block")
 * )
 */
class ExtraFieldBlockPlugin extends ExtraFieldTypePluginBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $blockManager;

  /**
   * Extra field block plugin constructor.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param $plugin_id
   *   The plugin identifier.
   * @param $plugin_definition
   *   The plugin definition.
   * @param \Drupal\Core\Block\BlockManagerInterface $block_manager
   *   The block manager.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    BlockManagerInterface $block_manager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->blockManager = $block_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    return new static (
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.block')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'block_type' => NULL,
      'block_config' => [],
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $block_type = $this->getPluginFormStateValue('block_type', $form_state);

    $form['block_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Block Type'),
      '#required' => TRUE,
      '#options' => $this->getBlockTypeOptions(),
      '#empty_option' => $this->t('- Select -'),
      '#ajax' => [
        'event' => 'change',
        'method' => 'replace',
      ] + $this->extraFieldPluginAjax(),
      '#default_value' => $block_type,
    ];

    if (isset($block_type) && !empty($block_type)) {
      if ($this->blockManager->hasDefinition($block_type)) {
        $block_config = $this->getPluginFormStateValue('block_config', $form_state, []);
        $block_instance = $this->blockManager->createInstance($block_type, $block_config);

        if ($block_instance instanceof PluginFormInterface) {
          $form['block_config'] = [
            '#type' => 'fieldset',
            '#title' => $this->t('Block Configuration'),
            '#tree' => TRUE,
          ];
          $subform = ['#parents' => array_merge(
            $form['#parents'], ['block_config']
          )];

          $form['block_config'] += $block_instance->buildConfigurationForm(
            $subform,
            SubformState::createForSubform($subform, $form, $form_state)
          );
        }
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::validateConfigurationForm($form, $form_state);

    $block_instance = $this->getBlockTypeInstance();

    if ($block_instance !== FALSE) {
      if ($block_instance instanceof PluginFormInterface) {
        $subform = ['#parents' => array_merge(
          $form['#parents'], ['block_config']
        )];

        $block_instance->validateConfigurationForm(
          $subform,
          SubformState::createForSubform($subform, $form, $form_state)
        );
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    $block_instance = $this->getBlockTypeInstance();

    if ($block_instance !== FALSE) {
      if ($block_instance instanceof PluginFormInterface) {
        $subform = ['#parents' => array_merge(
          $form['#parents'], ['block_config']
        )];

        $block_instance->submitConfigurationForm(
          $subform,
          SubformState::createForSubform($subform, $form, $form_state)
        );
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return $this->getBlockTypeInstance()->build();
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    $dependencies = [];

    $block_type_instance = $this->getBlockTypeInstance();

    if ($block_type_instance !== FALSE) {
      $plugin_definition = $block_type_instance->getPluginDefinition();

      // Due to the plugin definition not being an instance with a
      // \Drupal\Component\Plugin\Definition\PluginDefinitionInterface, so we
      // aren't able to use the \Drupal\Core\Plugin\PluginDependencyTrait. If
      // this changes in the future then update.
      if (is_array($plugin_definition)) {
        if (isset($plugin_definition['config_dependencies'])) {
          $dependencies = array_merge_recursive(
            $dependencies,
            $plugin_definition['config_dependencies']
          );
        }

        if (isset($plugin_definition['provider'])) {
          $dependencies['module'][] = $plugin_definition['provider'];
        }
      }
    }

    return $dependencies;
  }

  /**
   * Get block type instance.
   *
   * @return bool|\Drupal\Core\Block\BlockBase
   *   The block instance; otherwise FALSE if type is not defined.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  protected function getBlockTypeInstance() {
    $config = $this->getConfiguration();

    if (!isset($config['block_type'])) {
      return FALSE;
    }

    return $this->blockManager->createInstance(
      $config['block_type'],
      $config['block_config']
    );
  }

  /**
   * Get block type options.
   *
   * @param array $excluded_ids
   *   An array of block ids to exclude.
   *
   * @return array
   *   An array of block type options.
   */
  protected function getBlockTypeOptions($excluded_ids = []) {
    $options = [];

    // There are a couple block ids that are excluded by default as either
    // they're not really needed, or they are causing problems when selected.
    $excluded_ids = [
      'broken',
      'system_branding_block',
    ] + $excluded_ids;

    foreach ($this->blockManager->getDefinitions() as $block_id => $definition) {
      if (!isset($definition['admin_label']) || in_array($block_id, $excluded_ids)) {
        continue;
      }
      $category = isset($definition['category'])
        ? $definition['category']
        : $this->t('Undefined');

      $options[(string)$category][$block_id] = $definition['admin_label'];
    }

    return $options;
  }
}
