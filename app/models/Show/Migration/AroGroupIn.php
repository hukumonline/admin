<?php

/**
 * Description of Comment
 *
 * @author nihki <nihki@madaniyah.com>
 */
class App_Model_Show_Migration_AroGroupIn extends App_Model_Db_DefaultAdapter
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
    public function get_group_data($group_id)
    {
        $db = parent::_dbSelect();
    	$select = $db->from("gacl_aro_groups",
                    array('id', 'parent_id', 'value', 'name', 'lft', 'rgt'))
                    ->where('id='.$group_id);
        
        $configDb = Zend_Registry::get('db2');
    	$row = $configDb->fetchAll($select);

    	return $row;
    }
    public function get_group_id($value)
    {
        $db = parent::_dbSelect();
    	$select = $db->from("gacl_aro_groups",array("id"))
                    ->where("value='".$value."'");

        /*
        $select = $select->__toString();
    	print_r($select);exit();
         * 
         */

        $configDb = Zend_Registry::get('db2');
    	$row = $configDb->fetchRow($select);

    	return $row;
    }
}
