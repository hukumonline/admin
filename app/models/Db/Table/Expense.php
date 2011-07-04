<?php

/**
 * Description of Expense
 *
 * @author nihki <nihki@madaniyah.com>
 */
class App_Model_Db_Table_Expense extends Zend_Db_Table_Abstract
{
    protected $_name = 'KutuUserExpense';
    protected $_schema = 'hid';
    protected function  _setupDatabaseAdapter()
    {
        $this->_db = Zend_Registry::get('db2');

        parent::_setupDatabaseAdapter();
    }
}
