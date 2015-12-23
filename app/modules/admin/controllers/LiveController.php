<?php
class Admin_LiveController extends Zend_Controller_Action
{
	public function preDispatch()
	{
		$this->_helper->layout->setLayout('live');
		
		// Initialize mongodb
		$db = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application-cli.ini','mongodb');
		Shanty_Mongo::addConnections($db);
	}
	
	public function indexAction()
	{
		
	}
	
	public function logrequestAction()
	{
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		
		echo number_format(App_Model_Mongodb_RequestLog::all()->count());
	}
}