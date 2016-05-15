<?php
class Admin_IndexController extends Zend_Controller_Action
{
    protected $_user;
    protected $_signOut;

	public function preDispatch()
	{
		$this->_helper->layout->setLayout('lte');
		
		$auth = Zend_Auth::getInstance();
		
		$identity = Pandamp_Application::getResource('identity');
		
		$loginUrl = $identity->loginUrl;
		
		$multidb = Pandamp_Application::getResource('multidb');
		$multidb->init();
		
		$db = $multidb->getDb('db2');
		
		$sReturn = "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
		$sReturn = base64_encode($sReturn);
		
		if (!$auth->hasIdentity()) {
			$this->_redirect($loginUrl.'?returnUrl='.$sReturn);
		}
		else
		{
			$this->_user = $auth->getIdentity();
			
			$logoutUrl = $identity->logoutUrl;
			
			$this->_signOut = $logoutUrl.'/returnUrl/'.$sReturn;
			
			$zl  = Zend_Registry::get("Zend_Locale");
			
			$acl = Pandamp_Acl::manager();
			if (!$acl->checkAcl("site",'all','user', $this->_user->username, false,false))
			{
				header(ROOT_URL.'/'.$zl->getLanguage().'/error/restricted');
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
	
	public function indexAction()
	{
		
	}
	
	/**
	 * Sementara karena sebagian masih menggunakan template lama
	 */
	public function topnavAction()
    {
        $this->view->assign('logout', $this->_signOut);
    }
}