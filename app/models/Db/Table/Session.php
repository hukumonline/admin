<?php
/**
 * Description of User
 *
 * @author nihki <nihki@madaniyah.com>
 */

class App_Model_Db_Table_Session extends Zend_Db_Table_Abstract
{
    protected $_name = 'session';
    
    protected function  _setupDatabaseAdapter()
    {
        $this->_db = Zend_Registry::get('db2');

        parent::_setupDatabaseAdapter();
    }
}
