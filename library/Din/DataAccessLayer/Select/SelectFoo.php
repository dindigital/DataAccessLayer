<?php

namespace Din\DataAccessLayer\Select;

use Din\DataAccessLayer\Select\SelectReadyInterface;

class SelectFoo implements SelectReadyInterface
{

  protected $_sql;
  protected $_where_values = array();

  public function setSQL ( $sql )
  {
    $this->_sql = $sql;

  }

  public function setWhereValues ( array $array )
  {
    $this->_where_values = $array;

  }

  public function getSQL ()
  {
    return $this->_sql;

  }

  public function getWhereValues ()
  {
    return $this->_where_values;

  }

}
