<?php

namespace Din\DataAccessLayer\PDO;

use Din\DataAccessLayer\PDO\DSN\iDSN;

class PDODriver extends \PDO
{

  public function __construct ( iDSN $DSN, $host, $schema, $username, $passwd )
  {

    $dsn = $DSN->getDSN($schema, $host);
    parent::__construct($dsn, $username, $passwd);

    //$this->exec("set names utf8");

  }

//
//  /**
//   * Executa uma query e retorna um fetchAll do PDO.
//   * Utilizada em consultas SELECT
//   *
//   * @param string $SQL
//   * @param array $arrParams
//   * @return array
//   */
//  public function select ( $SQL, array $arrParams = array(), $fetch_class = null )
//  {
//    $stmt = $this->prepare($SQL);
//    if ( $fetch_class ) {
//      $stmt->setFetchMode(\PDO::FETCH_CLASS, get_class($fetch_class));
//      $stmt->execute($arrParams);
//      $result = $stmt->fetchAll();
//    } else {
//      $stmt->setFetchMode(\PDO::FETCH_ASSOC);
//
//      $stmt->execute($arrParams);
//      $result = $stmt->fetchAll();
//    }
//
//
//
//    return $result;
//
//  }
//
//  /**
//   * Executa o SQL e retorna uma instancia de PDOStatement
//   *
//   * @param string $SQL
//   * @param array $arrParams
//   * @return int
//   */
//  public function execute ( $SQL, array $arrParams = array() )
//  {
//    $PDOStatement = $this->prepare($SQL);
//    $PDOStatement->execute($arrParams);
//
//    return $PDOStatement;
//
//  }
}
