<?php

class Dev_FacebookController extends Zend_Controller_Action 
{
	function metatagAction()
	{
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		echo 'test';
	}
}