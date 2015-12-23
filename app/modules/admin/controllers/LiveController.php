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
		$query = array();
		$requestLog = App_Model_Mongodb_RequestLog::fetchOne(
				['access_time' => ['$lte' => new \MongoDate(strtotime('+1 minute'))]]
				,['_id' => -1]);
		
		/*$content = 0;
		$data = array();
		foreach ($requestLog as $reqlog) {
			$data[$content]['ip'] = $reqlog->ip;
			$data[$content]['access_time'] = $reqlog->access_time;
			
			$content++;
		}*/
		
		
		$this->view->assign('reqlog',$requestLog);
		$this->view->assign('dateFormat', array(
				'DAY' 			=> '%s days ago',
				'DAY_HOUR'		=> '%s days %s hours ago',
				'HOUR' 			=> '%s hours ago',
				'HOUR_MINUTE' 	=> '%s hours %s minutes ago',
				'MINUTE' 		=> '%s minutes ago',
				'MINUTE_SECOND'	=> '%s minutes %s seconds ago',
				'SECOND'		=> '%s seconds ago',
		));
	}
}