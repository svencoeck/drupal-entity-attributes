<?php

namespace Drupal\entity_attributes\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\kint\Plugin\Devel\Dumper\Kint;

/**
 * Provides a field type of Entity Attributes.
 *
 * @FieldType(
 *   id = "entity_attributes",
 *   category = @Translation("Custom"),
 *   label = @Translation("Entity Attributes"),
 *   default_formatter = "entity_attributes_default_formatter",
 *   default_widget = "entity_attributes_default_widget"
 * )
 */
class EntityAttributes extends FieldItemBase implements FieldItemInterface
{

    /**
     * @inheritdoc
     */
    public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition)
    {
        $properties = [];
        $properties['value'] = DataDefinition::create('string')
            ->setLabel(t('Entity Attributes'));

        return $properties;
    }

    /**
     * @inheritdoc
     */
    public function isEmpty()
    {
        return $this->value === NULL || $this->value === '';
    }

    /**
     * @inheritdoc
     */
    public static function schema(FieldStorageDefinitionInterface $field_definition)
    {
        return [
            'columns' => [
                'value' => [
                    'type' => 'blob',
                    'size' => 'normal',
                    'not null' => FALSE,
                ],
            ],
            'indexes' => [],
        ];
    }
}
