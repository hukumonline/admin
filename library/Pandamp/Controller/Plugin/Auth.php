<?php
/**
 * @author	2011-2018 Nihki Prihadi <nihki@madaniyah.com>
 * @version $Id: Auth.php 1 2013-03-22 10:46
 */

class Pandamp_Controller_Plugin_Auth extends Zend_Controller_Plugin_Abstract
{
	public function preDispatch(Zend_Controller_Request_Abstract $request)
	{
		if ($request->isXmlHttpRequest()) {
			return;
		}
		
		$module 	= $request->getModuleName();
		$controller = $request->getControllerName();
		$action 	= $request->getActionName();
		
		$isAllowed = false;
		if (Zend_Auth::getInstance()->hasIdentity()) {
			$user = Zend_Auth::getInstance()->getIdentity();
			require_once(APPLICATION_PATH.'/modules/core/services/Acl.php');
			$acl = Core_Services_Acl::getInstance();
			if (in_array(strtolower($module . '_' . $controller . '_' . $action),array('default_index_index'))) {
				$isAllowed = true;
			}
			else
			{
				$isAllowed = $acl->isUserOrRoleAllowed($user, $module, $controller, $action);
			}
		}
		if (!$isAllowed) {
			if (Zend_Auth::getInstance()->hasIdentity()) {
				$forwardAction = 'deny';
			}
			else
			{
				$forwardAction = 'login';
			}
			
			$sReturn = "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
			$sReturn = base64_encode($sReturn);
			
			$request->setModuleName('core')
					->setControllerName('Auth')
					->setActionName($forwardAction)
					->setParam('returnUrl', $sReturn)
					->setDispatched(true);
		}
	}
}