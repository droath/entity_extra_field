<?php

namespace Drupal\entity_extra_field\Entity;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\Annotation\ConfigEntityType;
use Drupal\Core\Entity\EntityWithPluginCollectionInterface;
use Drupal\Core\Plugin\DefaultSingleLazyPluginCollection;
use Drupal\Core\Plugin\PluginDependencyTrait;
use Drupal\Core\Url;
use Drupal\entity_extra_field\ExtraFieldTypePluginInterface;

/**
 * Define entity extra field.
 *
 * @ConfigEntityType(
 *   id = "entity_extra_field",
 *   label = @Translation("Extra Field"),
 *   admin_permission = "administer entity extra field",
 *   config_prefix = "extra_field",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   handlers = {
 *     "form" = {
 *       "add" = "\Drupal\entity_extra_field\Form\EntityExtraFieldForm",
 *       "edit" = "\Drupal\entity_extra_field\Form\EntityExtraFieldForm",
 *       "delete" = "\Drupal\entity_extra_field\Form\EntityExtraFieldFormDelete"
 *     },
 *     "list_builder" = "\Drupal\entity_extra_field\Controller\EntityExtraFieldListBuilder"
 *   }
 * )
 */
class EntityExtraField extends ConfigEntityBase implements EntityWithPluginCollectionInterface, EntityExtraFieldInterface {

  use PluginDependencyTrait;

  /**
   * @var string
   */
  public $id;

  /**
   * @var string
   */
  public $name;

  /**
   * @var string
   */
  public $label;

  /**
   * @var string
   */
  public $description;

  /**
   * @var array
   */
  public $display = [];

  /**
   * @var string
   */
  public $field_type_id;

  /**
   * @var array
   */
  public $field_type_config = [];

  /**
   * @var string
   */
  public $base_entity_type_id;

  /**
   * @var string
   */
  public $base_bundle_type_id;

  /**
   * {@inheritdoc}
   */
  public function id() {
    if (empty($this->name)
      || empty($this->base_entity_type_id)
      || empty($this->base_bundle_type_id)) {
      return NULL;
    }

    return "{$this->base_entity_type_id}.{$this->base_entity_type_id}.{$this->name}";
  }

  /**
   * {@inheritdoc}
   */
  public function name() {
    return $this->name;
  }

  /**
   * {@inheritdoc}
   */
  public function description() {
    return $this->description;
  }

  /**
   * {@inheritdoc}
   */
  public function getDisplay() {
    return $this->display;
  }

  /**
   * {@inheritdoc}
   */
  public function getDisplayType() {
    $display = $this->getDisplay();

    return isset($display['type'])
      ? $display['type']
      : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldTypePluginId() {
    return $this->field_type_id;
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldTypePluginConfig() {
    return $this->field_type_config;
  }

  /**
   * {@inheritdoc}
   */
  public function getBaseEntityTypeId() {
    return $this->base_entity_type_id;
  }

  /**
   * {@inheritdoc}
   */
  public function getBaseBundleTypeId() {
    return $this->base_bundle_type_id;
  }

  /**
   * {@inheritdoc}
   */
  public function getBaseEntityType() {
    return $this->entityTypeManager()->getDefinition(
      $this->getBaseEntityTypeId()
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getBaseEntityTypeBundle() {
    $entity_type = $this->getBaseEntityType();

    return $this->entityTypeManager()->getDefinition(
      $entity_type->getBundleEntityType()
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheDiscoveryId() {
    $langcode = $this->languageManager()->getCurrentLanguage()->getId();
    return "entity_bundle_extra_fields:{$this->getBaseEntityTypeId()}:{$this->getBaseBundleTypeId()}:{$langcode}";
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $field_type_plugin = $this->getFieldTypePlugin();

    if (!$field_type_plugin instanceof ExtraFieldTypePluginInterface) {
      return [];
    }

    return [
      '#field' => $this,
      '#theme' => 'entity_extra_field',
      'content' => $field_type_plugin->build()
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function exists($name) {
    return (bool) $this->getQuery()
      ->condition('id', "{$this->getBaseEntityTypeId()}.{$this->getBaseBundleTypeId()}.{$name}")
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function toUrl($rel = 'edit-form', array $options = []) {
    $base_route_name = $this->getBaseRouteName();
    $route_parameters = $this->urlRouteParameters($rel);

    switch ($rel) {
      case 'collection':
        return URL::fromRoute($base_route_name, $route_parameters, $options);
      case 'add-form':
        return Url::fromRoute("{$base_route_name}.add", $route_parameters, $options);
      case 'edit-form':
        return Url::fromRoute("{$base_route_name}.edit", $route_parameters, $options);
      case 'delete-form':
        return Url::fromRoute("{$base_route_name}.delete", $route_parameters, $options);
    }

    throw new \RuntimeException(
      sprintf('Unable to find %s to built a URL.', $rel)
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginCollections() {
    $field_type_plugin_id = $this->getFieldTypePluginId();

    if (!isset($field_type_plugin_id) || empty($field_type_plugin_id)) {
      return [];
    }

    return [
      new DefaultSingleLazyPluginCollection(
        \Drupal::service('plugin.manager.extra_field_type'),
        $this->getFieldTypePluginId(),
        $this->getFieldTypePluginConfig()
      )
    ];
  }

  /**
   * Get field type plugin instance.
   *
   * @return ExtraFieldTypePluginInterface
   *   The extra field type plugin.
   */
  protected function getFieldTypePlugin() {
    return \Drupal::service('plugin.manager.extra_field_type')
      ->createInstance($this->getFieldTypePluginId(), $this->getFieldTypePluginConfig());
  }

  /**
   * {@inheritdoc}
   */
  protected function urlRouteParameters($rel) {
    $base_bundle_type_id = $this->getBaseEntityTypeBundle()->id();

    $uri_route_parameters = [];
    $uri_route_parameters[$base_bundle_type_id] = $this->getBaseBundleTypeId();

    switch ($rel) {
      case 'edit-form':
      case 'delete-form':
        $uri_route_parameters[$this->getEntityTypeId()] = $this->id();
        break;
    }

    return $uri_route_parameters;
  }

  /**
   * {@inheritdoc}
   */
  protected function linkTemplates() {
    $templates = [];
    $ui_base_path = $this->getBaseEntityBundleUiPath();

    $entity_type = $this->getEntityType();
    $entity_handlers = $entity_type->getHandlerClasses();

    if (isset($entity_handlers['form'])) {
      foreach (array_keys($entity_handlers['form']) as $rel) {
        $template_path = "{$ui_base_path}/extra-fields";

        switch ($rel) {
          case 'add':
            $template_path = "{$template_path}/{$rel}";
            break;
          case 'edit':
          case 'delete':
            $template_path = "{$template_path}/{" . $entity_type->id() . "}/{$rel}";
            break;
        }
        $templates[$rel . '-form'] = $template_path;
      }
    }
    $templates['collection'] = "{$ui_base_path}/extra-fields";

    return $templates;
  }

  /**
   * Get base entity bundle UI path.
   *
   * @return bool|string|null
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getBaseEntityBundleUiPath() {
    $base_route = $this
      ->getBaseEntityType()
      ->get('field_ui_base_route');

    if (!isset($base_route)) {
      return NULL;
    }

    $base_route_rel = strtr(
      substr($base_route, strrpos($base_route, '.') + 1),
      ['_' => '-']
    );
    $base_entity_bundle = $this->getBaseEntityTypeBundle();

    if (!$base_entity_bundle->hasLinkTemplate($base_route_rel)) {
      return NULL;
    }

    return $base_entity_bundle->getLinkTemplate($base_route_rel);
  }

  /**
   * Get base entity route name.
   *
   * @return string
   *   The base entity route.
   */
  protected function getBaseRouteName() {
    return "entity.{$this->getBaseEntityTypeId()}.extra_fields";
  }

  /**
   * Get entity storage query.
   *
   * @return \Drupal\Core\Entity\Query\QueryInterface
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getQuery() {
    return $this->getStorage()->getQuery();
  }

  /**
   * Get entity storage.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   *
   * @return \Drupal\Core\Entity\EntityStorageInterface
   */
  protected function getStorage() {
    return $this->entityTypeManager()
      ->getStorage($this->getEntityTypeId());
  }
}
