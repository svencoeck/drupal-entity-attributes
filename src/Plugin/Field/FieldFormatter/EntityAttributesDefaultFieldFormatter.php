<?php
declare(strict_types=1);

namespace Drupal\entity_attributes\Plugin\Field\FieldFormatter;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;


/**
 * Plugin implementation of the 'Entity Attributes' formatter.
 *
 * @FieldFormatter(
 *   id = "entity_attributes_default_formatter",
 *   label = @Translation("Entity Attributes"),
 *   field_types = {
 *     "entity_attributes"
 *   }
 * )
 */
class EntityAttributesDefaultFieldFormatter extends FormatterBase
{

    /**
     * @inheritdoc
     */
    public function viewElements(FieldItemListInterface $items, $langcode)
    {
        $elements = [];
        foreach ($items as $delta => $item) {
            $elements[$delta] = [
                '#type' => 'markup',
                '#markup' => t("")
            ];
        }
        return $elements;
    }
}