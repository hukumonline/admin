<?php
/**
 * @author	2011-2012 Nihki Prihadi <nihki@madaniyah.com>
 * @version $Id: Cache.php 2 2011-12-24 01:59Z $
 */

class Pandamp_Cache 
{
	/**
	 * Get global cache instance
	 * 
	 * @return Zend_Cache_Core
	 */
	public static function getInstance() 
	{
		$config = Pandamp_Config::getConfig();
		if (!isset($config->cache->frontend) || !isset($config->cache->backend)) {
			return null;
		}
		$frontendOptions = $config->cache->frontend->options->toArray();
		$backendOptions  = $config->cache->backend->options->toArray();
		$frontendOptions = self::_replaceConst($frontendOptions);
		$backendOptions  = self::_replaceConst($backendOptions);
		
		return Zend_Cache::factory($config->cache->frontend->name, $config->cache->backend->name,
			$frontendOptions, $backendOptions);
	}
	
	private static function _replaceConst($options) 
	{
		$search 	= array('{DS}', '{TEMP_DIR}');
		$replace 	= array(DS, TEMP_DIR);
		$newOptions = array();
		foreach ($options as $key => $value) {
			$newOptions[$key] = str_replace($search, $replace, $value);
		}
		return $newOptions;
	}
}
