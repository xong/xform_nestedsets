<?php

/**
 * XForm-Plugin: Nested Sets
 * @author robert.rupf[at]maumha[dot]de Robert Rupf
 * @author <a href="http://www.maumha.de">www.maumha.de</a>
 */

class rex_xform_action_nestedsets extends rex_xform_action_abstract
{
	
	function execute()
	{
    static $first = true;
    
    // call only once ...
    if(!$first)
      return true;
    
    $first = false;
    
    // ... and only on add (not update)
    if($this->params['main_id'] > 0)
      return true;
    
    $table = $this->getElement(2);
    $parent = rex_request('nestedset_parent', 'int', 1);
    if(!$parent)
      $parent = 1;
    $sql = rex_sql::factory();
    $sql->setTable($table);
    
    // select lft and rgt
    $sql->setWhere('id = '.$parent);
    
    if(!$sql->select('id, nestedset_rgt, nestedset_lvl'))
      return false; // error
    
    $id = $sql->getValue('id');
    $rgt = $sql->getValue('nestedset_rgt');
    $lvl = $sql->getValue('nestedset_lvl');
    
    // update rgt
    $sql->setQuery(
      sprintf('
        UPDATE `%s`
        SET nestedset_rgt = nestedset_rgt + 2
        WHERE nestedset_rgt >= %d',
        $table,
        $rgt
      )
    );
    
    // update lft
    $sql->setQuery(
      sprintf('
        UPDATE `%s`
        SET nestedset_lft = nestedset_lft + 2
        WHERE nestedset_lft > %d',
        $table,
        $rgt
      )
    );
    
    // new data
    $this->params['value_pool']['sql']['nestedset_lft'] = $rgt;
    $this->params['value_pool']['sql']['nestedset_rgt'] = $rgt + 1;
    $this->params['value_pool']['sql']['nestedset_parent'] = $parent;
    $this->params['value_pool']['sql']['nestedset_lvl'] = $lvl + 1;
	}

	function getDescription()
	{
		return "action|nestedsets|tblname";
	}

}

?>