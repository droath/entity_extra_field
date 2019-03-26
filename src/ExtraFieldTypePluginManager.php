<?php

namespace Drupal\entity_extra_field;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Define the extra field type plugin manage.
 */
class ExtraFieldTypePluginManager extends DefaultPluginManager {

  /**
   * {@inheritdoc}
   */
  public function __construct(
    \Traversable $namespaces,
    CacheBackendInterface $cache_backend,
    \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
  ) {
    parent::__construct(
      'Plugin/ExtraFieldType',
      $namespaces,
      $module_handler,
      '\Drupal\entity_extra_field\ExtraFieldTypePluginInterface',
      '\Drupal\entity_extra_field\Annotation\ExtraFieldType'
    );

    $this->alterInfo('extra_field_type_info');
    $this->setCacheBackend($cache_backend, 'extra_field_type');
  }

  /**
   * {@inheritdoc}
   */
  public function createInstance($plugin_id, array $configuration = []) {
    return parent::createInstance($plugin_id, $configuration);
  }
}
