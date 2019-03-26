<?php

namespace Drupal\entity_extra_field\Entity;

/**
 * Define entity extra field interface.
 */
interface EntityExtraFieldInterface {

  /**
   * Get extra field machine name.
   *
   * @return string
   *   The extra field machine name.
   */
  public function name();

  /**
   * Build the extra field.
   *
   * @return array
   *   A renderable array.
   */
  public function build();

  /**
   * Get the extra field description.
   *
   * @return string
   *   The extra field description.
   */
  public function description();

  /**
   * Get extra field display.
   *
   * @return array
   *   An array of display information.
   */
  public function getDisplay();

  /**
   * Get extra field display type.
   *
   * @return string
   *   Get the display type.
   */
  public function getDisplayType();

  /**
   * Get field type plugin identifier.
   *
   * @return string
   *   The field type plugin identifier.
   */
  public function getFieldTypePluginId();

  /**
   * Get field type plugin configuration
   *
   * @return array
   *   An array of the plugin configuration.
   */
  public function getFieldTypePluginConfig();

  /**
   * Get base entity type id.
   *
   * @return string
   *   The base entity type identifier.
   */
  public function getBaseEntityTypeId();

  /**
   * Get base bundle type id.
   *
   * @return string
   *   A base bundle type id.
   */
  public function getBaseBundleTypeId();

  /**
   * Get base entity type instance.
   *
   * @return \Drupal\Core\Entity\EntityTypeInterface|null
   *   The entity type instance.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getBaseEntityType();

  /**
   * Get base entity type bundle instance.
   *
   * @return \Drupal\Core\Entity\EntityTypeInterface|null
   *   The entity type bundle instance.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getBaseEntityTypeBundle();

  /**
   * Get the cache discovery identifier.
   *
   * @return string
   *   The cache identifier in the cache_discovery table.
   */
  public function getCacheDiscoveryId();

  /**
   * Check if entity identifier exist.
   *
   * @param $name
   *   The entity machine name.
   *
   * @return int
   *   Return TRUE if machine name exist; otherwise FALSE.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function exists($name);
}
