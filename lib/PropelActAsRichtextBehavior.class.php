<?php

include_once(dirname(__FILE__).'/../../iceLibsPlugin/lib/vendor/HtmLawed.php');

class PropelActAsRichtextBehavior
{
  /**
   * Called before node is saved
   *
   * @param   BaseObject  $node
   */
  public function preSave(BaseObject $node)
  {
    $get_method = sprintf(
      'get%s',
      $node->getPeer()->translateFieldName(
        sfConfig::get(sprintf('propel_behavior_PropelActAsRichTextBehavior_%s_column', get_class($node))),
        BasePeer::TYPE_COLNAME,
        BasePeer::TYPE_PHPNAME
      )
    );
    $set_method = sprintf(
      'set%s',
      $node->getPeer()->translateFieldName(
        sfConfig::get(sprintf('propel_behavior_PropelActAsRichTextBehavior_%s_column', get_class($node))),
        BasePeer::TYPE_COLNAME,
        BasePeer::TYPE_PHPNAME
      )
    );

    $config = array(
      'safe' => 1,
      'tidy' => 1,
      'cdata' => 0,
      'clean_ms_char' => 0,
      'comment' => 1
    );
    $text = $node->$get_method();
    $text = mb_convert_encoding($text, 'utf-8', mb_detect_encoding($text));
    $processed = HtmLawed($text, $config);

    $node->$set_method($processed);
  }
}
