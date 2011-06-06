<?php

/**
 * Description of OrderStatus
 *
 * @author nihki <nihki@madaniyah.com>
 */
class App_Model_Show_OrderStatus extends App_Model_Db_DefaultAdapter
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
    public function getStatus()
    {
        $db = parent::_dbSelect();
    	$select = $db->from('KutuOrderStatus');

    	$result = parent::_getDefaultAdapter()->fetchAll($select);

    	return $result;
    }
    public function getSpecifiedStatus($where)
    {
        $db = parent::_dbSelect();
        $select = $db->from('KutuOrderStatus')
                    ->where('orderStatusId = '.$where);

        $result = parent::_getDefaultAdapter()->fetchAll($select);

        return $result;
    }
}
