<?php

namespace Drupal\entity_extra_field\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;

/**
 * Define entity extra field list builder.
 */
class EntityExtraFieldListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    return [
        'label' => $this->t('Label'),
      ] + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    return [
        'label' => $entity->label()
      ] + parent::buildRow($entity);
  }
}
