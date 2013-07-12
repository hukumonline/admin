<?php

/**
 * Description of AroGroup
 *
 * @author nihki <nihki@madaniyah.com>
 */
class App_Model_Show_AroGroup extends App_Model_Db_DefaultAdapter
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
    public static function show()
    {
        if (!isset(self::$_instance)) {
                $show = __CLASS__;
                self::$_instance = new $show;
        }
        return self::$_instance;
    }
    public function getUserGroup($packageId)
    {
        $db = parent::_dbSelect();
        $select = $db->from('gacl_aro_groups')
                    ->where("id = $packageId");
                    
		$conn = self::$_db;                    

        $result = $conn->fetchRow($select);

        return $result;
    }
}
