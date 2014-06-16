<?php

namespace Din\DataAccessLayer;

use PDO;
use Din\DataAccessLayer\Table\iTable;
use Din\DataAccessLayer\Select;
use Din\DataAccessLayer\Select\SelectReadyInterface;
use Din\DataAccessLayer\Select\SelectCount;
use Exception;

class DAO
{

  /**
   * Instancia PDO
   * @var \PDO
   */
  public $_driver;

  /**
   * Data Access Object para operações CRUD.
   * @param \PDO $PDO
   */
  public function __construct ( PDO $PDO )
  {
    $PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $this->_driver = $PDO;

  }

  /**
   * Realiza insert recebendo instancia da table como parâmetro.
   * Retorna o lastInsertId
   * @param \Din\DataAccessLayer\Table\iTable $table
   * @param bool $ignore ignorar ao inserir id duplicado?
   * @return int
   */
  public function insert ( iTable $table, $ignore = false )
  {
    $tbl = $table->getName();
    $row = $table->getArray();

    $insert = new Insert;
    $insert->setTbl($tbl);
    $insert->setRow($row);
    $insert->setIgnore($ignore);
    $insert->build();
    $SQL = $insert->getSQL();
    $arr_params = $insert->getParams();

    $this->pdoExecute($SQL, $arr_params);

    return $this->_driver->lastInsertId();

  }

  /**
   * Realiza update recebendo  instancia de table como parametro.
   * Retorna o número de linhas afetadas.
   * @param \Din\DataAccessLayer\Table\iTable $Table
   * @param array $arrCriteria criterio no formato da class Criteria
   * @return int
   */
  public function update ( iTable $table, array $arrCriteria )
  {
    $tbl = $table->getName();
    $row = $table->getArray();

    $update = new Update;
    $update->setTbl($tbl);
    $update->setRow($row);
    $update->setCriteria($arrCriteria);
    $update->build();
    $SQL = $update->getSQL();
    $arr_params = $update->getParams();

    $PDOStatement = $this->pdoExecute($SQL, $arr_params);

    return $PDOStatement->rowCount();

  }

  /**
   * Realiza delete recebendo nome da tabela e criteria.
   * Retorna o número de linhas afetadas.
   * @param string $tablename
   * @param array $arrCriteria criterio no formato da class Criteria
   * @return int
   * @throws Exception
   */
  public function delete ( $tablename, array $arrCriteria )
  {
    $delete = new Delete;
    $delete->setTbl($tablename);
    $delete->setCriteria($arrCriteria);
    $delete->build();
    $SQL = $delete->getSQL();
    $arr_params = $delete->getParams();

    $PDOStatement = $this->pdoExecute($SQL, $arr_params);

    return $PDOStatement->rowCount();

  }

  public function select_debug ( Select $select )
  {
    $SQL = $select->getSQL();
    $arrIN = $select->getWhereValues();

    foreach ( $arrIN as $parameter ) {
      $SQL = str_replace('?', '"' . $parameter . '"', $SQL);
    }

    var_dump($SQL);
    exit;

  }

  /**
   * Realiza select utilizando instancia da class Select como parametro
   * Retorna resultado em array
   * @param \Din\DataAccessLayer\Select $select
   * @return array
   */
  public function select_pure ( $SQL, $params, $fetch_class = null )
  {
    return $this->pdoFetch($SQL, $params, $fetch_class);

  }

  /**
   * Realiza select utilizando instancia da class Select como parametro
   * Retorna resultado em array
   * @param SelectReadyInterface $select
   * @return array
   */
  public function select ( SelectReadyInterface $select, $fetch_class = null )
  {
    return $this->pdoFetch($select->getSQL(), $select->getWhereValues(), $fetch_class);

  }

  /**
   * Realiza SELECT COUNT utilizando instancia da class Select como parametro
   * Retorna um número inteiro.
   * @param \Din\DataAccessLayer\Select $select
   * @return int
   */
  public function select_count ( SelectReadyInterface $select )
  {
    $select = new SelectCount($select);
    $result = $this->pdoFetch($select->getSQL(), $select->getWhereValues());

    return intval($result[0]['total']);

  }

  /**
   * Realiza SELECT COUNT utilizando instancia da class Select como parametro
   * Retorna um número inteiro.
   * @param \Din\DataAccessLayer\Select $select
   * @return int
   */
  public function select_count_old ( SelectReadyInterface $select )
  {
    $result = $this->pdoFetch($select->getSQLCount(), $select->getWhereValues());

    return intval($result[0]['total']);

  }

  /**
   * Executa uma query livre, passando por parametro ela e o criterio.
   * Retorna o número de linhas afetadas.
   * @param string $SQL
   * @param array $arrCriteria criterio no formato da class Criteria
   * @return int
   */
  public function execute ( $SQL, array $arrCriteria = array(), $fetch = false )
  {
    $execute = new Execute;
    $execute->setSQL($SQL);
    $execute->setCriteria($arrCriteria);
    $execute->build();
    $SQL = $execute->getSQL();
    $arr_params = $execute->getParams();

    $PDOStatement = $this->pdoExecute($SQL, $arr_params);

    return $fetch ? $PDOStatement->fetchAll(PDO::FETCH_ASSOC) : $PDOStatement->rowCount();

  }

  /**
   * Executa uma query e retorna um fetchAll do PDO.
   * Utilizada em consultas SELECT
   *
   * @param string $SQL
   * @param array $arrParams
   * @return array
   */
  private function pdoFetch ( $SQL, array $arrParams = array(), $fetch_class = null )
  {
    $stmt = $this->_driver->prepare($SQL);
    if ( $fetch_class ) {
      $stmt->setFetchMode(PDO::FETCH_CLASS, get_class($fetch_class));
      $stmt->execute($arrParams);
      $result = $stmt->fetchAll();
    } else {
      $stmt->setFetchMode(PDO::FETCH_ASSOC);

      $stmt->execute($arrParams);
      $result = $stmt->fetchAll();
    }

    return $result;

  }

  /**
   * Executa o SQL e retorna uma instancia de PDOStatement
   *
   * @param string $SQL
   * @param array $arrParams
   * @return \PDOStatement
   */
  public function pdoExecute ( $SQL, array $arrParams = array() )
  {
    $PDOStatement = $this->_driver->prepare($SQL);
    $PDOStatement->execute($arrParams);

    return $PDOStatement;

  }

}
