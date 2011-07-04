<?php
class App_Model_Db_Table_Promotion extends Zend_Db_Table_Abstract 
{
	protected $_name = 'KutuUserPromotion';
	protected $_schema = 'hid';
    protected function  _setupDatabaseAdapter()
    {
        $this->_db = Zend_Registry::get('db2');

        parent::_setupDatabaseAdapter();
    }
}