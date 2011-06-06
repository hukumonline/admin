<?php

/**
 * Description of Folder
 *
 * @author nihki <nihki@madaniyah.com>
 */

class App_Model_Show_Folder extends App_Model_Db_DefaultAdapter
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
        $zl = Zend_Registry::get("Zend_Locale");
        if ($zl->getLanguage() == 'id')
            self::$_db = Zend_Registry::get('db1');
        else
            self::$_db = Zend_Registry::get('db3');
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

    /**
     * @return obj
     */
    public function fetchChildren($parentGuid)
    {
        $db = parent::_dbSelect();
        if($parentGuid == 'root')
        {
            $select = $db->from('KutuFolder')
                        ->where('parentGuid=guid')
                        ->order('title ASC');
        }
        else
        {
            $select = $db->from('KutuFolder')
                        ->where("parentGuid = '$parentGuid' AND NOT parentGuid=guid")
                        ->order('title ASC');
        }

        $conn = self::$_db;
        $rows = $conn->fetchAll($select);

        return $rows;
    }
}
