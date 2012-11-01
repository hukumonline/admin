<?php

/**
 * Description of RelatedItem
 *
 * @author nihki <nihki@madaniyah.com>
 */
class App_Model_Db_Table_RelatedItem extends Zend_Db_Table_Abstract
{
    protected $_name = 'KutuRelatedItem';
    protected $_rowClass = 'App_Model_Db_Table_Row_RelatedItem';
    protected $_referenceMap    = array(
        'Catalog' => array(
            'columns'           => 'itemGuid',
            'refTableClass'     => 'App_Model_Db_Table_Catalog',
            'refColumns'        => 'guid'
        ),
        'RelatedCatalog' => array(
            'columns'           => 'relatedGuid',
            'refTableClass'     => 'App_Model_Db_Table_Catalog',
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
    public function insert (array $data)
    {
    	return parent::insert($data);
    }
    function createNew()
    {
    	return $this->createRow(array('itemGuid'=>'', 'relatedGuid'=>'','relateAs'=>''));
    }
    public function delete($where)
    {
        return parent::delete($where);
    }
}
