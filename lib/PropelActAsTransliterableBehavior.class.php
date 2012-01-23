<?php

class PropelActAsTransliterableBehavior
{
  /**
   * Called before node is saved
   *
   * @param   BaseObject  $node
   */
  public function preSave(BaseObject $node)
  {
    $getter = self::forgeMethodName($node, 'get', 'to');
    $to_setter = self::forgeMethodName($node, 'set', 'to');

    $node->$to_setter($this->translit($node));
  }

  /**
   * Returns the appropiate transliterated text
   *
   * @param   BaseObject  $node
   */
  public function translit(BaseObject $node)
  {
    $peer_name = get_class($node->getPeer());
    $node_class = get_class($node);

    $getter = self::forgeMethodName($node, 'get', 'from');
    $column = self::getColumnConstant($node_class, 'to');

    $translit = array();
    $text = $node->$getter();

    return Utf8::translit($text);
  }

  /**
   * Returns the appropriate column name.
   *
   * @author  Tristan Rivoallan
   * @param   string   $node_class               Propel model class
   * @param   string   $column                   "generic" column name (either parent, left, right, scope)
   * @param   bool     $skip_table_name_prefix   Removes table name from column name if true (defaults to false)
   *
   * @return  string   Column's name
   */
  private static function getColumnConstant($node_class, $column, $skip_table_name_prefix = false)
  {
    $conf_directive = sprintf('propel_behavior_PropelActAsTransliterableBehavior_%s_columns', $node_class);
    $columns = sfConfig::get($conf_directive);

    return $skip_table_name_prefix ? substr($columns[$column], strpos($columns[$column], '.') + 1) : $columns[$column];
  }

  /**
   * Returns getter / setter name for requested column.
   *
   * @author  Tristan Rivoallan
   * @param   BaseObject  $node
   * @param   string      $prefix     get|set|...
   * @param   string      $column     from|to
   */
  private static function forgeMethodName($node, $prefix, $column)
  {
    $method_name = sprintf('%s%s', $prefix, $node->getPeer()->translateFieldName(self::getColumnConstant(get_class($node), $column),
                                                                        BasePeer::TYPE_COLNAME,
                                                                        BasePeer::TYPE_PHPNAME));
    return $method_name;
  }
}