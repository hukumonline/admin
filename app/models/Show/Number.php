<?php

class App_Model_Show_Number extends App_Model_Db_DefaultAdapter 
{
	/**
	 * class instance object
	 */
	private static $_instance;
	 
	/**
	 * non-aktif-kan constructor
	 */
	final private function  __construct() {}
	 
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
		$statement = $db->from('KutuNumber','*','hid');
		
		$result = parent::_getDefaultAdapter()->fetchRow($statement);
		
		return $result;
	}
}

?>