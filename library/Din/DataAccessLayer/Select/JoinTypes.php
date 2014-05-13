<?php

namespace Din\DataAccessLayer\Select;

class JoinTypes
{

  const INNER = 'INNER';
  const LEFT = 'LEFT';

  public static $types = array(
      self::INNER,
      self::LEFT
  );

  public static function isValid ( $type )
  {
    return array_search($type, self::$types) !== false;

  }

}
