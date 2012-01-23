<?php

class PropelActAsEblobBehavior
{
  /**
   * @var array
   */
  private static $_eblob = array();
  
  /**
   * @var string
   */
  private $default_column = 'eblob';

  public function setFlag(BaseObject $object, $flag, $value)
  {
    
  }

  public function getFlag(BaseObject $object, $flag)
  {

  }

  public function setCounter(BaseObject $object, $counter, $value)
  {

  }

  public function getCounter(BaseObject $object, $counter)
  {
    
  }
  
  /**
   * @param  BaseObject  $object
   * @param  string      $name
   * 
   * @return IceXMLElement
   */
  public function getEblobElement(BaseObject $object, $name)
  {
    $xml = $this->_getXml($object);

    return $xml->findOne($name);
  }
  
  /**
   * @param  BaseObject     $object
   * @param  string         $name
   * @param  IceXMLElement  $element 
   * 
   * @return boolean
   */
  public function setEblobElement(BaseObject $object, $name, $element = null)
  {
    if (is_string($element))
    {
      $element = simplexml_load_string($element, 'IceXMLElement');
    }

    if (!$element instanceof IceXMLElement)
    {
      return false;
    }

    // Get the current eblob data
    $xml = $this->_getXml($object);
    
    // Delete the old element from it if it already exists
    if (isset($xml->$name)) 
    {
      unset($xml->$name);
    }

    // Figure out the node to insert the element into
    $root = ($element->getName() !== $name) ? $xml->addChild($name) : $xml;

    if ($element->getName() == 'data')
    {
      foreach ($element->children() as $child)
      {
        IceXMLElement::join($root, $child);
      }
    }
    else
    {
      IceXMLElement::join($root, $element);
    }

    return $this->_setXml($object, $xml);
  }

  /**
   * @param  BaseObject  $object
   * 
   * @return IceXMLElement
   */
  private function _getXml(BaseObject $object)
  {
    $key = md5(get_class($object) .'-'. $object->getId());

    if (!isset(self::$_eblob[$key]))
    {
      $xml = null;

      if (method_exists($object, 'getEblob'))
      {
        $xml = simplexml_load_string($object->getEblob(), 'IceXMLElement');
      }

      if (!$xml || $xml->getName() != 'eblob')
      {
        $xml = simplexml_load_string('<?xml version="1.0" encoding="UTF-8"?><eblob></eblob>', 'IceXMLElement');
      }

      self::$_eblob[$key] = $xml;
    }

    return self::$_eblob[$key];
  }

  /**
   * @param  BaseObject     $object
   * @param  IceXMLElement  $xml
   * 
   * @return boolean
   */
  private function _setXml(BaseObject $object, IceXMLElement $xml)
  {
    $conf_column = sprintf('propel_behavior_PropelActAsEblobBehavior_%s_column', get_class($object));
    $column = sfConfig::has($conf_column) ? sfConfig::get($conf_column) : $this->default_column;

    $key = md5(serialize(array(get_class($object), $object->getId())));
    self::$_eblob[$key] = $xml;

    if (method_exists($object, 'set'. $column))
    {
      return call_user_func(array($object, 'set'. $column), $xml->asXml());
    }

    return false;
  }
}
