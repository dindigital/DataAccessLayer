<?php

namespace Din\DataAccessLayer\Criteria;

class Field
{

  private $_expression;
  private $_separator;

  public function setExpression ( $expression )
  {
    $field = substr($expression, 0, strpos($expression, ' '));
    $dotpos = strpos($field, '.');
    if ( $dotpos ) {
      $field = substr($field, $dotpos + 1);
    }
    $expression = str_replace($field, "`{$field}`", $expression);

    /*
    * Condição para efetuar a busca por Mês, desconsiderando o campo
    * utilizando a função MONTH() 
    */
    $pos = strpos($expression, 'MONTH');
    if ($pos !== false) {
       $expression = str_replace("`{$field}`", "{$field}", $expression);
    }

    $this->_expression = $expression;

  }

  public function setSeparator ( $separator )
  {
    $this->_separator = $separator;

  }

  public function getExpression ()
  {
    return $this->_expression;

  }

  public function getSeparator ()
  {
    return $this->_separator;

  }

}
