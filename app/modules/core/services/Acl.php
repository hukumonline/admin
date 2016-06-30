<?php
/**
 * @author	2013-2018 Nihki Prihadi <nihki@madaniyah.com>
 * @version $Id: Acl.php 1 2013-03-21 17:35
 */

class Core_Services_Acl
{
	/**
	 * @var Core_Services_Acl
	 */
	private static $_instance = null;
	
	/**
	 * @return Core_Services_Acl
	 */
	public static function getInstance()
	{
		if (null == self::$_instance) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	public function isUserOrRoleAllowed($user, $module, $controller, $action = null)
	{
		if ($action != null) {
			$action = strtolower($action);
		}
		$resource = strtolower($module . ':' . $controller);
		
		$acl = Pandamp_Acl::manager();
		if ($acl->checkAcl('action','all','user',$user->username,'content','all-access')
				|| $acl->checkAcl($resource,$action,'user',$user->username,false,false)) {
			return true;
		}
		return false;
	}
}