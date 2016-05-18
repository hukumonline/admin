<?php
class Pandamp_Search_Engine
{
	public static function factory()
	{
		$config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/indexing.ini','indexing');
		$adapter = $config->adapter;
		$adapter = str_replace(' ', '_', ucwords(str_replace('_', ' ', strtolower($adapter))));
		$class 	 = 'Pandamp_Search_Index_' . $adapter . '_Connection';
		if (!class_exists($class)) {
			throw new Exception('Does not support ' . $adapter . ' connection');
		}
		$instance = new $class($adapter);
		return $instance;
	}
	
	public static function _factory($config = array())
	{
		$solrHost = $config['host'];
		$solrPort = $config['port'];
		$solrHomeDir = $config['homedir'];
		$newAdapter = new Pandamp_Search_Adapter_Solr($solrHost, $solrPort, $solrHomeDir);
		
		return $newAdapter;
	}
}