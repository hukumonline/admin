<?php
class Msite_IndexController extends Zend_Controller_Action
{
	public function init()
	{
		$this->_helper->cache(array('index'), array('entries'));
	}	
	function indexAction()
	{
	}
}
?>