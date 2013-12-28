<?php

/**
 * XForm-Plugin: Nested Sets
 * @author robert.rupf[at]maumha[dot]de Robert Rupf
 * @author <a href="http://www.maumha.de">www.maumha.de</a>
 */

function xform_nestedsets_prepare_list_sql($_params)
{
  if(!xform_nestedsets_table_is_nestedset($_params['table']['table_name']))
    return $_params['subject'];
  
  if(stripos($_params['subject'], ' from `'.$_params['table']['table_name'].'` where'))
  {
    return str_ireplace(' from `'.$_params['table']['table_name'].'` where', ' FROM `'.$_params['table']['table_name'].'` WHERE (id != 1 AND nestedset_parent IS NOT NULL) AND ', $_params['subject']);
  }
  else
  {
    return $_params['subject'].' WHERE (id != 1 AND nestedset_parent IS NOT NULL)';
  }
}

function xform_nestedsets_extendlist($_params)
{
  global $REX, $I18N;
  
  xform_nestedsets_show_deactivate($_params);
  
  if(!xform_nestedsets_table_is_nestedset($_params['table']['table_name']))
    return $_params['subject'];
  
  $func = rex_get('func');
  $data_id = rex_get('data_id', 'int');
  $data = array();
  if($data_id)
  {
    $sql = rex_sql::factory();
    $sql->setQuery('SELECT * FROM '.$_params['table']['table_name'].' WHERE id='.$data_id);
    if($sql->getRows())
    {
      $data = $sql->getRow();
    }
    else
    {
      $data_id = '';
    }
  }
  
  if($func == 'moveup' OR $func == 'movedown')
  {
    if($func == 'moveup')
    {
      $sql = rex_sql::factory();
      $sql->setQuery('SELECT MAX(nestedset_rgt) as nestedset_rgt FROM `'.$_params['table']['table_name'].'`');
      $maxrgt = $sql->getValue('nestedset_rgt');
      $sql->flush();
      $sql->setQuery('
        SELECT id, nestedset_lft, nestedset_rgt
        FROM `'.$_params['table']['table_name'].'`
        WHERE nestedset_rgt = '.($data['nestedset_lft'] - 1).'
        AND nestedset_lvl = '.$data['nestedset_lvl'].'
      ');
      
      if($sql->getRows())
      {
        $qry1 = 'UPDATE `'.$_params['table']['table_name'].'` SET nestedset_lft = nestedset_lft + '.($data['nestedset_rgt']-$data['nestedset_lft']+1).' - '.$maxrgt.', nestedset_rgt = nestedset_rgt + '.($data['nestedset_rgt']-$data['nestedset_lft']+1).' - '.$maxrgt.' WHERE nestedset_lft >= '.$sql->getValue('nestedset_lft').' AND nestedset_rgt <= '.$sql->getValue('nestedset_rgt');
        $qry2 = 'UPDATE `'.$_params['table']['table_name'].'` SET nestedset_lft = nestedset_lft - '.($sql->getValue('nestedset_rgt')-$sql->getValue('nestedset_lft')+1).', nestedset_rgt = nestedset_rgt - '.($sql->getValue('nestedset_rgt')-$sql->getValue('nestedset_lft')+1).' WHERE nestedset_lft >= '.$data['nestedset_lft'].' AND nestedset_rgt <= '.$data['nestedset_rgt'].' AND nestedset_lvl > 0';
        $qry3 = 'UPDATE `'.$_params['table']['table_name'].'` SET nestedset_lft = nestedset_lft + '.$maxrgt.', nestedset_rgt = nestedset_rgt + '.$maxrgt.' WHERE nestedset_lft < 0';
        
        $sql->flush();
        $sql->setQuery($qry1);
        $sql->setQuery($qry2);
        $sql->setQuery($qry3);
      }
    }
    
    if($func == 'movedown')
    {
      $sql = rex_sql::factory();
      $sql->setQuery('SELECT (nestedset_rgt) as nestedset_rgt FROM `'.$_params['table']['table_name'].'`');
      $maxrgt = $sql->getValue('nestedset_rgt');
      $sql->flush();
      $sql->setQuery('
        SELECT id,nestedset_lft,nestedset_rgt
        FROM `'.$_params['table']['table_name'].'`
        WHERE nestedset_lft = '.($data['nestedset_rgt'] + 1).'
        AND nestedset_lvl = '.$data['nestedset_lvl'].'
      ');
      
      if($sql->getRows())
      {
        $qry1 = 'UPDATE `'.$_params['table']['table_name'].'` SET nestedset_lft = nestedset_lft - '.($data['nestedset_rgt']-$data['nestedset_lft']+1).' - '.$maxrgt.', nestedset_rgt = nestedset_rgt - '.($data['nestedset_rgt']-$data['nestedset_lft']+1).' - '.$maxrgt.' WHERE nestedset_lft >= '.$sql->getValue('nestedset_lft').' AND nestedset_rgt <= '.$sql->getValue('nestedset_rgt');
        $qry2 = 'UPDATE `'.$_params['table']['table_name'].'` SET nestedset_lft = nestedset_lft + '.($sql->getValue('nestedset_rgt')-$sql->getValue('nestedset_lft')+1).', nestedset_rgt = nestedset_rgt + '.($sql->getValue('nestedset_rgt')-$sql->getValue('nestedset_lft')+1).' WHERE nestedset_lft >= '.$data['nestedset_lft'].' AND nestedset_rgt <= '.$data['nestedset_rgt'].' AND nestedset_lvl > 0';
        $qry3 = 'UPDATE `'.$_params['table']['table_name'].'` SET nestedset_lft = nestedset_lft + '.$maxrgt.', nestedset_rgt = nestedset_rgt + '.$maxrgt.' WHERE nestedset_lft < 0';
        
        $sql->flush();
        $sql->setQuery($qry1);
        $sql->setQuery($qry2);
        $sql->setQuery($qry3);
      }
    }
    
    header('Location: http://'.$_SERVER['HTTP_HOST'].substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], '/')).'/index.php?&page=xform&subpage=manager&tripage=data_edit&table_name='.$_params['table']['table_name']);
    exit;
  }
  
  $list = $_params['subject'];
  
  $list->setColumnLayout($I18N->msg('delete'), array('<th>###VALUE###</th>', '<td onclick="return confirm(\''.htmlspecialchars($I18N->msg('xform_nestedsets_delete')).'\');">###VALUE###</td>'));
  
  // move up link
  $list->addColumn('▲','▲');
  $list->setColumnParams('▲', array('data_id'=>'###id###','func'=>'moveup'));
  
  // move down link
  $list->addColumn('▼','▼');
  $list->setColumnParams('▼', array('data_id'=>'###id###','func'=>'movedown'));
  
  // add child link
  $list->addColumn($I18N->msg('xform_nestedsets_add'), $I18N->msg('xform_nestedsets_add'));
  $list->setColumnParams($I18N->msg('xform_nestedsets_add'), array('data_id'=>'###id###', 'func'=>'add', 'nestedset_parent'=>'###id###'));
  
  // remove sortability
  foreach($list->columnOptions as $colname => $option)
    unset($list->columnOptions[$colname][REX_LIST_OPT_SORT]);
  
  // order by LFT ASC
  $_REQUEST['list'] = $list->getName();
  $_REQUEST['sort'] = 'nestedset_lft';
  $_REQUEST['sorttype'] = 'asc';
  
  $list->sql->setQuery($list->prepareQuery($list->query));
  
  // add padding
  foreach($list->getColumnNames() as $colname)
  {
    if($colname == 'id')
      continue;
      
    if(!is_array($colname) AND in_array($colname, $list->columnDisabled))
      continue;
    
    $list->setColumnLayout($colname, array('<th>###VALUE###</th>', '<td class="nestedset lvl-###nestedset_lvl###">###VALUE###</td>'));
    break;
  }
  
  return $list;
}