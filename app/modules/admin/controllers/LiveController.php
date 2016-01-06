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
		
		$request = $this->getRequest();
		
		$query = array();
		if ($request->getParam('log') == 'today')
			$query = [
				'access_time' => [
					'$gte' => new \MongoDate( strtotime(date('Y-m-d 23:59:59', strtotime('-2 days'))) ),
					'$lte' => new \MongoDate(),
				]
			];
		
		elseif ($request->getParam('log') == 'minute')
			$query = [
				'access_time' => [
					'$gte' => new \MongoDate( strtotime('-1 minute') ),
					'$lte' => new \MongoDate(),
				]
			];
		
		elseif ($request->getParam('log') == 'customer')
			$query = [
				'access_time' => [
					'$gte' => new \MongoDate( strtotime(date('Y-m-d 23:59:59', strtotime('-2 days'))) ),
					'$lte' => new \MongoDate(),
				],
				'kopel' => [
					'$exists' => true
				]
			];
		
			
		
		
		echo number_format(App_Model_Mongodb_RequestLog::all($query)->count());
	}
	
	public function timelineAction()
	{
		$this->_helper->layout()->disableLayout();
		
		//$requestLog = App_Model_Mongodb_RequestLog::all()->skip(0)->limit(1)->sort(['access_time'=>-1]);
		$requestLog = App_Model_Mongodb_RequestLog::getMongoCollection()->find()->limit(1)->sort(['access_time'=>-1]);
		$requestLog = $requestLog->getNext();
		$this->view->assign('reqlog',$requestLog);
	}
	
	public function deviceAction()
	{
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		
		$request = $this->getRequest();
		
		$device = App_Model_Mongodb_RequestLog::device();
		
		$d = $m = 0;
		foreach ($device['result'] as $key => $value) {
			if (parse_url($value['_id'])['host'] == 'www.hukumonline.com') {
				$d += $value['percentage'];
			}
			else
			{
				$m += $value['percentage'];
			}
		}
		
		
		if ($request->getParam('platform') == 'desktop')
			echo round($d).'%';
		elseif ($request->getParam('platform') == 'mobile')
			echo round($m).'%';
		
	}
	
	public function referralAction()
	{
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		
		$request = $this->getRequest();
		
		$referral = App_Model_Mongodb_RequestLog::referral($request->getParam('periode'));
		
		$this->getResponse()->setBody(Zend_Json::encode($referral));
	}
	
	public function clickAction()
	{
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
	
		$request = $this->getRequest();
	
		$click = App_Model_Mongodb_RequestLog::click($request->getParam('periode'));
	
		foreach ($click['result'] as $key => $val) {
			$furl = $val['_id'];
			$url = pathinfo($furl);
			$guid = basename($url['dirname']);
			$title = basename($val['_id']);
			$catalogDb = App_Model_Show_Catalog::show()->getCatalogByGuid($guid);
			if ($catalogDb) {
				if ($catalogDb['profileGuid'] == 'klinik')
					$title = App_Model_Show_CatalogAttribute::show()->getCatalogAttributeValue($guid,'fixedCommentTitle');
				else
					$title = App_Model_Show_CatalogAttribute::show()->getCatalogAttributeValue($guid,'fixedTitle');
			}
			
			$click['result'][$key]['title'] = $title;
		}
		
		$this->getResponse()->setBody(Zend_Json::encode($click));
	}
	
	
}