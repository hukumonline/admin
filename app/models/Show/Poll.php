<?php

/**
 * Description of PaymentConfirmation
 *
 * @author nihki <nihki@madaniyah.com>
 */
class App_Model_Show_Poll extends App_Model_Db_DefaultAdapter
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
    function getPollByDate($date)
    {
        $db = parent::_dbSelect();
        $select = $db->from('polls')
                    ->where("checkedTime < '$date'","checkedTime DESC");

        $result = parent::_getDefaultAdapter()->fetchRow($select);

        return $result;
    }
}