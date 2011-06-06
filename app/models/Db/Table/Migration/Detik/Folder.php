<?php

/**
 * Description of Folder
 *
 * @author nihki <nihki@madaniyah.com>
 */
class App_Model_Db_Table_Migration_Detik_Folder extends Zend_Db_Table_Abstract
{
    protected $_name = 'KutuFolder';
    //protected $_rowClass = 'App_Model_Db_Table_Row_Folder';
    //protected $_dependentTables = array('App_Model_Db_Table_CatalogFolder');
    protected function  _setupDatabaseAdapter()
    {
        $zl = Zend_Registry::get('Zend_Locale');
        $this->_db = Zend_Registry::get('db4');
        
        parent::_setupDatabaseAdapter();
    }
    public function fetchChildren($parentGuid)
    {
    	if($parentGuid == 'root')
    	{
    		return $this->fetchAll("parentGuid=guid",'title ASC');
    	}
    	else 
    	{
			return $this->fetchAll("parentGuid = '$parentGuid' AND NOT parentGuid=guid",'title ASC');
    	}
    }
}
