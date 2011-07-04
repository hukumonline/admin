<?php

/**
 * Description of UserStatus
 *
 * @author nihki <nihki@madaniyah.com>
 */
class App_Model_Db_Table_UserStatus extends Zend_Db_Table_Abstract
{
    protected $_name = 'KutuUserStatus';
    protected $_schema = 'hid';
    protected function  _setupDatabaseAdapter()
    {
        $this->_db = Zend_Registry::get('db2');

        parent::_setupDatabaseAdapter();
    }
}
