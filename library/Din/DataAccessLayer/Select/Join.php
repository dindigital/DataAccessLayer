<?php

namespace Din\DataAccessLayer\Select;

use Din\DataAccessLayer\Select\JoinTypes;

class Join
{

  protected $_table;
  protected $_type;
  protected $_field1;
  protected $_field2;

  public function __construct ( $foreign_table, $type = JoinTypes::INNER )
  {
    $this->setTable($foreign_table);
    $this->setType($type);

  }

  public function setTable ( $foreign_table )
  {
    $this->_table = "`{$foreign_table}`";

  }

  public function setType ( $type )
  {
    if ( !JoinTypes::isValid($type) )
      throw new \InvalidArgumentException('Invalid type of join: ' . $type);

    $this->_type = $type;

  }

  /**
   * @param string $origin_field Eg: foregin_table.foreign_field
   * @param string $foreign_field Eg: field
   */
  public function on ( $origin_field, $foreign_field )
  {
    $this->_field1 = $origin_field;
    $this->_field2 = "{$this->_table}.`{$foreign_field}`";

  }

  public function getSQL ()
  {
    $r = "
      {$this->_type} JOIN
        {$this->_table}
      ON
        {$this->_field1} = {$this->_field2}
    ";

    return $r;

  }

  public function __toString ()
  {
    return $this->getSQL();

  }

}
