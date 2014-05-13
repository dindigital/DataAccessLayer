<?php

namespace Din\DataAccessLayer\Select;

use Din\DataAccessLayer\Criteria\Criteria;
use Din\DataAccessLayer\Select\SubselectReadyInterface;

class Subselect implements SubselectReadyInterface
{

  protected $_table;
  protected $_field;
  protected $_where_fields;
  protected $_where_values = array();
  protected $_order_by;

  public function __construct ( $table )
  {
    $this->setTable($table);

  }

  public static function create ( $table )
  {
    $subselect = new self($table);

    return $subselect;

  }

  public function setTable ( $table )
  {
    $this->_table = "`{$table}`";

  }

  public function setField ( $field, $alias = null )
  {
    $str_field = "{$this->_table}.`{$field}`";

    if ( $alias ) {
      $str_field .= " as '{$alias}'";
    }
    $this->_field = $str_field;

    return $this;

  }

  public function setSField ( $value, $alias )
  {
    $this->_field = "'{$value}' as '{$alias}'";

    return $this;

  }

  public function setFField ( $function, $alias )
  {
    $this->_field = "{$function} as '{$alias}'";

    return $this;

  }

  public function where ( Criteria $criteria )
  {
    $criteria->buildSQL();
    $this->_where_fields = '  ' . $criteria->getSQL();
    $this->_where_values = array_merge(
            $this->_where_values, $criteria->getParams()
    );

    return $this;

  }

  public function order_by ( $order_by )
  {
    $this->_order_by = "
      ORDER BY
        {$order_by}";

    return $this;

  }

  public function getSQL ()
  {
    $r = "
      SELECT
        {$this->_field}
      FROM
        {$this->_table}
      {$this->_where_fields}{$this->_order_by}
      LIMIT 1
    ";

    return $r;

  }

  public function getWhereValues ()
  {
    return $this->_where_values;

  }

  public function __toString ()
  {
    return $this->getSQL();

  }

}
