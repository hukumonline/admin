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
		
		echo number_format(App_Model_Mongodb_RequestLog::all()->count(true));
	}
	
	public function timelineAction()
	{
		$this->_helper->layout()->disableLayout();
		
		$requestLog = App_Model_Mongodb_RequestLog::all()->skip(0)->limit(1)->sort(['access_time'=>-1]);
		$this->view->assign('reqlog',$requestLog->getNext());
	}
	
	public function referralAction()
	{
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		
		$request = $this->getRequest();
		
		$referral = App_Model_Mongodb_RequestLog::referral($request->getParam('periode'));
		
		$this->getResponse()->setBody(Zend_Json::encode($referral));
	}
}