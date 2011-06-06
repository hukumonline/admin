<?php

/**
 * Description of CatalogFolder
 *
 * @author nihki <nihki@madaniyah.com>
 */
class App_Model_Db_Table_CatalogFolder extends Zend_Db_Table_Abstract
{
    protected $_name = 'KutuCatalogFolder';
    protected $_referenceMap    = array(
        'Catalog' => array(
            'columns'           => 'catalogGuid',
            'refTableClass'     => 'App_Model_Db_Table_Catalog',
            'refColumns'        => 'guid'
        ),
        'Folder' => array(
            'columns'           => 'folderGuid',
            'refTableClass'     => 'App_Model_Db_Table_Folder',
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
