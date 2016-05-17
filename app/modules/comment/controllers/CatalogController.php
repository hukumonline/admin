<?php

/**
 * Description of CatalogController
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Comment_CatalogController extends Zend_Controller_Action
{
    protected $_user;
    function  preDispatch()
    {
        //$this->_helper->layout->setLayout('layout-comment');
        $this->_helper->layout->setLayout('layout-newpolling');
        
        $auth = Zend_Auth::getInstance();

		$identity = Pandamp_Application::getResource('identity');

		$loginUrl = $identity->loginUrl;
		
		/*
		$multidb = Pandamp_Application::getResource('multidb');
		$multidb->init();
		
		$db = $multidb->getDb('db2');
		*/
		
        $sReturn = "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
        $sReturn = base64_encode($sReturn);

        //$sso = new Pandamp_Session_Remote();
        //$user = $sso->getInfo();

        if (!$auth->hasIdentity()) {
            //$this->_forward('login','account','admin');
			
			$this->_redirect($loginUrl.'?returnUrl='.$sReturn);    
        }
        else
        {
            $this->_user = $auth->getIdentity();

            $zl = Zend_Registry::get("Zend_Locale");
            
            $acl = Pandamp_Acl::manager();
            if (!$acl->checkAcl("site",'all','user', $this->_user->username, false,false))
            {
                //$this->_redirect(ROOT_URL.'/'.$zl->getLanguage().'/error/restricted');
                $this->_forward('restricted','error','admin',array('lang'=>$zl->getLanguage()));
            }
            
			// [TODO] else: check if user has access to admin page and status website is online
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
			
			// check session expire
			/*
			$timeLeftTillSessionExpires = $_SESSION['__ZF']['Zend_Auth']['ENT'] - time();

			if (Pandamp_Lib_Formater::diff('now', $this->_user->dtime) > $timeLeftTillSessionExpires) {
				$db->update('KutuUser',array('ses'=>'*'),"ses='".Zend_Session::getId()."'");
				$flashMessenger = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
		        $flashMessenger->addMessage('Session Expired');
		        $auth->clearIdentity();
		        
		        $this->_redirect($loginUrl.'?returnUrl='.$sReturn);     
			}
			
			$dat = Pandamp_Lib_Formater::now();
			$db->update('KutuUser',array('dtime'=>$dat),"ses='".Zend_Session::getId()."'");
			*/
        }
    }
    function browseAction()
    {
        /*if ($this->getRequest()->isXmlHttpRequest())
        {
            $this->_helper->layout()->disableLayout();
        }*/
        
        $r = $this->getRequest();
        $limit = ($r->getParam('limit'))?$r->getParam('limit'):10;
        $this->view->limit =$limit;
        $itemsPerPage = $limit;
        $this->view->itemsPerPage = $itemsPerPage;
        //$offset = ($r->getParam('offset'))?$r->getParam('offset'):0;
        //$this->view->offset = $offset;
        
        $pageIndex = $r->getParam('page',1);
        
        $offset = ($pageIndex > 0) ? ($pageIndex - 1) * $itemsPerPage : 0;

        $commentList = App_Model_Show_Comment::show()->fetchComment($offset, $limit);
        $this->view->commentList = $commentList;

        $numOfRows = (new App_Model_Db_Table_Comment)->fetchAll()->count();
        
        //$totalItems = App_Model_Show_Comment::show()->getNumOfComment();
        
        $paginator = Zend_Paginator::factory($numOfRows);
        $paginator->setCurrentPageNumber($pageIndex);
        $paginator->setItemCountPerPage($itemsPerPage);
        
        $this->view->assign('perpage',$itemsPerPage);
        //$this->view->assign('totalItems',$totalItems);
        $this->view->assign('paginator',$paginator);
        $this->view->assign('currentPageNumber',$paginator->getCurrentPageNumber());
        
        //$this->_helper->layout()->headerTitle = "Comment";
    }
    
    public function delcomAction()
    {
    	$this->_helper->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender();
    
    	$request = $this->getRequest();
    	$result  = 'RESULT_ERROR';
    	if ($request->isPost()) {
    		$id = $request->getPost('id');
    		$ids = array();
    		$ids = Zend_Json::decode($id);
    		foreach ($ids as $articleId) {
    			try {
    				$modelComment = new App_Model_Db_Table_Comment();
    				$rowset = $modelComment->find($articleId)->current();
    				$rowset->delete();
    			}
    			catch (Exception $e)
    			{
    				throw new Zend_Exception($e->getMessage());
    			}
    
    
    		}
    			
    		$result = 'RESULT_OK';
    	}
    
    	$this->getResponse()->setBody($result);
    }
    
    function deleteAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();

        $urlReferer = $_SERVER['HTTP_REFERER'];

        $id = ($this->_getParam('id'))? $this->_getParam('id') : '';
        $modelComment = new App_Model_Db_Table_Comment();
        $rowset = $modelComment->find($id);
        try {
            $row = $rowset->current();

            $row->delete();
        }
        catch (Zend_Exception $e)
        {
        }

        $sessHistory = new Zend_Session_Namespace('BROWSER_HISTORY');
        $sessHistory->urlReferer = $urlReferer;
        $this->_redirect($sessHistory->urlReferer);
    }
    function statusAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();

        $urlReferer = $_SERVER['HTTP_REFERER'];

        $status	= ($this->_getParam('st'))? $this->_getParam('st') : '';
        $id	= ($this->_getParam('id'))? $this->_getParam('id') : '';
        
        $modelComment = new App_Model_Db_Table_Comment();
        $row = $modelComment->find($id)->current();

        if ($status == 0)
        {
            $row->published = 0;
        }
        else
        {
            $row->published = 99;
        }

        $row->save();

        $sessHistory = new Zend_Session_Namespace('BROWSER_HISTORY');
        $sessHistory->urlReferer = $urlReferer;
        $this->_redirect($sessHistory->urlReferer);
    }
}
