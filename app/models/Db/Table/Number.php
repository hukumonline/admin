<?php

/**
 * Description of Number
 *
 * @author nihki <nihki@madaniyah.com>
 */
class App_Model_Db_Table_Number extends Zend_Db_Table_Abstract
{
    protected $_name = 'KutuNumber';
    protected $_schema = 'hid';
    protected function  _setupDatabaseAdapter()
    {
        $this->_db = Zend_Registry::get('db1');

        parent::_setupDatabaseAdapter();
    }
}
