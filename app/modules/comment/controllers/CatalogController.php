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
        $this->_helper->layout->setLayout('layout-comment');
        $auth = Zend_Auth::getInstance();

		$identity = Pandamp_Application::getResource('identity');
		
        $sReturn = "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
        $sReturn = base64_encode($sReturn);

        //$sso = new Pandamp_Session_Remote();
        //$user = $sso->getInfo();

        if (!$auth->hasIdentity()) {
            //$this->_forward('login','account','admin');
			$loginUrl = $identity->loginUrl;
			
			$this->_redirect($loginUrl.'?returnTo='.$sReturn);     
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
    function browseAction()
    {
        if ($this->getRequest()->isXmlHttpRequest())
        {
            $this->_helper->layout()->disableLayout();
        }
        
        $r = $this->getRequest();
        $limit = ($r->getParam('limit'))?$r->getParam('limit'):25;
        $this->view->limit =$limit;
        $itemsPerPage = $limit;
        $this->view->itemsPerPage = $itemsPerPage;
        $offset = ($r->getParam('offset'))?$r->getParam('offset'):0;
        $this->view->offset = $offset;

        $commentList = App_Model_Show_Comment::show()->fetchComment($offset, $limit);
        $this->view->commentList = $commentList;

        $this->view->totalItems = App_Model_Show_Comment::show()->getNumOfComment();

        $this->_helper->layout()->headerTitle = "Comment";
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
