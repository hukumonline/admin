<?php

/**
 * Description of Order
 *
 * @author nihki <nihki@madaniyah.com>
 */
class App_Model_Show_User extends App_Model_Db_DefaultAdapter
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
    	self::$_db = Zend_Registry::get('db2');
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

    public function getUserQueue()
    {
        $db = parent::_dbSelect();
        $select = $db->from('KutuUser')
                    ->where("isActive = 0")
                    ->where("periodeId IN (1,2)")
                    ->order("createdDate DESC");

		$conn = self::$_db;
		                    
        $result = $conn->fetchAll($select);

        return $result;
    }
    public function getUserList()
    {
        $db = parent::_dbSelect();
        $select = $db->from('KutuUser');
        
        $conn = self::$_db;

        $result = $conn->fetchAll($select);

        return $result;
    }
    public function getUserByName($username)
    {
        $db = parent::_dbSelect();
        $select = $db->from('KutuUser')
                ->where("username='".$username."'");
                
                
		$conn = self::$_db;                

        $result = $conn->fetchRow($select);

        return $result;
    }
    public function getUserById($id)
    {
        $db = parent::_dbSelect();
        $select = $db->from('KutuUser')
                ->where("kopel='".$id."'");

		$conn = self::$_db;
		                
        $result = $conn->fetchRow($select);

        return $result;
    }
    public function fetchUser($where,$start,$end)
    {
    	$db = parent::_dbSelect();
    	$select = $db->from(array('ku' => 'KutuUser'))
			->joinLeft(array('gag' => 'gacl_aro_groups'),
			'ku.packageId = gag.id')
			->joinLeft(array('kus' => 'KutuUserStatus'),
			'ku.periodeId = kus.accountStatusId','kus.status')
			->where("$where")
			->order('kopel ASC')->limit($end, $start);
    	
		//$sql = $select->__toString();
    	//print_r($sql);exit();
    	
        $conn = self::$_db;

        $db = $conn->query($select);
        $dataFetch = $db->fetchAll(Zend_Db::FETCH_OBJ);
        return $dataFetch;
    }
    public function countUser($where)
    {
    	$db = parent::_dbSelect();
    	$sql = $db->from(array('ku' => 'KutuUser'),array('count'=>'COUNT(*)'))
			->joinLeft(array('gag' => 'gacl_aro_groups'),
			'ku.packageId = gag.id')
			->joinLeft(array('kus' => 'KutuUserStatus'),
			'ku.periodeId = kus.accountStatusId','kus.status')
			->where("$where");
		
        $conn = self::$_db;
        
		$db = $conn->query($sql);
    	$dataFetch = $db->fetchAll(Zend_Db::FETCH_ASSOC);
    	
    	return ($dataFetch[0]['count']);
    }
}
