<?php

class Setting_IndexController extends Zend_Controller_Action
{
    protected $_user;

    function  preDispatch()
    {
        $this->_helper->layout->setLayout('layout-setting');

        $auth = Zend_Auth::getInstance();
        
        $identity = Pandamp_Application::getResource('identity');

        $sReturn = "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
        $sReturn = base64_encode($sReturn);

        if (!$auth->hasIdentity()) {
			$loginUrl = $identity->loginUrl;
			
			$this->_redirect($loginUrl.'?returnUrl='.$sReturn);     
        }
        else
        {
            $this->_user = $auth->getIdentity();

            $acl = Pandamp_Acl::manager();
            if (!$acl->checkAcl("site",'all','user', $this->_user->username, false,false))
            {
                $zl = Zend_Registry::get("Zend_Locale");
                $this->_redirect(ROOT_URL.'/'.$zl->getLanguage().'/error/restricted');
            }
        }
    }
    function indexAction()
    {
    	$request = $this->getRequest();
    	
		$tblSetting = new App_Model_Db_Table_Setting();
		$rowset = $tblSetting->find(1)->current();
		
		if ($request->isPost()) {
	        $data = array(
	        	'status' => $request->getPost('status'),
	        	'frontend' => $request->getPost('frontend'),
	            'searchend' => $request->getPost('searchend'),
	            'logstat' => $request->getPost('logstat')
	        );
	
	        $tblSetting->update($data, "id=1");
	        
	        
			$rowset = $tblSetting->find(1)->current();
			
			$this->view->assign('rowset',$rowset);			
		}
		
		if ($rowset)
		{
			$this->view->assign('rowset',$rowset);
		}		
    }	
    function headerAction()
    {
        $r = $this->getRequest();
        $sOffset = $r->getParam('sOffset');
        $this->view->sOffset = $sOffset;
        $sLimit = $r->getParam('sLimit');
        $this->view->sLimit = $sLimit;

        $query = ($r->getParam('q'))? $r->getParam('q') : '';
        $this->_helper->layout()->searchQuery = $query;
        $this->view->user = $this->_user;
    }
}