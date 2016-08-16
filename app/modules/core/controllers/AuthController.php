<?php
class Core_AuthController extends Zend_Controller_Action
{
	public function denyAction()
	{
		Zend_Layout::getMvcInstance()->setLayout('layoutdeny');
	}
	
	public function loginAction()
	{
		Zend_Layout::getMvcInstance()->setLayout('layout-login2');
		
		$request 	= $this->getRequest();
		$returnUrl 	= $request->getParam('returnUrl', null);
		
		$this->view->assign('returnUrl', $returnUrl);
	}
}