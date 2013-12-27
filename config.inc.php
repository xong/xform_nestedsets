<?php

/**
 * XForm-Plugin: Nested Sets
 * @author robert.rupf[at]maumha[dot]de Robert Rupf
 * @author <a href="http://www.maumha.de">www.maumha.de</a>
 */

$mypage = 'nestedsets';

$REX['ADDON']['xform']['classpaths']['value'][] = $REX['INCLUDE_PATH'] . '/addons/xform/plugins/'.$mypage.'/classes/value/';
$REX['ADDON']['xform']['classpaths']['action'][] = $REX['INCLUDE_PATH'] . '/addons/xform/plugins/'.$mypage.'/classes/action/';

if ($REX['REDAXO'] && !$REX['SETUP'])
{
  // language file
  $I18N->appendFile($REX['INCLUDE_PATH'] . '/addons/xform/plugins/'.$mypage.'/lang/');
  
  // disable/enable function for nested sets
  if(rex_request('page') == 'xform' AND rex_request('subpage') == 'manager')
  {
    require_once $REX['INCLUDE_PATH'].'/addons/xform/plugins/'.$mypage.'/functions/function.misc.inc.php';
    require_once $REX['INCLUDE_PATH'].'/addons/xform/plugins/'.$mypage.'/functions/function.deactivate_nestedset.inc.php';
    require_once $REX['INCLUDE_PATH'].'/addons/xform/plugins/'.$mypage.'/functions/function.extendlist.inc.php';
    require_once $REX['INCLUDE_PATH'].'/addons/xform/plugins/'.$mypage.'/functions/function.delete.inc.php';
    
    rex_register_extension('XFORM_DATA_LIST_SQL', 'xform_nestedsets_prepare_list_sql');
    rex_register_extension('XFORM_DATA_LIST', 'xform_nestedsets_extendlist');
    rex_register_extension('XFORM_DATA_DELETE', 'xform_nestedsets_delete');
    rex_register_extension('XFORM_DATA_DATASET_DELETE', 'xform_nestedsets_dataset_delete');
    rex_register_extension('XFORM_DATA_TABLE_TRUNCATED', 'xform_nestedsets_truncate');
    
    function rex_xform_nestedsets_css($params)
    {
      global $REX;
      
      $params['subject'] .= "\n  " . '<link rel="stylesheet" type="text/css" href="'.$REX['HTDOCS_PATH'].'files/addons/xform/plugins/nestedsets/style.css" />';
      $params['subject'] .= "\n  " . '<script src="'.$REX['HTDOCS_PATH'].'files/addons/xform/plugins/nestedsets/scripts.js" type="text/javascript"></script>';
      
      return $params['subject'];
    }
    
    rex_register_extension('PAGE_HEADER', 'rex_xform_nestedsets_css');
  }
}
