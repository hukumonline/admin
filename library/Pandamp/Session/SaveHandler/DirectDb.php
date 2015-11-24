<?php

class Pandamp_Session_SaveHandler_DirectDb implements Zend_Session_SaveHandler_Interface
{
	protected $_db;
	
	function __construct()
	{
		$multidb = Pandamp_Application::getResource('multidb');
		$multidb->init();
		
		$config = $multidb->getDb('db2');
		
		$this->_db = $config;
	}
	
	public function open($save_path, $name) 
	{
		global $sess_save_path;
		$sess_save_path = $save_path;
		return true;
	}
	function close() 
	{
		return true;
	}
	
	public function read($id) 
	{ 
		$return  = '';
		$time = time();
		$db = $this->_db;
		$aRows = $db->fetchAll("SELECT sessionData FROM `session` WHERE sessionId='$id' AND sessionExpiration > ".new Zend_Db_Expr("FROM_UNIXTIME($time)"));
	
		if(count($aRows) > 0)
		{
			$sSessionData = $aRows[0]['sessionData'];
			$return = $sSessionData;
		}
		else
		{
			$this->destroy($id);
		}
		
		return $return;
	}
	public function write($id, $data) 
	{
		$obj = new stdClass();
		$obj->session_id = $id;
		$obj->data = $data;
		$obj->modified = time();
		
		$db = $this->_db;
		
		$lifeTime = ini_get('session.gc_maxlifetime'); //get_cfg_var("session.gc_maxlifetime");
		$time = $obj->modified + $lifeTime - 600;
		
		//error_log("$obj->session_id = $obj->data");
		//$val = addslashes($obj->data);
		
		$aRows = $db->fetchAll("SELECT * FROM `session` WHERE sessionId='$obj->session_id'");
		if(count($aRows) > 0)
		{
			return $db->update('session',
					array(
							'sessionData'  => $obj->data,
							'sessionExpiration' => new Zend_Db_Expr("FROM_UNIXTIME($time)"),
					),
					array(
							'sessionId = ?' => $obj->session_id,
					));
		}
		else
		{
			return $db->insert('session',
					array(
							'sessionId' => $obj->session_id,
							'sessionData' => $obj->data,
							'sessionExpiration' => new Zend_Db_Expr("FROM_UNIXTIME($time)"),
					));
		}
		
		
	}
	public function destroy($id) 
	{
	 	$db = $this->_db;
		
		return $db->delete('session',
				array(
						'sessionId = ?' => $id,
				));
	}
		
	public function gc($maxlifetime) 
	{
		$db = $this->_db;
		
		// Garbage Collection
		$time = time();
		$date = date("Y-m-d H:i:s", $time);
		
		$db->delete('session',
				array(
						'sessionExpiration < ?' => new Zend_Db_Expr("FROM_UNIXTIME($time)"),
				));

		return true;
	}
}