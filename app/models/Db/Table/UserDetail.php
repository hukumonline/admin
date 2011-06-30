<?php

/**
 * Description of UserDetail
 *
 * @author nihki <nihki@madaniyah.com>
 */
class App_Model_Db_Table_UserDetail extends Zend_Db_Table_Abstract
{
    protected $_name = 'KutuUserDetail';
    protected $_schema = 'hid';
    protected $_referenceMap = array(
        'User' => array(
            'columns'		=> array('userId'),
            'refTableClass'	=> 'App_Model_Db_Table_User',
            'refColumns'	=> array('kopel')
        )
    );
    protected function  _setupDatabaseAdapter()
    {
        $this->_db = Zend_Registry::get('db2');

        parent::_setupDatabaseAdapter();
    }
    
}
