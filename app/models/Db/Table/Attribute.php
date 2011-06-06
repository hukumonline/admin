<?php

/**
 * Description of Attribute
 *
 * @author nihki <nihki@madaniyah.com>
 */
class App_Model_Db_Table_Attribute extends Zend_Db_Table_Abstract
{
    protected $_name = 'KutuAttribute';
    protected $_dependentTables = array('App_Model_Db_Table_CatalogAttribute','Kutu_Core_Orm_Table_ProfileAttribute');

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
