<?php
/**
 * @author	2011-2018 Nihki Prihadi
 * @version $Id: UserAvatar.php 1 2013-09-23 16:46Z $
 */

class Pandamp_Controller_Action_Helper_UserAvatar 
{
	public function userAvatar()
	{
		$auth = Zend_Auth::getInstance();
		if (!$auth->hasIdentity()) {
			return;
		}
		
		$viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
		if (null === $viewRenderer->view) {
			$viewRenderer->initView();
		}
		$view = $viewRenderer->view;
		
		$arraypictureformat = array("jpg", "jpeg", "gif");
		$txt_allowedformat = implode('; ', $arraypictureformat);
		
		$registry = Zend_Registry::getInstance();
		$config = $registry->get(Pandamp_Keys::REGISTRY_APP_OBJECT);
		$cdn = $config->getOption('cdn');
		
		$sDir = $cdn['static']['dir']['photo'];
		$sDir2 = $cdn['static']['url']['photo'].'/';
		
		$x = 0;
		foreach ($arraypictureformat as $key => $val) {
			if (is_file($sDir."/".$auth->getIdentity()->kopel.".".$val)) {
				$myphoto = $sDir."/".$auth->getIdentity()->kopel.".".$val;
				$myext = $val;
				$x = 1;
				break;
			}
		}
		if ($x == 1) {
			$myphotosize = getimagesize($myphoto);
			$dis = "";
			if (isset($myext) && is_file($sDir."/".$auth->getIdentity()->kopel.".".$myext))
				$txt_existence = $sDir2.$auth->getIdentity()->kopel.".".$myext;
		
		}
		else
		{
			$txt_existence = $view->cdn('images')."/user_32.png";
		}
		
		return $txt_existence;
	}
}
