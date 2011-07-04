<?php

/**
 * Description of State
 *
 * @author nihki <nihki@madaniyah.com>
 */
class App_Model_Db_Table_State extends Zend_Db_Table_Abstract
{
    protected $_name = 'KutuUserProvinces';
    protected $_schema = 'hid';
    protected function  _setupDatabaseAdapter()
    {
        $this->_db = Zend_Registry::get('db2');

        parent::_setupDatabaseAdapter();
    }
}
