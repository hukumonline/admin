<?php

/**
 * Description of Catalog
 *
 * @author nihki <nihki@madaniyah.com>
 */

class App_Model_Show_Catalog extends App_Model_Db_DefaultAdapter
{
    /**
     * class instance object
     */
    private static $_instance;

    private static $_db;

    /**
     * de-activate constructor
     */
    final private function  __construct()
    {
        $zl = Zend_Registry::get("Zend_Locale");
        if ($zl->getLanguage() == 'id')
            self::$_db = Zend_Registry::get('db1');
        else
            self::$_db = Zend_Registry::get('db3');
    }

     /**
      * de-activate object cloning
      */
    final private function  __clone() {}

    /**
     * @return obj
     */
    public function show()
    {
        if (!isset(self::$_instance)) {
                $show = __CLASS__;
                self::$_instance = new $show;
        }
        return self::$_instance;
    }

    /**
     * @return obj
     */
    public function fetchFromFolder($folderGuid, $start = 0 , $end = 0)
    {
        $now = date('Y-m-d H:i:s');
        $db = parent::_dbSelect();
        $statement = $db->from('KutuCatalog')
                        ->join('KutuCatalogFolder','KutuCatalog.guid=KutuCatalogFolder.catalogGuid',array())
                        ->where('KutuCatalog.status=?',99)
                        ->where('KutuCatalogFolder.folderGuid=?',$folderGuid)
                        ->where("KutuCatalog.publishedDate = '0000-00-00 00:00:00' OR KutuCatalog.publishedDate <= '$now'")
                        ->where("KutuCatalog.expiredDate = '0000-00-00 00:00:00' OR KutuCatalog.expiredDate >= '$now'")
                        ->order('KutuCatalog.publishedDate DESC')
                        ->limit($end,$start);

        $conn = self::$_db;

        $result = $conn->fetchAll($statement);

        return $result;
    }
    public function getCatalogByGuid($guid)
    {
        $db = parent::_dbSelect();
        $statement = $db->from('KutuCatalog')->where('guid=?', $guid);

        $conn = self::$_db;

        $row = $conn->fetchRow($statement);

        return $row;
    }
    /*
    public function fetchCatalogByStatus($status, $profile='klinik')
    {
        $db = parent::_dbSelect();
        $statement = $db->from('KutuCatalog',array('guid'))
                ->where('profileGuid=?', "$profile")
                ->where('status=?', $status);

        $conn = self::$_db;

        $row = $conn->fetchAll($statement);

        return $row;
    }
    */
    public function fetchCatalogInFolder($folderGuid,$start = 0 ,$end = 0, $sort, $sortBy)
    {
    	$db = parent::_dbSelect();
    	$select = $db->from('KutuCatalog')
    				->join('KutuCatalogFolder','KutuCatalog.guid=KutuCatalogFolder.catalogGuid',array())
    				->where('KutuCatalogFolder.folderGuid=?',$folderGuid)
    				->where('KutuCatalog.status IN ("-1","0","1","2","99")')
    				->order('KutuCatalog.'.$sort.' '.$sortBy)
    				->limit($end,$start);

		$conn = self::$_db;
		    					
    	$rows = $conn->fetchAll($select);

        return $rows;
    }
    public function getCountCatalogsInFolder($folderGuid)
    {
    	$db = parent::_dbSelect();
    	$select = $db->from('KutuCatalog',array('COUNT(*) as count'))
    				->join('KutuCatalogFolder','KutuCatalog.guid=KutuCatalogFolder.catalogGuid',array())
    				->where('KutuCatalogFolder.folderGuid=?',$folderGuid)
    				->where('KutuCatalog.status IN ("-1","0","1","2","99")');

		$conn = self::$_db;    				
    				
    	$row = $conn->fetchRow($select);
    	
    	return ($row !== null) ? $row['count'] : 0;
    }
    function fetchFromFolderAdminClinic($status, $start = 0 ,$end = 0, $sort, $sortBy)
    {
    	$db = parent::_dbSelect();
    	$conn = self::$_db;    				
    	
    	if ($status == 0)
    		$row = $conn->fetchAll($db->from('KutuCatalog')->where('profileGuid=?', 'klinik')->where('status=?', 0)->order('createdDate DESC')->limit($end,$start));
    	else 
    		$row = $conn->fetchAll($db->from('KutuCatalog')->where('profileGuid=?', 'klinik')->where('status=?', $status)->order($sort.' '.$sortBy)->limit($end,$start));

    		
    	return $row;
    }
    function countCatalogsInFolderClinic($status)
    {
    	$db = parent::_dbSelect();
    	$select = $db->from('KutuCatalog',array('COUNT(*) as count'))
    				->where("profileGuid='klinik' AND status=$status");
    				
		$conn = self::$_db;    				    				
    				
    	$row = $conn->fetchRow($select);
    	
    	return ($row !== null) ? $row['count'] : 0;
    }
    public function fetchCatalogInFolder4Mig($folderGuid)
    {
    	$db = parent::_dbSelect();
    	$select = $db->from('KutuCatalog')
    				->join('KutuCatalogFolder','KutuCatalog.guid=KutuCatalogFolder.catalogGuid',array())
    				->where('KutuCatalogFolder.folderGuid=?',$folderGuid);

		$conn = self::$_db;
		    					
    	$rows = $conn->fetchAll($select);

        return $rows;
    }
}
?>
