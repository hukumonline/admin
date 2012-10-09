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
    
	public function getProfile($profileId = null, $offset = null, $count = null)
	{
		$db = parent::_dbSelect();
		$select = $db->from('KutuProfile');
							
		$conn = self::$_db;
		
		if (isset($profileId)) {
			$select->where('guid = ?', $profileId);
			$rs = $conn->fetchRow($select);
			return (null == $rs) ? null : $rs;
		}
		else 
		{
			if (is_int($offset) && is_int($count)) {
				$select->limit($count, $offset);
			}
			$select->order('guid desc');
			$rs = $conn->fetchAll($select);
			return $rs;							
		}
	}

    public function getYear()
    {
        $db = parent::_dbSelect();
        $statement = $db->from('KutuCatalog',array('YEAR( `createdDate` ) as cd'))
                ->group('YEAR( `createdDate` )')
                ->order('cd desc');

        $conn = self::$_db;

        $row = $conn->fetchAll($statement);

        return $row;
    }
    
    public function getCatalogByMonth($profile,$mon)
    {
        $db = parent::_dbSelect();
        $statement = $db->from('KutuCatalog',array('COUNT(*) as count'))
                ->where("createdDate LIKE '%".$mon."%'");
                
        if ($profile == 'peraturan')
        	$statement->where("profileGuid IN ('kutu_peraturan','kutu_peraturan_kolonial','kutu_rancangan_peraturan')");
        else if ($profile == 'article')
        	$statement->where("profileGuid IN ('article','isuhangat')");
       	else
        	$statement->where('profileGuid=?', $profile);

    	/*$sql = $statement->__toString();
    	print_r($sql);exit();*/
    		
        $conn = self::$_db;

    	$row = $conn->fetchRow($statement);
    	
    	return ($row !== null) ? $row['count'] : 0;
    }
    
    public function getEnglishCatalogByMonth($profile,$mon)
    {
        $db = parent::_dbSelect();
        $statement = $db->from('KutuCatalog',array('COUNT(*) as count'))
                ->where("createdDate LIKE '%".$mon."%'");
                
        if ($profile == 'news')
        	$statement->where("profileGuid IN ('news','article','hot_news')");
        else if ($profile == 'ilb')
        	$statement->where("profileGuid IN ('ilb','hot_issue_ilb','executive_alert','consumer_goods','financial_services','general_corporate','manufacturing_&_industry','oil_and_gas','telecommunications_and_media')");
        else if ($profile == 'ild')
        	$statement->where("profileGuid IN ('ild','hot_issue_ild','executive_summary')");
       	else
        	$statement->where('profileGuid=?', $profile);

    	/*$sql = $statement->__toString();
    	print_r($sql);exit();*/
    		
        $conn = Zend_Registry::get('db3');

    	$row = $conn->fetchRow($statement);
    	
    	return ($row !== null) ? $row['count'] : 0;
    }
    
    public function getCatalogBy($profileGuid)
    {
    	$db = parent::_dbSelect();
    	$sql = $db->from('KutuCatalog',array('guid', 'createdBy'))
    			->where("profileGuid = ?", $profileGuid)
    			->group('createdBy');
    			
//    	$sql = $sql->__toString();
//    	print_r($sql);exit();
    		
        $conn = self::$_db;

        $row = $conn->fetchAll($sql);

        return $row;
    }
}
