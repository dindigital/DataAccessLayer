<?php

namespace Din\DataAccessLayer\Select;

interface SelectReadyInterface
{

  public function getSQL ();

  public function getWhereValues ();
}
