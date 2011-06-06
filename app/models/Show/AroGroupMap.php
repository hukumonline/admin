<?php

/**
 * Description of AroGroupMap
 *
 * @author nihki <nihki@madaniyah.com>
 */
class App_Model_Show_AroGroupMap extends App_Model_Db_DefaultAdapter
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

    public function getGroupException($where)
    {
        $db = Zend_Registry::get('db2');
        $query = $db->query("SELECT * FROM gacl_aro o, gacl_groups_aro_map gm  WHERE gm.group_id=".$where." AND gm.aro_id=o.id");

        $result = $query->fetchAll(Zend_Db::FETCH_ASSOC);

        return $result;
    }
}
