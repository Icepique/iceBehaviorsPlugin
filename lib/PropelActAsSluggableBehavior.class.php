<?php
/*
 * This file is part of the PropelActAsSluggableBehavior package.
 *
 * (c) 2006-2007 Guillermo Rauch (http://devthought.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * This behavior automates the generation of 'slugs' based on the return value of a model method.
 * Its code is inspired in sfPropelActAsNestedSetBehavior, from which some functions were taken.
 *
 * @author  Guillermo Rauch (http://devthought.com)
 */

class PropelActAsSluggableBehavior
{
  private $default_separator = '-';
  private $default_permanent = false;

  /**
   * Called before node is saved
   *
   * @param   BaseObject  $node
   */
  public function preSave(BaseObject $node)
  {
    $conf_permanent = sprintf('propel_behavior_PropelActAsSluggableBehavior_%s_permanent', get_class($node));
    $permanent = sfConfig::has($conf_permanent) ? sfConfig::get($conf_permanent) : $this->default_permanent;

    $getter = self::forgeMethodName($node, 'get', 'to');

    if (!$permanent || $node->isNew() || !$node->$getter())
    {
      $peer_name = get_class($node->getPeer());
      $culture = (false !== stripos($peer_name, 'I18n')) ? $node->getCulture() : null;

      $slug = $this->createSlug($node, $culture);

      $to_setter = self::forgeMethodName($node, 'set', 'to');
      $node->$to_setter($slug);
    }
  }

  /**
   * Returns the appropiate slug to save
   *
   * @param  BaseObject  $node
   * @param  string      $culture
   *
   * @return null|string
   */
  public function createSlug(BaseObject $node, $culture = null)
  {
    $peer_name = get_class($node->getPeer());
    $node_class = get_class($node);

    $getter = self::forgeMethodName($node, 'get', 'from');
    $column = self::getColumnConstant($node_class, 'to');

    $conf_separator = sprintf('propel_behavior_PropelActAsSluggableBehavior_%s_separator', $node_class);
    $separator = sfConfig::has($conf_separator) ? sfConfig::get($conf_separator) : $this->default_separator;

    $conf_lowecase = sprintf('propel_behavior_PropelActAsSluggableBehavior_%s_lowercase', get_class($node));
    $lowercase = sfConfig::has($conf_lowecase) ? sfConfig::get($conf_lowecase) : true;

    $conf_ascii = sprintf('propel_behavior_PropelActAsSluggableBehavior_%s_ascii', get_class($node));
    $ascii = sfConfig::has($conf_ascii) ? sfConfig::get($conf_ascii) : false;

    $conf_chars = sprintf('propel_behavior_PropelActAsSluggableBehavior_%s_chars', get_class($node));
    $chars = sfConfig::has($conf_chars) ? sfConfig::get($conf_chars) : false;

    $conf_unique = sprintf('propel_behavior_PropelActAsSluggableBehavior_%s_unique', get_class($node));
    $unique = sfConfig::has($conf_unique) ? sfConfig::get($conf_unique) : true;

    $slug = $new_slug = Utf8::slugify($node->$getter(), $separator, $lowercase, $ascii);

    // Impose the char limit if specified
    if ($chars)
    {
      // The limit should be -7 chars because of the random string
      // we put in the end if the slug already exists
      $slug = $new_slug = IceStatic::truncateText($slug, (int) $chars - 7, '', true);
    }

    if (method_exists($peer_name, 'disableSoftDelete'))
    {
      call_user_func(array($peer_name, 'disableSoftDelete'));
    }

    $s = IceStatic::getUniqueId(6);
    while ($unique && !empty($new_slug))
    {
      $c = new Criteria();
      $c->add($column, $new_slug);
      if ($culture !== null)
      {
        $c->add(constant($peer_name.'::CULTURE'), $culture);
      }
      $entry = call_user_func(array($peer_name, 'doSelectOne'), $c);

      /** @var $entry BaseObject */
      if ($entry && !$entry->equals($node))
      {
        $new_slug = $slug . $separator . $s;
        $s = IceStatic::getUniqueId(6);
      }
      else
      {
        break;
      }
    }

    return !empty($new_slug) ? $new_slug : null;
  }

  /**
   * Returns the appropriate column name.
   *
   * @param  string   $node_class  Propel model class
   * @param  string   $column  "generic" column name (either parent, left, right, scope)
   * @param  boolean  $skip_table_name_prefix  Removes table name from column name if true (defaults to false)
   *
   * @return string
   */
  private static function getColumnConstant($node_class, $column, $skip_table_name_prefix = false)
  {
    $conf_directive = sprintf('propel_behavior_PropelActAsSluggableBehavior_%s_columns', $node_class);
    $columns = sfConfig::get($conf_directive);

    return $skip_table_name_prefix ? substr($columns[$column], strpos($columns[$column], '.') + 1) : $columns[$column];
  }

  /**
   * Returns getter / setter name for requested column.
   *
   * @param  BaseObject  $node
   * @param  string  $prefix  get|set|...
   * @param  string  $column  from|to
   *
   * @return string
   */
  private static function forgeMethodName($node, $prefix, $column)
  {
    $method_name = sprintf('%s%s', $prefix, $node->getPeer()->translateFieldName(self::getColumnConstant(get_class($node), $column),
                                                                        BasePeer::TYPE_COLNAME,
                                                                        BasePeer::TYPE_PHPNAME));
    return $method_name;
  }
}
