<?php
class Core_AuthController extends Zend_Controller_Action
{
	public function denyAction()
	{
		Zend_Layout::getMvcInstance()->setLayout('layoutdeny');
	}
}