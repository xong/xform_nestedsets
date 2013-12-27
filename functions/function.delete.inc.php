<?php

/**
 * XForm-Plugin: Nested Sets
 * @author robert.rupf[at]maumha[dot]de Robert Rupf
 * @author <a href="http://www.maumha.de">www.maumha.de</a>
 */

function xform_nestedsets_delete($_params)
{
  if(!xform_nestedsets_table_is_nestedset($_params['table']['table_name']))
    return true;
  
  $sql = rex_sql::factory();
  $sql->setQuery("DELETE FROM `".$sql->escape($_params['table']['table_name'])."` WHERE nestedset_lft BETWEEN ".$_params['value']['nestedset_lft']." AND ".$_params['value']['nestedset_rgt']);
  //$sql->setQuery("UPDATE `".$sql->escape($_params['table']['table_name'])."` SET nestedset_lft = NULL, nestedset_rgt = NULL WHERE id = ".$_params['id']);
  $sql->setQuery("UPDATE `".$sql->escape($_params['table']['table_name'])."` SET nestedset_lft = nestedset_lft - ROUND(".($_params['value']['nestedset_rgt'] - $_params['value']['nestedset_lft'] + 1).") WHERE nestedset_lft > ".$_params['value']['nestedset_rgt']);
  $sql->setQuery("UPDATE `".$sql->escape($_params['table']['table_name'])."` SET nestedset_rgt = nestedset_rgt - ROUND(".($_params['value']['nestedset_rgt'] - $_params['value']['nestedset_lft'] + 1).") WHERE nestedset_rgt > ".$_params['value']['nestedset_rgt']);
  
  return true;
}

function xform_nestedsets_dataset_delete($_params)
{
  if(!xform_nestedsets_table_is_nestedset($_params['table']['table_name']))
    return true;
  
  $rex_xform_searchfields = rex_request('rex_xform_searchfields', 'array');
  $rex_xform_searchtext = rex_request('rex_xform_searchtext', 'string');
  $rex_xform_filter = rex_request('rex_xform_filter', 'array');
  
  $xform_manager = new rex_xform_manager();
  $sql = rex_sql::factory();
  $sql->setQuery("SELECT id FROM `".$sql->escape($_params['table']['table_name'])."` ".$xform_manager->getDataListQueryWhere($rex_xform_filter, $rex_xform_searchfields, $rex_xform_searchtext));
  
  if(!$sql->getRows())
    return true;
  
  foreach($sql->getArray() as $id)
  {
    $sql->flush();
    $sql->setQuery("SELECT nestedset_lft, nestedset_rgt FROM `".$sql->escape($_params['table']['table_name'])."` WHERE id = ".$id['id']);
    $data = $sql->getRow();
    
    xform_nestedsets_delete(array(
      'table' => array('table_name' => $_params['table']['table_name']),
      'value' => array(
        'nestedset_lft' => $data['nestedset_lft'],
        'nestedset_rgt' => $data['nestedset_rgt']
      )
    ));
  }
  
  return true;
}

function xform_nestedsets_truncate($_params)
{
  if(!xform_nestedsets_table_is_nestedset($_params['table']['table_name']))
    return true;
  
  xform_nestedsets_deactivate($_params['table']['table_name']);
  xform_nestedsets_activate($_params['table']['table_name']);
}
