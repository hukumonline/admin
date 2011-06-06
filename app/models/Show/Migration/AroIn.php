<?php

/**
 * Description of Comment
 *
 * @author nihki <nihki@madaniyah.com>
 */
class App_Model_Show_Migration_AroIn extends App_Model_Db_DefaultAdapter
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
    public function getUserGroupId($username)
    {
        $db = parent::_dbSelect();
    	$select = $db->from('gacl_aro')
                    ->where('section_value=?','user')
                    ->where('value=?',$username);

        $config = Zend_Registry::get('db2');
    	$row = $config->fetchRow($select);

    	return $row;
    }
}
