<?php

/**
 * Class TwigExtension
 */
class TwigExtension extends \Twig_Extension implements \Twig_ExtensionInterface{

  public function getName()
  {
    return 'entity_attributes';
  }

  public function getFunctions()
  {
    return array(
      new \Twig_SimpleFunction('entity_attributes', array($this, 'entityAttributes'), array('is_safe' => array('html'))),
    );
  }

  public function entityAttributes($entity)
  {
    $entityAttributesHelper = new \Drupal\entity_attributes\Helper\EntityAttributesHelper();
    $entityAttributesHelper->getEntityAttributes($entity);
  }

}
