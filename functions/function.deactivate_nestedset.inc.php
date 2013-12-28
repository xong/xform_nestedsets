<?php

/**
 * XForm-Plugin: Nested Sets
 * @author robert.rupf[at]maumha[dot]de Robert Rupf
 * @author <a href="http://www.maumha.de">www.maumha.de</a>
 */

function xform_nestedsets_show_deactivate($_params)
{
  global $I18N;
  
  if($nestedsets = rex_get('nestedsets'))
  {
    if($nestedsets == 'activate')
    {
      xform_nestedsets_activate($_params['table']['table_name']);
    }
    else
    {
      xform_nestedsets_deactivate($_params['table']['table_name']);
    }
  }
  
  $func = 'activate';
  $func_link = '';
  $sql = rex_sql::factory();
  
  if(xform_nestedsets_table_is_nestedset($_params['table']['table_name']))
  {
    $func = 'deactivate';
  }
  else
  {
    // nested sets are only available for empty tables
    $sql->flush();
    $sql->setQuery("SELECT id FROM `".$sql->escape($_params['table']['table_name'])."` LIMIT 1");
    if($sql->getRows())
      $func_link = '<em>'.$I18N->msg('xform_nestedsets_notavailable').'</em>';
  }
  
  if(empty($func_link))
    $func_link = '<a class="'.$func.'" href="index.php?'.http_build_query(array(
      'page' => 'xform',
      'subpage' => 'manager',
      'tripage' => 'data_edit',
      'table_name' => $_params['table']['table_name'],
      'nestedsets' => $func
    ), NULL, '&amp;').'" data-confirm="'.$I18N->msg('xform_nestedsets_deactivate_confirm').'">'.$I18N->msg('xform_nestedsets_'.$func).'</a>';
  
  echo '<div class="rex-addon-output nestedsets-preparation">
          <div style="font-size:12px;font-weight:bold;" class="rex-hl2">
            <span style="float:left;">'.$I18N->msg('xform_nestedsets').'</span>
            <span style="float:right;">'.$func_link.'</span>
            <br style="clear:both;" />
          </div>
        </div>';
}

function xform_nestedsets_activate($_tablename)
{
  global $REX;
  
  $sql = rex_sql::factory();
  
  if(xform_nestedsets_table_is_nestedset($_tablename))
    return false;
  
  // prepare table
  $sql->flush();
  $sql->setQuery("ALTER TABLE `".$_tablename."`
    ADD COLUMN nestedset_lvl INT NOT NULL DEFAULT 0 AFTER id,
    ADD COLUMN nestedset_parent INT NULL AFTER id,
    ADD COLUMN nestedset_rgt INT NOT NULL DEFAULT 0 AFTER id,
    ADD COLUMN nestedset_lft INT NOT NULL DEFAULT 0 AFTER id,
    ADD INDEX nestedset_lft_index (nestedset_lft),
    ADD INDEX nestedset_rgt_index (nestedset_rgt),
    ADD INDEX nestedset_parent_index (nestedset_parent)
  ");
  
  // create root element
  $sql->setQuery("TRUNCATE `".$_tablename."`");
  $sql->flush();
  $sql->setTable($_tablename);
  $sql->setValues(array(
    'id' => 1,
    'nestedset_lft' => 0,
    'nestedset_rgt' => 1,
    'nestedset_lvl' => 0
  ));
  $sql->insert();
  
  // add xform table fields
  $sql->flush();
  $sql->setTable($REX['TABLE_PREFIX'].'xform_field');
  $sql->setValues(array(
    'table_name' => $_tablename,
    'prio' => 0,
    'type_id' => 'value',
    'type_name' => 'nestedsets_integer',
    'f1' => 'nestedset_lft',
    'f2' => 'Nested Set: Left',
    'list_hidden' => 1,
    'search' => 0
  ));
  $sql->insert();
  
  $sql->flush();
  $sql->setTable($REX['TABLE_PREFIX'].'xform_field');
  $sql->setValues(array(
    'table_name' => $_tablename,
    'prio' => 0,
    'type_id' => 'value',
    'type_name' => 'nestedsets_integer',
    'f1' => 'nestedset_rgt',
    'f2' => 'Nested Set: Right',
    'list_hidden' => 1,
    'search' => 0
  ));
  $sql->insert();
  
  $sql->flush();
  $sql->setTable($REX['TABLE_PREFIX'].'xform_field');
  $sql->setValues(array(
    'table_name' => $_tablename,
    'prio' => 0,
    'type_id' => 'value',
    'type_name' => 'nestedsets_integer',
    'f1' => 'nestedset_parent',
    'f2' => 'Nested Set: Parent',
    'list_hidden' => 1,
    'search' => 0
  ));
  $sql->insert();
  
  $sql->flush();
  $sql->setTable($REX['TABLE_PREFIX'].'xform_field');
  $sql->setValues(array(
    'table_name' => $_tablename,
    'prio' => 0,
    'type_id' => 'value',
    'type_name' => 'nestedsets_integer',
    'f1' => 'nestedset_lvl',
    'f2' => 'Nested Set: Level',
    'list_hidden' => 1,
    'search' => 0
  ));
  $sql->insert();
  
  $sql->flush();
  $sql->setTable($REX['TABLE_PREFIX'].'xform_field');
  $sql->setValues(array(
    'table_name' => $_tablename,
    'prio' => 0,
    'type_id' => 'action',
    'type_name' => 'nestedsets',
    'f1' => $_tablename,
    'list_hidden' => 1,
    'search' => 0
  ));
  $sql->insert();
  
  return true;
}

function xform_nestedsets_deactivate($_tablename)
{
  global $REX;
  
  $sql = rex_sql::factory();
  
  if(!xform_nestedsets_table_is_nestedset($_tablename))
    return false;
  
  // remove root element
  $sql->setQuery("DELETE FROM `".$_tablename."` WHERE nestedset_parent IS NULL");
  
  // remove columns and indexes
  $sql->setQuery("ALTER TABLE `".$_tablename."`
    DROP INDEX nestedset_lft_index,
    DROP INDEX nestedset_rgt_index,
    DROP INDEX nestedset_parent_index,
    DROP COLUMN nestedset_lft,
    DROP COLUMN nestedset_rgt,
    DROP COLUMN nestedset_parent,
    DROP COLUMN nestedset_lvl
  ");
  
  // remove xform table fields
  $sql->setQuery("DELETE FROM `".$sql->escape($REX['TABLE_PREFIX'].'xform_field')."` WHERE table_name = '".$sql->escape($_tablename)."' AND type_name LIKE 'nestedsets%'");
  
  return true;
}
