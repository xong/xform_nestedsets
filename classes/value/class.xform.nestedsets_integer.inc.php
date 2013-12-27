<?php

/**
 * XForm-Plugin: Nested Sets
 * @author robert.rupf[at]maumha[dot]de Robert Rupf
 * @author <a href="http://www.maumha.de">www.maumha.de</a>
 */

class rex_xform_nestedsets_integer extends rex_xform_abstract
{

  function enterObject()
  {
    if($v = rex_request($this->getName(), 'int', 0))
      $this->setValue($v);
    
    $this->params['form_output'][$this->getId()] = '<input type="hidden" name="'.$this->getName().'" value="'.$this->getValue().'" />';
  }

  function getDescription()
  {
    return 'nestedsets_integer -> Beispiel: nestedsets_integer|name|label';
  }

  function getDefinitions()
  {
    return array(
      'type' => 'value',
      'name' => 'nestedsets_integer',
      'values' => array(
        array( 'type' => 'name',   'label' => 'Feld' ),
        array( 'type' => 'text',    'label' => 'Bezeichnung')
      ),
      'description' => 'Nestedsets-Datentyp',
      'dbtype' => 'int',
      'famous' => false
    );

  }
}
