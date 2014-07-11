<?php

namespace Din\DataAccessLayer\Select;

use Din\DataAccessLayer\Select\SelectReadyInterface;

class Union implements SelectReadyInterface
{

  protected $_selects = array();
  protected $_where_values = array();
  protected $_order_by;
  protected $_limit;

  public function addSelect ( SelectReadyInterface $select )
  {
    $this->_selects[] = $select;

  }

  public function getSQL ()
  {
    $sqls = array();

    foreach ( $this->_selects as $select ) {
      if ( !$select instanceof SelectReadyInterface )
        throw new \Exception('Select deve implementar a interface SelectreadyInterface');

      $sqls[] = $select->getSQL();
      $this->_where_values = array_merge($this->_where_values, $select->getWhereValues());
    }

    $r = implode(PHP_EOL .
                    'UNION ALL' . PHP_EOL, $sqls)
            . $this->_order_by
            . $this->_limit;

    return $r;

  }

  public function getWhereValues ()
  {
    return $this->_where_values;

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

  public function __toString ()
  {
    return $this->getSQL();

  }

}
