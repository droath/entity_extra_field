<?php

namespace Drupal\entity_extra_field;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Core\Plugin\PluginFormInterface;

/**
 * Define extra field type plugin interface.
 */
interface ExtraFieldTypePluginInterface extends PluginFormInterface, ConfigurablePluginInterface {

  /**
   * Build the render array of the extra field type contents.
   *
   * @return array
   *   The extra field renderable array.
   */
  public function build();
}
