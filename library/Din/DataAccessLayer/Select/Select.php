<?php

namespace Din\DataAccessLayer\Select;

use Din\DataAccessLayer\Criteria\Criteria;

class Select
{

  private $_fields = array();
  private $_table;

  public function __construct ( $table, $fields = array() )
  {
    $this->setTable($table);
    $this->setFields($fields);

  }

  public function setTable ( $table )
  {
    $this->_table = $table;

  }

  public function setFields ( $fields )
  {
    foreach ( $fields as $key => $field ) {
      $alias = !is_numeric($key) ? $key : null;
      $this->addField($field, $alias);
    }

    return $this;

  }

  public function addAllFields ()
  {
    $str_field = "{{$this->getTable()}}.*";
    $this->_fields['*'] = $str_field;

    return $this;

  }

  public function addField ( $field, $alias = null )
  {
    $str_field = "{{$this->getTable()}}.`{$field}`";

    if ( $alias ) {
      $str_field .= " as '{$alias}'";
    }
    $this->_fields[$field] = $str_field;

    return $this;

  }

  public function addSField ( $alias, $value )
  {
    $this->_fields[$alias] = "'{$value}' as {$alias}";

    return $this;

  }

  public function addFField ( $alias, $function )
  {
    $this->_fields[$alias] = "{$function} as {$alias}";

    return $this;

  }

  public function addJoin ( $type, $join, $field, $field2 = null )
  {
    $master_table = $this->getTable();
    $joined_table = $join->getTable();

    if ( is_null($field2) ) {
      $field2 = $field;
    }

    $on = "{{$joined_table}}.`{$field}` = {{$master_table}}.`{$field2}`";

    $str_join = "{$type} JOIN
        `{$joined_table}` {{$joined_table}}
      ON
        {$on}
    ";

    $this->_tables = array_merge($this->_tables, $join->getTables());

    $std = new \stdClass();
    $std->join = $str_join . '  ' . $join->getJoins();
    $std->join_fields = $join->getFields();

    $this->_join[$joined_table] = $std;

    return $this;

  }

  public function getTables ()
  {
    return $this->_tables;

  }

  public function getJoins ()
  {
    $joins = array();
    foreach ( $this->getJoin() as $join ) {
      $joins[] = $join->join;
    }

    $str_joins = implode('', $joins);

    return $str_joins;

  }

  public function getJoin ()
  {
    return $this->_join;

  }

  public function inner_join ( $field, $join, $field2 = null )
  {
    return $this->setJoin('INNER', $join, $field, $field2);

  }

  public function left_join ( $field, $join, $field2 = null )
  {
    return $this->setJoin('LEFT', $join, $field, $field2);

  }

  public function setWhere ( $arrCriteria )
  {
    $criteria = new Criteria($arrCriteria);
    $criteria->buildSQL();
    $this->_where_fields = '  ' . $criteria->getSQL();
    $this->_where_values = $criteria->getParams();

    return $this;

  }

  public function getWhereValues ()
  {
    return $this->_where_values;

  }

  public function where ( $arrCriteria )
  {
    return $this->setWhere($arrCriteria);

  }

  public function setOrderBy ( $order_by )
  {
    $this->_order_by = "
      ORDER BY
        {$order_by}";

    return $this;

  }

  public function order_by ( $order_by )
  {
    return $this->setOrderBy($order_by);

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
    $this->_group_by_field = $field;
    $this->_group_by = "  GROUP BY
        {$field}";

    return $this;

  }

  public function getFields ()
  {

    $fields = array();
    if ( count($this->_fields) ) {
      $fields = array(implode(', ', $this->_fields));
    }

    foreach ( $this->getJoin() as $join ) {
      if ( $join->join_fields != '' ) {
        $fields[] = $join->join_fields;
      }
    }

    $str_fields = implode(',' . PHP_EOL . '        ', $fields);

    return $str_fields;

  }

  public function getSQL ()
  {
//    if ( $this->_count )
//      return $this->getSQLCount();

    $str_fields = $this->getFields();
    $str_joins = $obj->getJoins();
    $str_union = $obj->getUnion();
    $str_where = $obj->_where_fields;

    $r = "
      SELECT
        {$str_fields}
      FROM
        `{$obj->_table}` {$obj->_table_alias}
      {$str_joins}{$str_where}{$str_union}{$obj->_group_by}{$obj->_order_by}{$obj->_limit}
    ";

    return $r;

  }

  public function __toString ()
  {
    return $this->getSQL();

  }

}
