<?php

/**
 * Description of IndexController
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Admin_IndexController extends Zend_Controller_Action
{
    protected $_user;
    protected $_signOut;

    function  preDispatch()
    {
        $this->_helper->layout->setLayout('administry');

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

			$logoutUrl = $identity->logoutUrl;
			
			$this->_signOut = $logoutUrl.'/'.$sReturn;

            $acl = Pandamp_Acl::manager();
            if (!($acl->checkAcl("site",'all','user', $this->_user->username, false,false)))
            {
                $zl = Zend_Registry::get("Zend_Locale");
                //$this->_redirect(ROOT_URL.'/'.$zl->getLanguage().'/error/restricted');
                header(ROOT_URL.'/'.$zl->getLanguage().'/error/restricted');
            }
        }
    }
    function indexAction()
    {
        $this->view->user = $this->_user;
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
    function topnavAction()
    {
        $this->view->logout = $this->_signOut;
    }
}
