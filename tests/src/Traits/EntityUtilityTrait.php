<?php

namespace Drupal\Tests\rdf_entity\Traits;

/**
 * Provide some entity utility methods to be used in tests.
 */
trait EntityUtilityTrait {

  /**
   * Returns an entity by its label.
   *
   * Being used for testing, this method assumes that, within an entity type,
   * all entities have unique labels.
   *
   * @param string $entity_type_id
   *   The entity type ID.
   * @param string $label
   *   The entity label.
   * @param string|null $bundle
   *   (optional) The search can be restricted to a specific bundle.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface
   *   The content entity.
   *
   * @throws \InvalidArgumentException
   *   When the entity type lacks a label key.
   * @throws \Exception
   *   When the entity with the specified label was not found.
   */
  protected function loadEntityByLabel($entity_type_id, $label, $bundle = NULL) {
    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager */
    $entity_type_manager = \Drupal::entityTypeManager();
    $entity_type = $entity_type_manager->getDefinition($entity_type_id);

    if (!$entity_type->hasKey('label')) {
      throw new \InvalidArgumentException("Entity type '$entity_type_id' doesn't have a label key.");
    }
    $storage = $entity_type_manager->getStorage($entity_type_id);
    $query = $storage->getQuery();

    $label_key = $entity_type->getKey('label');
    $query->condition($label_key, $label);

    if ($bundle) {
      if (!$entity_type->hasKey('bundle')) {
        throw new \InvalidArgumentException("A bundle was passed but entity type '$entity_type_id' doesn't have a bundle key.");
      }
      $bundle_key = $entity_type->getKey('bundle');
      $query->condition($bundle_key, $bundle);
    }

    $ids = $query->execute();
    if (empty($ids)) {
      $message = "No $entity_type_id";
      if ($bundle) {
        $message .= " ($bundle_key '$bundle')";
      }
      $message .= " entity with $label_key '$label' was found.";
      throw new \Exception($message);
    }
    $id = reset($ids);

    return $storage->load($id);
  }

}
