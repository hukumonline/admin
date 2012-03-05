<?php
/**
 * @author	2011-2012 Nihki Prihadi <nihki@madaniyah.com>
 * @version $Id: Config.php 2 2011-12-24 12:46Z $
 */

class Pandamp_Config 
{
	const KEY = 'Pandamp_Config_';
	
	/**
	 * Get application config object
	 * 
	 * @return Zend_Config
	 */
	public static function getConfig() 
	{
		$host = $_SERVER['SERVER_NAME'];
		$host = (substr($host, 0, 3) == 'www') ? substr($host, 4) : $host;

		$key = self::KEY.$host;
		if (!Zend_Registry::isRegistered($key)) {
			$defaultConfig = APPLICATION_PATH . DS . 'configs' . DS . 'zhol.ini';
			$hostConfig    = APPLICATION_PATH . DS . 'configs' . DS . $host . '.ini';
			
			$file 	= file_exists($hostConfig) ? $hostConfig : $defaultConfig;
			$config = new Zend_Config_Ini($file);
			Zend_Registry::set($key, $config);
		}
		
		return Zend_Registry::get($key);
	}
}
