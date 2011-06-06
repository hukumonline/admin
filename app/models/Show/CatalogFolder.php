<?php

/**
 * Description of CatalogFolder
 *
 * @author nihki <nihki@madaniyah.com>
 */
class App_Model_Show_CatalogFolder extends App_Model_Db_DefaultAdapter
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
    public function getCatalogGuidByFolderGuid($node)
    {
        $db = parent::_dbSelect();
        $select = $db->from('KutuCatalogFolder',array('guid'=>'catalogGuid'))
                    ->where("folderGuid=?","$node")
                    ->order("catalogGuid desc");

    	//$sql = $select->__toString();
    	//print_r($sql);exit();

        $conn = self::$_db;
        //$rows = $conn->query("SELECT catalogGuid as guid from KutuCatalogFolder where folderGuid='$node'");

        //$result = $rows->fetchAll(Zend_Db::FETCH_OBJ);
        $result = $conn->fetchAll($select);

        return $result;
    }
    
    /*
    public function getCountCatalogGuidByFolderGuid($node)
    {
        $db = parent::_dbSelect();
        $select = $db->from('KutuCatalogFolder',array('COUNT(catalogGuid) as guid'))
                    ->where("folderGuid=?","$node");

        $conn = self::$_db;

        $result = $conn->fetchAll($select);

        return $result[0]['guid'];
    }
    */
}
