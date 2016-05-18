<?php
/**
 * @author	2011-2018 Nihki Prihadi <nihki@madaniyah.com>
 * @version $Id: Abstract.php 1 2016-05-18 13:30
 */

abstract class Pandamp_Search_Index_Abstract
{
	const KEY = 'Pandamp_Search_Index_Abstract_Key';
	
	protected $_adapter;
	
	public function __construct($adapter)
	{
		$this->_adapter = $adapter;
	}
	
	/**
	 * @return string
	 */
	public function getAdapter()
	{
		return $this->_adapter;
	}
	
	/**
	 * Support master connection type
	 *
	 * @return mixed
	 */
	public function getMasterConnection()
	{
		return $this->_getConnection('master');
	}
	
	/**
	 * Support slave connection type
	 *
	 * @return mixed
	 */
	public function getSlaveConnection()
	{
		return $this->_getConnection('slave');
	}
	
	public function getShorturlConnection()
	{
		return $this->_getConnection('shorturl');
	}
	
	/**
	 * @param string $type Type of connection. Must be slave or master
	 * @return mixed
	 */
	protected function _getConnection($type)
	{
		$key = self::KEY.'_'.$type;
		if (!Zend_Registry::isRegistered($key)) {
			$config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/indexing.ini','indexing');
			$servers = $config->$type;
			
			/**
			 * Connect to random server
			 */
			$servers = $servers->toArray();
			$randomServer = array_rand($servers);
			
			$db = $this->_connect($servers[$randomServer]);
			
			Zend_Registry::set($key, $db);
		}
		return Zend_Registry::get($key);
	}
	
	/**
	 * Abstract connection
	 * @param array $config solr connection settings, includes parameters:
	 * - host
	 * - port
	 * @return mixed Solr connection
	 */
	protected abstract function _connect($config);
	
	/**
	 * Execute SOLR query
	 *
	 * @param string $query
	 */
	public abstract function search($querySolr, $start = 0 , $end = 2000, $aParams, $method="GET");
}