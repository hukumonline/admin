<?php

/**
 * Description of Invoice
 *
 * @author nihki <nihki@madaniyah.com>
 */
class App_Model_Show_Invoice extends App_Model_Db_DefaultAdapter
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
    	self::$_db = Zend_Registry::get('db2');
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

    public function getInvoiceById($id)
    {
        $db = parent::_dbSelect();
        $select = $db->from('KutuUserInvoice')
                ->where("uid='".$id."'");
                
		$conn = self::$_db;                

        $result = $conn->fetchAll($select);

        return $result;
    }
}
