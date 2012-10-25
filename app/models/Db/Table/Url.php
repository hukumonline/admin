<?php
/**
 * @author	2011-2012 Nihki Prihadi <nihki@madaniyah.com>
 * @version $Id: urls.php 1 2012-02-14 13:07Z $
 */

class App_Model_Db_Table_Url extends Zend_Db_Table_Abstract
{
	protected $_name = 'urls';
	
    protected function  _setupDatabaseAdapter()
    {
        $this->_db = Zend_Registry::get('db4');

        parent::_setupDatabaseAdapter();
    }
}