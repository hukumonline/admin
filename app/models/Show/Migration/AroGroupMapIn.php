<?php

/**
 * Description of AroGroupMapIn
 *
 * @author nihki <nihki@madaniyah.com>
 */
class App_Model_Show_Migration_AroGroupMapIn extends App_Model_Db_DefaultAdapter
{
    /**
     * class instance object
     */
    private static $_instance;

    /**
     * de-activate constructor
     */
    final private function  __construct() {}

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
    public function get_object_groups($object_id)
    {
        $db = parent::_dbSelect();
    	$select = $db->from("gacl_groups_aro_map","group_id")
                    ->where('aro_id='.$object_id);

        /*
        $select = $select->__toString();
    	print_r($select);exit();
         *
         */

        $configDb = Zend_Registry::get('db2');
    	$row = $configDb->fetchAll($select);

    	return $row;
    }
    public function getObjectsByGroup($groupId)
    {
        $configDb = Zend_Registry::get('db2');
    	$row = $configDb->fetchAll("SELECT * FROM gacl_aro o, gacl_groups_aro_map gm  WHERE gm.group_id=".$groupId." AND gm.aro_id=o.id");

    	return $row;
    }
}
