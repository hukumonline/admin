<?php
class App_Model_Db_Table_Catalog extends Zend_Db_Table_Abstract
{
    protected $_name = 'KutuCatalog';
    protected $_rowClass = 'App_Model_Db_Table_Row_Catalog';
    protected $_rowsetClass = 'App_Model_Db_Table_Rowset_Catalog';
    protected $_dependentTables = array('App_Model_Db_Table_CatalogAttribute','App_Model_Db_Table_CatalogFolder');
    
    protected function  _setupDatabaseAdapter()
    {
        $zl = Zend_Registry::get('Zend_Locale');
        if ($zl->getLanguage() == 'id')
            $this->_db = Zend_Registry::get('db1');
        else
            $this->_db = Zend_Registry::get('db3');

        parent::_setupDatabaseAdapter();
    }
    
	public function convert($entity)
	{
		return new App_Model_Catalog($entity); 
	}
	
    public function fetchCatalogInFolder($folderGuid = null, $start = null, $end = null, $order = null, $attr = null)
    {
    	$select = $this->select()->from('KutuCatalog');
    
		if (in_array($folderGuid, ['lt4b11ece54d870','lt4b11e8fde1e42','lt4b11ecf5408d2','lt4b11e8c86c8a4'])) {
    		$select->where('profileGuid=?', 'klinik');
    	}
    		
    	// Approved, Draft, NA, Published
    	if ($folderGuid !== null & !in_array($folderGuid, ['lt4b11ece54d870','lt4b11e8fde1e42','lt4b11ecf5408d2','lt4b11e8c86c8a4'])) {
    		$select->join('KutuCatalogFolder','KutuCatalog.guid=KutuCatalogFolder.catalogGuid',array())
    		->where('KutuCatalogFolder.folderGuid=?',$folderGuid);
    	}
    		
    	if ($attr) {
    		if (isset($attr['status']) && ($attr['status']!== '')) {
    			$select->where('status = ?', $attr['status']);
    		}
    	}
    	
    	if ($order !== null) {
    		$select->order('KutuCatalog.'.$order);
    	}
    		
    	if ($start !== null || $end !== null) {
    		$select->limit($end,$start);
    	}
    	/*$sql = $select->__toString();
    		print_r($sql);die;*/
    	$rs = $select->query()->fetchAll();
    	return new Pandamp_Model_RecordSet($rs, $this);
    }
    
	public function getCountCatalogInFolder($folderGuid=null, $attr = null)
	{
		$select = $this->select()
				  ->from('KutuCatalog',array('num_files'=>'COUNT(*)'));
		
		if (in_array($folderGuid, ['lt4b11ece54d870','lt4b11e8fde1e42','lt4b11ecf5408d2','lt4b11e8c86c8a4'])) {
    		$select->where('profileGuid=?', 'klinik');
    	}
    		
    	// Approved, Draft, NA, Published
    	if ($folderGuid !== null & !in_array($folderGuid, ['lt4b11ece54d870','lt4b11e8fde1e42','lt4b11ecf5408d2','lt4b11e8c86c8a4'])) {
			$select->join('KutuCatalogFolder','KutuCatalog.guid=KutuCatalogFolder.catalogGuid',array())
			->where('KutuCatalogFolder.folderGuid=?',$folderGuid);
		}
		
		if ($attr) {
			if (isset($attr['status']) && !empty($attr['status'])) {
				$select->where('status = ?', $attr['status']);
			}
		}
		 
		$row = $select->query()->fetch();
		return ($row) ? $row['num_files'] : 0;
	}
	
}