<?php

class iceBehaviorsPluginConfiguration extends sfPluginConfiguration
{
  /**
   * @see sfPluginConfiguration
   */
  public function initialize()
  {
    sfPropelBehavior::registerHooks('PropelActAsTransliterableBehavior', array(
      ':save:pre' => array('PropelActAsTransliterableBehavior', 'preSave'),
    ));

    sfPropelBehavior::registerHooks('PropelActAsSluggableBehavior', array(
      ':save:pre' => array('PropelActAsSluggableBehavior', 'preSave'),
    ));

    sfPropelBehavior::registerMethods('PropelActAsEblobBehavior', array(
      array('PropelActAsEblobBehavior', 'getFlag'),
      array('PropelActAsEblobBehavior', 'setFlag'),
      array('PropelActAsEblobBehavior', 'getCounter'),
      array('PropelActAsEblobBehavior', 'setCounter'),
      array('PropelActAsEblobBehavior', 'setEblobElement'),
      array('PropelActAsEblobBehavior', 'getEblobElement'),
    ));
  }
}
