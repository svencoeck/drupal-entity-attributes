<?php
declare(strict_types=1);

namespace Drupal\entity_attributes\Helper;

use Drupal\Core\Entity\Entity;

/**
 * Class EntityAttributesHelper
 */
class EntityAttributesHelper
{

    private function emptyAttributes()
    {
        return [
            'sets' => [],
            'class' => [],
            'id' => '',
            'advanced' => [],
        ];
    }

    public function fromJson($json)
    {
        $json = (string)$json;
        $values = $this->emptyAttributes();
        $values = array_merge($values, (array)json_decode($json));

        return $values;
    }

    public function toJson($item)
    {
        $values = $this->emptyAttributes();
        $values['class'] = explode(" ", $item['class']);
        $values['sets'] = isset($item['sets']) ? array_values((array)$item['sets']) : [];
        $values['id'] = $item['id'];
        $values['advanced'] = preg_split("/\r?\n/", $item['advanced']['advanced_attributes']);

        $values = $this->removeEmptyAttributes($values);

        return !empty($values) ? json_encode($values) : '';
    }

    public function prepareAttributes($item, $entity)
    {
        $attributes = [];

        if (!empty($item['class'])) {
            $attributes['class'] = $item['class'];
        }

        if (!empty($item['id'])) {
            $attributes['id'] = $item['id'];
        }

        if (!empty($item['advanced'])) {
            foreach ($item['advanced'] as $advancedAttribute) {
                $advancedAttribute = explode('|', $advancedAttribute, 2);

                switch (count($advancedAttribute)) {
                    case 1:
                        $key = $advancedAttribute[0];
                        $value = true;
                        break;
                    case 2:
                        list($key, $value) = $advancedAttribute;
                }

                $attributes[$key] = $value;
            }
        }

        if (!empty($item['sets'])) {
            foreach ($item['sets'] as $set) {
                $attributes = array_merge_recursive($this->getEntityAttributesBySet($set, $entity), $attributes);
            }
        }

        return $attributes;
    }

    /**
     * @param Entity $entity
     * @return array
     */
    public function getEntityAvailableViewModes($entity)
    {
        $view_modes = \Drupal::service('entity_display.repository')
            ->getViewModes($entity->getEntityType()->id());

        array_walk($view_modes, function(&$item, $key) {
            if ($item['status'] == false) {
                $item = false;
                return;
            }

            if (in_array($key, ['token'])) {
                $item = false;
                return;
            };

            $item = $item['label'];
        });

        return array_filter($view_modes);
    }

    public function getEntityAttributes($entity)
    {
        $fields = \Drupal::service('entity_field.manager')
            ->getFieldDefinitions($entity->getEntityTypeId(), $entity->bundle());

        $attributes = [];
        /** @var \Drupal\field\Entity\FieldConfig $field */
        foreach ($fields as $field) {
            if ($field->getType() == 'entity_attributes') {
                $fieldName = $field->getName();

                $values = $entity->{$fieldName}->getValue();
                foreach ($values as $delta => $value) {
                    $item = $this->fromJson($value['value']);
                    $attributes = array_merge($attributes, $this->prepareAttributes($item, $entity));
                }
            }
        }

        return $attributes;
    }


    public function getDefinedAttributeSets($entity)
    {
        $attributeSets = [];
        $modules = \Drupal::moduleHandler()->getImplementations('entity_attributes_sets_info');
        foreach ($modules as $module) {
            $function = $module . '_entity_attributes_sets_info';
            if (function_exists($function)) {
                $attributeSets += $function($entity);
            }
        }

        $theme = \Drupal::service('theme.manager')->getActiveTheme()->getName();
        $function = $theme . '_entity_attributes_sets_info';
        if (function_exists($function)) {
            $attributeSets += $function($entity);
        }

        \Drupal::moduleHandler()->alter('entity_attributes_sets_info', $attributeSets, $entity);

        return $attributeSets;
    }

    public function getEntityAttributesBySet($set, $entity)
    {
        $attributes = [];
        $modules = \Drupal::moduleHandler()->getImplementations('entity_attributes_set');
        foreach ($modules as $module) {
            $function = $module . '_entity_attributes_set';
            if (function_exists($function)) {
                $attributes += $function($set, $entity);
            }
        }

        $theme = \Drupal::service('theme.manager')->getActiveTheme()->getName();
        $function = $theme . '_entity_attributes_set';
        if (function_exists($function)) {
            $attributes += $function($entity);
        }

        \Drupal::moduleHandler()->alter('entity_attributes_set', $attributes, $set, $entity);

        return $attributes;
    }


    public function removeEmptyAttributes(array $attributes)
    {
        $attributes = array_filter($attributes, function($attribute) {
            if (is_array($attribute)) {
                $attribute = $this->removeEmptyAttributes($attribute);
            }

            return !empty($attribute);
        });

        return $attributes;
    }

}