<?php

namespace Din\DataAccessLayer\Select;

use Din\DataAccessLayer\Select\SelectReadyInterface;

class SelectCount implements SelectReadyInterface
{

  protected $_select;

  public function __construct ( SelectReadyInterface $select )
  {
    $this->_select = clone($select);

  }

  public function getSQL ()
  {
    $SQL = $this->_select->getSQL();

    if ( strpos($SQL, 'UNION') !== false ) {
      $SQL = $this->countUnion($SQL);
    } elseif(strpos($SQL, 'HAVING') !== false ) {
        $SQL = $this->countHaving($SQL);
    } else {
        $SQL = $this->countNormal($SQL);
    }

    return $SQL;

  }

  protected function countUnion ( $SQL )
  {
    $unions = explode('UNION ALL', $SQL);

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
    $SQL = preg_replace('!\s+!', ' ', $SQL . ' ');
    $last_from_pos = strrpos($SQL, 'FROM');

    $count_field = '*';

    if ( $start_gb = strpos($SQL, 'GROUP BY') ) {
      $gb_part = substr($SQL, $start_gb);

      $nextspace = strpos($gb_part, ' ', 9);
      $gb_field = substr($gb_part, 9, $nextspace - 9);

      $count_field = (trim($gb_field));
      $count_field = "DISTINCT({$count_field})";
      $SQL = str_replace($gb_part, '', $SQL);
    }

    $SQL = "
      SELECT COUNT({$count_field}) total
      " . substr($SQL, $last_from_pos);

    return $SQL;

  }

    protected function countHaving ( $SQL )
    {

        $SQL = "SELECT COUNT(*) total FROM ({$SQL}) test";

        return $SQL;

    }

  public function getWhereValues ()
  {
    return $this->_select->getWhereValues();

  }

}
