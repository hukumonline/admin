<?php

/**
 * Description of UserIn
 *
 * @author nihki <nihki@madaniyah.com>
 */
class App_Model_Show_Migration_UserIn extends App_Model_Db_DefaultAdapter
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
    public function getUser($username)
    {
        $db = parent::_dbSelect();
    	$select = $db->from("KutuUser")
                    ->where("username='".$username."'")
                    ->where("isActive=1")
                    ->where("email <> ''");

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
