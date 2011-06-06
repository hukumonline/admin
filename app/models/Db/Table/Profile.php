<?php

/**
 * Description of Profile
 *
 * @author nihki <nihki@madaniyah.com>
 */
class App_Model_Db_Table_Profile extends Zend_Db_Table_Abstract
{
    protected $_name = 'KutuProfile';
    protected $_dependentTables = array('App_Model_Db_Table_ProfileAttribute', 'App_Model_Db_Table_Catalog');
    protected function  _setupDatabaseAdapter()
    {
        $zl = Zend_Registry::get('Zend_Locale');
        if ($zl->getLanguage() == 'id')
            $this->_db = Zend_Registry::get('db1');
        else
            $this->_db = Zend_Registry::get('db3');

        parent::_setupDatabaseAdapter();
    }
}
