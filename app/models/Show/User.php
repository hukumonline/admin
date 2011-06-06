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

    public function getUserQueue()
    {
        $db = parent::_dbSelect();
        $select = $db->from('KutuUser','*','hid')
                    ->where("isActive = 0");

        $result = parent::_getDefaultAdapter()->fetchAll($select);

        return $result;
    }
    public function getUserList()
    {
        $db = parent::_dbSelect();
        $select = $db->from('KutuUser','*','hid');

        $result = parent::_getDefaultAdapter()->fetchAll($select);

        return $result;
    }
    public function getUserByName($username)
    {
        $db = parent::_dbSelect();
        $select = $db->from('KutuUser','*','hid')
                ->where("username='".$username."'");

        $result = parent::_getDefaultAdapter()->fetchRow($select);

        return $result;
    }
    public function getUserById($id)
    {
        $db = parent::_dbSelect();
        $select = $db->from('KutuUser','*','hid')
                ->where("kopel='".$id."'");

        $result = parent::_getDefaultAdapter()->fetchRow($select);

        return $result;
    }
}
