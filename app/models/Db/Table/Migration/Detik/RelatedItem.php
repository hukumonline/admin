<?php

/**
 * Description of RelatedItem
 *
 * @author nihki <nihki@madaniyah.com>
 */
class App_Model_Db_Table_Migration_Detik_RelatedItem extends Zend_Db_Table_Abstract
{
    protected $_name = 'KutuRelatedItem';
    
    /*
    protected $_rowClass = 'App_Model_Db_Table_Row_RelatedItem';
    protected $_referenceMap    = array(
        'Catalog' => array(
            'columns'           => 'itemGuid',
            'refTableClass'     => 'Kutu_Core_Orm_Table_Catalog',
            'refColumns'        => 'guid'
        ),
        'RelatedCatalog' => array(
            'columns'           => 'relatedGuid',
            'refTableClass'     => 'Kutu_Core_Orm_Table_Catalog',
            'refColumns'        => 'guid'
        )
    );
	*/

    protected function  _setupDatabaseAdapter()
    {
        $zl = Zend_Registry::get('Zend_Locale');
        $this->_db = Zend_Registry::get('db4');

        parent::_setupDatabaseAdapter();
    }
    function createNew()
    {
    	return $this->createRow(array('itemGuid'=>'', 'relatedGuid'=>'','relateAs'=>''));
    }
}
