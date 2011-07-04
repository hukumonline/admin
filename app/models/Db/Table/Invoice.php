<?php

/**
 * Description of User
 *
 * @author nihki <nihki@madaniyah.com>
 */
class App_Model_Db_Table_Invoice extends Zend_Db_Table_Abstract
{
    protected $_name = 'KutuUserInvoice';
    protected $_schema = 'hid';
    protected $_referenceMap = array(
        'User' => array(
            'columns'       => array('uid'),
            'refTableClass' => array('App_Model_Db_Table_User'),
            'refColumns'    => array('kopel')
        )
    );
    protected function  _setupDatabaseAdapter()
    {
        $this->_db = Zend_Registry::get('db2');

        parent::_setupDatabaseAdapter();
    }
}
