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

    $inject = "SELECT COUNT(*) total ";
    $last_from_pos = strrpos($SQL, 'FROM');

    $SQL = $inject . substr($SQL, $last_from_pos);

    return $SQL;

  }

  public function getWhereValues ()
  {
    return $this->_select->getWhereValues();

  }

}
