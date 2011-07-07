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
                    ->where("periodeId IN (1,2,5)")
                    ->order("kopel DESC");

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
}
