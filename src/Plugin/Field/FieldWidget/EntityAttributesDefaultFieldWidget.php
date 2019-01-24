<?php

namespace Drupal\entity_attributes\Plugin\Field\FieldWidget;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\entity_attributes\Helper\EntityAttributesHelper;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Plugin implementation of the 'Entity Attributes' widget.
 *
 * @FieldWidget(
 *   id = "entity_attributes_default_widget",
 *   label = @Translation("Entity Attributes"),
 *   field_types = {
 *     "entity_attributes"
 *   }
 * )
 */
class EntityAttributesDefaultFieldWidget extends WidgetBase
{

    protected $entityAttributeHelper;

    public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings)
    {
        parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
        $this->entityAttributeHelper = new EntityAttributesHelper();
    }

    /**
     * @inheritdoc
     */
    public function formElement(
        FieldItemListInterface $items,
        $delta,
        Array $element,
        Array &$form,
        FormStateInterface $formState
    ) {

        $value = isset($items[$delta]) ? $items[$delta]->value : '{}';
        $attributeValues = $this->entityAttributeHelper->fromJson($value);

        $cardinality = $items->getFieldDefinition()->getFieldStorageDefinition()->getCardinality();
        /** @var ContentEntityBase $entity */
        $entity = $items->getEntity();
        $view_modes = $this->entityAttributeHelper->getEntityAvailableViewModes($entity);
        $attributeSets = $this->entityAttributeHelper->getDefinedAttributeSets($entity);

        $element['entity_attributes'] = [
            '#type' => 'details',
            '#title' => t("Attributes"),
            '#open' => false,
        ];

        if (!empty($attributeSets)) {
            $element['entity_attributes']['sets_wrapper'] = [
              '#type' => 'details',
              '#title' => t('Attribute sets'),
              '#open' => false,
            ];

            $element['entity_attributes']['sets_wrapper']['sets'] = [
                '#type' => 'checkboxes',
                '#options' => $attributeSets,
                '#default_value' => $attributeValues['sets'],
                '#multiple' => true,
            ];
        }

        $element['entity_attributes']['class'] = [
            '#type' => 'textfield',
            '#title' => t('Class(es)'),
            '#description' => t('Class names separated with a space.'),
            '#default_value' => implode(" ", $attributeValues['class']),
        ];

        $element['entity_attributes']['id'] = [
            '#type' => 'textfield',
            '#title' => t('ID'),
            '#description' => t('The ID of the element. Must be a unique name.'),
            '#default_value' => $attributeValues['id'],
        ];


        $element['entity_attributes']['advanced'] = [
            '#type' => 'details',
            '#title' => t('Advanced'),
            '#open' => false,
        ];

        $element['entity_attributes']['advanced']['advanced_attributes'] = [
            '#type' => 'textarea',
            '#title' => t('Attributes'),
            '#description' => t('Enter each attribute on a new line using the following format "attribute|value"'),
            '#default_value' => implode("\n", $attributeValues['advanced'])
        ];

        if (count($view_modes) > 1) {
            $element['entity_attributes']['view_modes'] = [
                '#type' => 'details',
                '#title' => t('View modes'),
                '#open' => false,
            ];

            $element['entity_attributes']['view_modes']['view_mode_availability'] = [
                '#type' => 'radios',
                '#options' => [
                    'enable' => t('Enable for the following view modes only'),
                    'disable' => t('Disable for the following view modes only')
                ],
                '#default_value' => 'enable',
            ];

            $element['entity_attributes']['view_modes']['view_modes_selection'] = [
                '#type' => 'select',
                '#options' => $view_modes,
                '#title' => t('View mode selection'),
                '#multiple' => true,
            ];
        }


        return $element;
    }

    /**
     * @inheritdoc
     */
    public function massageFormValues(array $values, array $form, FormStateInterface $form_state)
    {
        array_walk($values, function(&$item, $key) {
           $item = !empty($item['entity_attributes'])
                ? $item['entity_attributes']
                : [];
           $item = ['value' => (!empty($item) ? $this->entityAttributeHelper->toJson($item) : '')];
        });

        return $values;
    }

    /**
     * @inheritdoc
     */
    public function flagErrors(FieldItemListInterface $items, ConstraintViolationListInterface $violations, array $form, FormStateInterface $form_state)
    {
        return;
    }

}
