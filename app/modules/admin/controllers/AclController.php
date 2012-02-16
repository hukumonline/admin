<?php

/**
 * Description of AclController
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Admin_AclController extends Zend_Controller_Action
{
    protected $_user;
    protected $_zl;

    function  preDispatch()
    {
        $this->_helper->layout->setLayout('layout-acl');

        $auth = Zend_Auth::getInstance();

		$identity = Pandamp_Application::getResource('identity');
		
        $sReturn = "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
        $sReturn = base64_encode($sReturn);

        //$sso = new Pandamp_Session_Remote();
        //$user = $sso->getInfo();

        if (!$auth->hasIdentity()) {
            //$this->_forward('login','account','admin');
			$loginUrl = $identity->loginUrl;
			
			//$this->_redirect($loginUrl.'?returnTo='.$sReturn);     
			$this->_redirect($loginUrl.'/returnUrl/'.$sReturn);
        }
        else
        {
            //$this->_user = $auth->getIdentity();
            $idt = $auth->getIdentity();
			//$this->_user = $identity['properties'];
			$this->_user = new stdClass();
			$this->_user->kopel 	= $idt['properties']['kopel'];
			$this->_user->username 	= $idt['properties']['username'];
			$this->_user->packageId = $idt['properties']['packageId'];
            
            $zl = Zend_Registry::get("Zend_Locale");
            
            $this->_zl = $zl;

            $acl = Pandamp_Acl::manager();
            if (!$acl->checkAcl("site",'all','user', $this->_user->username, false,false))
            {
                $this->_redirect(ROOT_URL.'/'.$this->_zl->getLanguage().'/error/restricted');
            }
        }
    }
    public function indexAction()
    {
    	/*
        if (!Pandamp_Controller_Action_Helper_IsAllowed::isAllowed('membership','all'))
        {
            $this->_redirect(ROOT_URL.'/'.$this->_zl->getLanguage().'/error/restricted');
        }

        $this->_helper->layout()->headerTitle = "Access Control Management";
        */
    }
    public function headerAction()
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
