<?php

namespace Din\DataAccessLayer\Select;

use Din\DataAccessLayer\Select\SelectReadyInterface;

class SelectCount implements SelectReadyInterface
{

  protected $_select;

  public function __construct ( SelectReadyInterface $select )
  {
    $this->_select = $select;

  }

  public function getSQL ()
  {
    $SQL = $this->_select->getSQL();

    if ( strpos($SQL, 'UNION') !== false ) {
      $SQL = $this->countUnion($SQL);
    } else {
      $SQL = $this->countNormal($SQL);
    }

    return $SQL;

  }

  protected function countUnion ( $SQL )
  {
    $unions = explode('UNION', $SQL);

    foreach ( $unions as $i => $part ) {
      $part = str_replace(PHP_EOL, ' ', $part);
      $part = str_replace("\r", ' ', $part);

      $part = preg_replace('/SELECT(.*)FROM/', '
        SELECT COUNT(*) to_sum FROM
        ', $part);

      $part = preg_replace('/ORDER(.*)/', '', $part);

      $unions[$i] = $part;
    }


    $SQL = implode('UNION ALL', $unions);

    $SQL = "
      SELECT SUM(to_sum) total FROM (
        {$SQL}
      ) counter
    ";

    return $SQL;

  }

  protected function countNormal ( $SQL )
  {
    $SQL = str_replace(PHP_EOL, ' ', $SQL);
    $SQL = str_replace("\r", ' ', $SQL);
    $SQL = preg_replace('/ORDER(.*)/', '', $SQL);
    $last_from_pos = strrpos($SQL, 'FROM');

    $SQL = "
      SELECT COUNT(*) total
      " . substr($SQL, $last_from_pos);

    return $SQL;

  }

  public function getWhereValues ()
  {
    return $this->_select->getWhereValues();

  }

}
