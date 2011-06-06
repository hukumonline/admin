<?php

/**
 * Description of AroGroupIn
 *
 * @author nihki <nihki@madaniyah.com>
 */
class App_Model_Db_Table_Migration_AroIn extends Zend_Db_Table_Abstract
{
    protected $_name = 'gacl_aro';

    protected function  _setupDatabaseAdapter()
    {
        $this->_db = Zend_Registry::get('db3');
        parent::_setupDatabaseAdapter();
    }
}
