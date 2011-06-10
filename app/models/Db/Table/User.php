<?php

/**
 * Description of User
 *
 * @author nihki <nihki@madaniyah.com>
 */
class App_Model_Db_Table_User extends Zend_Db_Table_Abstract
{
    protected $_name = 'KutuUser';
    protected $_schema = 'hid';
    protected $_rowClass = 'App_Model_Db_Table_Row_User';
    protected $_dependentTables = array(
        'App_Model_Db_Table_UserDetail',
        'App_Model_Db_Table_UserLog',
        'App_Model_Db_Table_UserFinance',
        'App_Model_Db_Table_Order',
        'App_Model_Db_Table_Invoice'
    );
    protected function  _setupDatabaseAdapter()
    {
        $this->_db = Zend_Registry::get('db2');

        parent::_setupDatabaseAdapter();
    }
}