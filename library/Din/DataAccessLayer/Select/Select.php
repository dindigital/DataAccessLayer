<?php

namespace Din\DataAccessLayer\Select;

use Din\DataAccessLayer\Criteria\Criteria;
use Din\DataAccessLayer\Select\SubselectReadyInterface;
use Din\DataAccessLayer\Select\Join;
use Din\DataAccessLayer\Select\JoinTypes;
use Din\DataAccessLayer\Select\SelectReadyInterface;

class Select implements SelectReadyInterface
{

  protected $_table;
  protected $_fields = array();
  protected $_where_fields;
  protected $_where_values = array();
  protected $_having_fields;
  protected $_group_by;
  protected $_order_by;
  protected $_limit;
  protected $_joins;

  public function __construct ( $table )
  {
    $this->setTable($table);

  }

  public function setTable ( $table )
  {
    $this->_table = "`{$table}`";

  }

  public function addAllFields ()
  {
    $str_field = "
        {$this->_table}.*";
    $this->_fields['*'] = $str_field;

    return $this;

  }

  public function addSubselect ( SubselectReadyInterface $subselect, $alias )
  {
    $SQL = $subselect->getSQL();
    $str_field = "
      ({$SQL}) as '{$alias}'";

    $this->_fields[$alias] = $str_field;
    $this->_where_values = array_merge(
            $this->_where_values, $subselect->getWhereValues()
    );

    return $this;

  }

  public function addField ( $field, $alias = null, $table = null )
  {

    $table = is_null($table) ? $this->_table : "`{$table}`";

    $str_field = "
        {$table}.`{$field}`";

    if ( $alias ) {
      $str_field .= " as '{$alias}'";
    }

    $field_id = is_null($alias) ? $field : $alias;

    $this->_fields[$field_id] = $str_field;

    return $this;

  }

  public function addSField ( $value, $alias )
  {
    $this->_fields[$alias] = "
      '{$value}' as '{$alias}'";

    return $this;

  }

  public function addFField ( $function, $alias )
  {
    $this->_fields[$alias] = "
        {$function} as '{$alias}'";

    return $this;

  }

  public function addJoin ( Join $join )
  {
    $this->_joins .= $join->getSQL();

    return $this;

  }

  public function inner_join ( $table, $origin_field, $foreign_field, $origin_table = null )
  {
    $origin_table = is_null($origin_table) ? $this->_table : "`{$origin_table}`";

    $join = new Join($table, JoinTypes::INNER);
    $join->on("{$origin_table}.`{$origin_field}`", $foreign_field);

    return $this->addJoin($join);

  }

  public function left_join ( $table, $origin_field, $foreign_field, $origin_table = null  )
  {
    $origin_table = is_null($origin_table) ? $this->_table : "`{$origin_table}`";

    $join = new Join($table, JoinTypes::LEFT);
    $join->on("{$origin_table}.`{$origin_field}`", $foreign_field);

    return $this->addJoin($join);

  }

  public function where ( Criteria $criteria )
  {
    $criteria->buildSQL();
    $this->_where_fields = '  ' . $criteria->getSQL();
//    $this->_where_values = array_merge(
//            $this->_where_values, $criteria->getParams()
//    );
    $this->_where_values = $criteria->getParams();

    return $this;

  }

  public function having ( Criteria $criteria )
  {
    $criteria->buildSQL();
    $where = '  ' . $criteria->getSQL();
    $having = str_replace('WHERE', '
      HAVING', $where);
    $this->_having_fields = $having;
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

  public function limit ( $limit, $offset = 0 )
  {
    $limit = intval($limit);
    $offset = intval($offset);

    $this->_limit = "
      LIMIT
        {$offset},{$limit}";

    return $this;

  }

  public function group_by ( $field )
  {
    $this->_group_by = "
      GROUP BY
        {$field}";

    return $this;

  }

  protected function getFields ()
  {
    $fields = array();
    if ( count($this->_fields) ) {
      $fields = array(implode(', ', $this->_fields));
    }

    $str_fields = implode(',' . PHP_EOL . '        ', $fields);

    return $str_fields;

  }

  public function getSQL ()
  {
    $r = '
      SELECT'
            . $this->getFields()
            . '
      FROM'
            . '
        ' . $this->_table
            . $this->_joins
            . $this->_where_fields
            . $this->_group_by
            . $this->_having_fields
            . $this->_order_by
            . $this->_limit;

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
