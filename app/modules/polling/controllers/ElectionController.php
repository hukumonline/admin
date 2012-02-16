<?php

/**
 * Description of ElectionController
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Polling_ElectionController extends Zend_Controller_Action
{
    protected $_user;
    function  preDispatch()
    {
        $this->_helper->layout->setLayout('layout-polling');
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
        $pollId = ($this->_getParam('id'))? $this->_getParam('id') : '';

        $tblPolling = new App_Model_Db_Table_Poll();

        $time = time();
        $date = date("Y-m-d H:i:s", $time);

        $rowPoll = $tblPolling->fetchAll("guid NOT IN('$pollId') AND checkedTime < '$date'","checkedTime DESC");
        $this->view->rowPoll = $rowPoll;

        $this->view->pollId = $pollId;
        
        $this->_helper->layout()->headerTitle = "Polling";
    }
    function whosAction()
    {
        $pollId = ($this->_getParam('id'))? $this->_getParam('id') : '';

        $tblPolling = new App_Model_Db_Table_Poll();

        $time = time();
        $date = date("Y-m-d H:i:s", $time);

        $rowPoll = $tblPolling->fetchRow("guid='$pollId' AND checkedTime < '$date'","checkedTime DESC");
        $this->view->rowPoll = $rowPoll;

        $this->view->pollId = $pollId;
        
        $this->_helper->layout()->headerTitle = "Polling";
    }
    function deleteAction()
    {
        $pguid = ($this->_getParam('id'))? $this->_getParam('id') : '';
        $hol = new Pandamp_Core_Hol_Poll();
        try {
            $hol->delete($pguid);
            $message = $pguid." has been deleted successfully";
        }
        catch (Exception $e)
        {
            $message = $e->getMessage();
        }
        
        $this->view->message = $message;
        $this->_helper->layout()->headerTitle = "Polling";
    }
    function newAction()
    {
        $r = $this->getRequest();
        if ($r->isPost())
        {
            $aData = $r->getParams();
            try {
                $hol = new Pandamp_Core_Hol_Poll();
                $hol->save($aData);

                $message = "Polling has been successfully saved";
            }
            catch (Exception $e)
            {
                $message = $e->getMessage();
            }

            $this->view->message = $message;
        }
        
        $this->_helper->layout()->headerTitle = "Polling";
    }
}
