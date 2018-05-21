<?php

/**
 * @param $entity
 * @return array
 */
function hook_entity_attributes_sets_info($entity) {
    $attributeSets = [];

    /** @var Drupal\Core\Entity\Entity $entity */
    if ($entity->getEntityType()->id() == 'paragraph' && $entity->bundle() == 'paragraph_title') {
        $attributeSets['green'] = t('Green');
        $attributeSets['red'] = t('Red');
    }

    return $attributeSets;
}

function hook_entity_attributes_set($set, $entity) {
    $attributes = [];

    /** @var Drupal\Core\Entity\Entity $entity */
    $entityType = $entity->getEntityType()->id();
    $bundle = $entity->bundle();

    if ($set == 'green' && $entityType == 'paragraph' && $bundle == 'paragraph_title') {
        $attributes += ['class' => ['text-green']];
    }

    if ($set == 'hidden') {
        $attributes += ['class' => ['element-invisible']];
    }

    return $attributes;
}