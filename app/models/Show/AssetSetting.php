<?php 
/**
 * Pandamp
 * 
 * @author		2012-2013 Nihki Prihadi
 * @version		$Id: AssetSetting.phtml 1 2012-10-04 18:50Z $
 */

class App_Model_Show_AssetSetting extends App_Model_Db_DefaultAdapter
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

	public function getHits($catalogGuid, $text)
	{
		$db = parent::_dbSelect();
		
		$conn = self::$_db;
		
        $row = $conn->fetchRow($db->from('KutuAssetSetting')->where('guid=?',$catalogGuid)->where('valueText=?',$text));

        return $row;
	}
}