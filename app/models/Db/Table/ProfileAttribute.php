<?php

/**
 * Description of ProfileAttribute
 *
 * @author nihki <nihki@madaniyah.com>
 */
class App_Model_Db_Table_ProfileAttribute extends Zend_Db_Table_Abstract
{
    protected $_name = 'KutuProfileAttribute';
    
    protected $_referenceMap    = array(
        'Profile' => array(
            'columns'           => 'profileGuid',
            'refTableClass'     => 'App_Model_Db_Table_Profile',
            'refColumns'        => 'guid'
        ),
        'Attribute' => array(
            'columns'           => 'attributeGuid',
            'refTableClass'     => 'App_Model_Db_Table_Attribute',
            'refColumns'        => 'guid'
        )
     );

    protected function  _setupDatabaseAdapter()
    {
        $zl = Zend_Registry::get('Zend_Locale');
        if ($zl->getLanguage() == 'id')
            $this->_db = Zend_Registry::get('db1');
        else
            $this->_db = Zend_Registry::get('db3');

        parent::_setupDatabaseAdapter();
    }
}
