<?php

/**
 * Description of ManagerController
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Agenda_ManagerController extends Zend_Controller_Action
{
    protected $_user;
    function  preDispatch()
    {
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

        $month = (int) $this->_getParam('month');
        $year = (int) $this->_getParam('year');
        
        $m = (!$month)? date("n") : $month;
        $y = (!$year)? date("Y") : $year;
        
        $calendar = new Pandamp_Lib_Calendar();

        $this->view->select_calender = $calendar->writeCalendar($m,$y);
    }
}
