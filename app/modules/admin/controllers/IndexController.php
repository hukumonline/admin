<?php
/**
 * Description of IndexController
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
			
			$this->_redirect($loginUrl.'?returnUrl='.$sReturn);     
        }
        else
        {
            $this->_user = $auth->getIdentity();
			
			$logoutUrl = $identity->logoutUrl;
			
			//$this->_signOut = $logoutUrl.'/'.$sReturn;
			$this->_signOut = $logoutUrl.'/returnUrl/'.$sReturn;

			$zl  = Zend_Registry::get("Zend_Locale");
			
            $acl = Pandamp_Acl::manager();
            if (!($acl->checkAcl("site",'all','user', $this->_user->username, false,false)))
            {
                //$this->_redirect(ROOT_URL.'/'.$zl->getLanguage().'/error/restricted');
                header(ROOT_URL.'/'.$zl->getLanguage().'/error/restricted');
            }
            
            
			// [TODO] else: check if user has access to admin page and status website is online
			$tblSetting = new App_Model_Db_Table_Setting();
			$rowset = $tblSetting->find(1)->current();
			
			if ($rowset)
			{
				if ($rowset->status == 1 && $zl->getLanguage() == 'id')
				{
					// it means that user offline other than admin
					$aReturn = App_Model_Show_AroGroup::show()->getUserGroup($this->_user->packageId);
					
					if (isset($aReturn['name']))
					{
						//if (($aReturn[1] !== "admin"))
						if (($aReturn['name'] !== "Master") && ($aReturn['name'] !== "Super Admin"))
						{
							$this->_forward('temporary','error','admin'); 
						}
					}
				}
				else if ($rowset->status == 2 && $zl->getLanguage() == 'en')
				{
					// it means that user offline other than admin
					$aReturn = App_Model_Show_AroGroup::show()->getUserGroup($this->_user->packageId);
					
					if (isset($aReturn['name']))
					{
						//if (($aReturn[1] !== "admin"))
						if (($aReturn['name'] !== "Master") && ($aReturn['name'] !== "Super Admin"))
						{
							$this->_forward('temporary','error','admin'); 
						}
					}
				}
				else if ($rowset->status == 3)
				{
					// it means that user offline other than admin
					$aReturn = App_Model_Show_AroGroup::show()->getUserGroup($this->_user->packageId);
					
					if (isset($aReturn['name']))
					{
						//if (($aReturn[1] !== "admin"))
						if (($aReturn['name'] !== "Master") && ($aReturn['name'] !== "Super Admin"))
						{
							$this->_forward('temporary','error','admin'); 
						}
					}
				}
				else 
				{
					return;
				}
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
    function headererrAction()
    {
    	$this->view->user = $this->_user;
    }
    function topnavAction()
    {
        $this->view->logout = $this->_signOut;
    }
}
