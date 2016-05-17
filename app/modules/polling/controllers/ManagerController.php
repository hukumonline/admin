<?php

/**
 * Description of ManagerController
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Polling_ManagerController extends Zend_Controller_Action
{
	protected $_user;
	
	public function preProcessSession()
	{
		$identity = Pandamp_Application::getResource('identity');
		$loginUrl = $identity->loginUrl;
		
		$sReturn = "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
		$sReturn = base64_encode($sReturn);
		
		$auth = Zend_Auth::getInstance();
		if (!$auth->hasIdentity()) {
			$this->_redirect($loginUrl.'?returnUrl='.$sReturn);
		}
		else
		{
			$this->_user = $auth->getIdentity();
				
			$zl = Zend_Registry::get("Zend_Locale");
				
			$acl = Pandamp_Acl::manager();
				
			if (!$acl->checkAcl("site",'all','user', $this->_user->username, false,false))
			{
				$this->_forward('restricted','error','admin',array('lang'=>$zl->getLanguage()));
			}
				
			$tblSetting = new App_Model_Db_Table_Setting();
			$rowset = $tblSetting->find(1)->current();
				
			if ($rowset)
			{
				if (($rowset->status == 1 && $zl->getLanguage() == 'id') || ($rowset->status == 2 && $zl->getLanguage() == 'en') || ($rowset->status == 3))
				{
					if (($this->_user->name !== "Master") && ($this->_user->name !== "Super Admin"))
					{
						$this->_forward('temporary','error','admin');
					}
						
				}
			}
				
		}
	}
	
    public function viewAction()
    {
		$tblPolling = new App_Model_Db_Table_Poll();
		$tblOption = new App_Model_Db_Table_PollOption();
		
		$time = time();
		$date = date("Y-m-d H:i:s", $time);
		
		$rowPoll = $tblPolling->fetchRow("checkedTime < '$date'","checkedTime DESC");
		$this->view->rowPoll = $rowPoll;
		
		$rowOpt = $tblOption->fetchAll("pollGuid='$rowPoll->guid'","text ASC");
		$this->view->rowOpt = $rowOpt;
    }
    
    public function listAction()
    {
    	$this->_helper->layout->setLayout('layout-newpolling');
    	
    	$this->preProcessSession();
    	
    	$request = $this->getRequest();
    	
    	$pageIndex = $request->getParam('page',1);
    	$perpage = $request->getParam('showperpage',25);
    	
    	$sWhere = null;
    	$params = null;
    	$exp = [
    		'showperpage' => $perpage,
    	];
    	
    	$time = time();
    	$date = date("Y-m-d H:i:s", $time);
    	
    	if ($polling = $request->getPost('searchpolling'))
    	{
    		$pColumns = ['guid','title'];
    		for ($i=0;$i<count($pColumns);$i++)
    		{
    			$sWhere .= $pColumns[$i]." LIKE '%".mysql_real_escape_string($polling)."%' OR ";
    		}
    			
    		$sWhere = substr_replace($sWhere,"",-3);
    			
    		$exp['polling'] = $polling;
    			
    		$params = rawurlencode(base64_encode(Zend_Json::encode($exp)));
    	}
    	else
    	{
    		$sWhere = "checkedTime < '$date'";
    			
    		$params = $request->getParam('q');
    		if ($params != null) {
	    		$exp = rawurldecode(base64_decode($params));
	    		$exp = Zend_Json::decode($exp);
    			
    		}
    		else
    		{
    			$params = rawurlencode(base64_encode(Zend_Json::encode($exp)));
    		}
    	}
    	
   		$offset = ($pageIndex > 0) ? ($pageIndex - 1) * $exp['showperpage'] : 0;
    	
   		$pollingDb = new App_Model_Db_Table_Poll();
   		$rowset = $pollingDb->fetchAll($sWhere,"checkedTime DESC",$exp['showperpage'],$offset);
   		$numOfRows = $pollingDb->fetchAll($pollingDb->select()->where($sWhere))->count();
    	
   		$paginator = Zend_Paginator::factory($numOfRows);
   		$paginator->setCurrentPageNumber($pageIndex);
   		$paginator->setItemCountPerPage($exp['showperpage']);
    		
   		$this->view->assign('params',$params);
   		$this->view->assign('exp',$exp);
   		$this->view->assign('rowset',$rowset);
		$this->view->assign('perpage',$exp['showperpage']);
   		$this->view->assign('paginator',$paginator);
   		$this->view->assign('currentPageNumber',$paginator->getCurrentPageNumber());
    }
    
    public function addAction()
    {
    	$this->_helper->layout->setLayout('layout-newpolling');
    	
    	$this->preProcessSession();
    	
    	$request = $this->getRequest();
    	
    	if ($request->isPost()) {
    		$title = $request->getPost('title');
    		$answers = $request->getPost('answers');
    		
    		$pollingDb = new App_Model_Db_Table_Poll();
    			
    		$pollingOptionDb = new App_Model_Db_Table_PollOption();
    			
    		$newRow = $pollingDb->fetchNew();
    		$newRow->title = $title;
    		$newRow->checkedTime = date("Y-m-d H:i:s");
    			
    		$guid = $newRow->save();
    			
    		for ($i=0; $i < count($answers); $i++)
    		{
	    		$dataNewRow = $pollingOptionDb->fetchNew();
	    		$dataNewRow->pollGuid = $guid;
	    		$dataNewRow->text = $answers[$i];
	    		$dataNewRow->hits = 0;
	    		$dataNewRow->save();
    		}
    		
    		$this->_helper->getHelper('FlashMessenger')
    		->addMessage('The poll has been added successfully.');
		
			$this->_redirect($this->view->serverUrl() . '/' . $this->view->getLanguage() . '/polling/manager/list');
    	}
    }
    
    public function editAction()
    {
    	$this->_helper->layout->setLayout('layout-newpolling');
    	
    	$this->preProcessSession();
    	
    	$request = $this->getRequest();
    	
    	$questionId = $request->getParam('pollid');

    	$pollingDb = new App_Model_Db_Table_Poll();
    	
    	$pollingOptionDb = new App_Model_Db_Table_PollOption();
    	
    	$question = $pollingDb->find($questionId)->current();
    	$answers = $pollingOptionDb->fetchAll("pollGuid='$questionId'");
    	
    	$this->view->assign('question', $question);
    	$this->view->assign('answers', $answers);
    	
    	if ($request->isPost()) {
    		$title = $request->getPost('title');
    		$answers = $request->getPost('answers');
    			
    		$purifier = new HTMLPurifier();
    			
    		$question->title = $purifier->purify($title);
    			
    		$question->save();
    			
    		if ($answers != null && $questionId != null) {
    			$pollingOptionDb->delete(['pollGuid=?' => $questionId]);
    	
    			for ($i=0; $i < count($answers); $i++)
    			{
    				if (empty($answers[$i])) continue;
	    			$dataNewRow = $pollingOptionDb->fetchNew();
	    			$dataNewRow->pollGuid = $questionId;
	    			$dataNewRow->text = $purifier->purify($answers[$i]);
	    			$dataNewRow->hits = 0;
	    			$dataNewRow->save();
    			}
    		}
    				
   			$this->_helper->getHelper('FlashMessenger')
    			->addMessage('The poll has been updated successfully.');
   			
			$this->_redirect($this->view->serverUrl() . '/' . $this->view->getLanguage() . '/polling/manager/list');
		}
    }
    
    public function deleteAction()
    {
    	$this->_helper->getHelper('layout')->disableLayout();
    	$this->_helper->getHelper('viewRenderer')->setNoRender();
    	
    	$this->preProcessSession();
    
    	$request = $this->getRequest();
    	$result  = 'RESULT_ERROR';
    
    	if ($request->isPost()) {
    		$guid = $request->getPost('guid');
    		$ids = array();
    			
    		$ids = Zend_Json::decode($guid);
    			
    		$pollingDb = new App_Model_Db_Table_Poll();
    			
    		$pollingOptionDb = new App_Model_Db_Table_PollOption();
    			
    		foreach ($ids as $pollId) {
    			$pollingDb->delete(['guid=?' => $pollId]);
    
    			$pollingOptionDb->delete(['pollGuid=?' => $pollId]);
    		}
    		$result = 'RESULT_OK';
    	}
    
    	$this->getResponse()->setBody($result);
    }
}
