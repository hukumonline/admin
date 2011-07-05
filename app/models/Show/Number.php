<?php

class App_Model_Show_Number extends App_Model_Db_DefaultAdapter 
{
	/**
	 * class instance object
	 */
	private static $_instance;
	
	private static $_db;
	 
	/**
	 * non-aktif-kan constructor
	 */
	final private function  __construct() 
	{
		self::$_db = Zend_Registry::get('db2');
	}
	 
	 /**
	  * non-aktif-kan object cloning
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
	public function getNumber()
	{
		$db = parent::_dbSelect();
		$statement = $db->from('KutuNumber');
		
		$conn = self::$_db;
		
		$result = $conn->fetchRow($statement);
		
		return $result;
	}
}

?>