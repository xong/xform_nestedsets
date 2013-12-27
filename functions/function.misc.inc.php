<?php

/**
 * XForm-Plugin: Nested Sets
 * @author robert.rupf[at]maumha[dot]de Robert Rupf
 * @author <a href="http://www.maumha.de">www.maumha.de</a>
 */

function xform_nestedsets_table_is_nestedset($_table)
{
  static $tables = array();
  
  if(array_key_exists($_table, $tables))
    return $tables[$_table];
  
  $sql = rex_sql::factory();
  $sql->setQuery("SHOW COLUMNS FROM `".$sql->escape($_table)."` WHERE Field LIKE 'nestedset\\_%'");
  
  return $sql->getRows() >= 4;
}