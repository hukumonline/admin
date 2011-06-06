<?php

/**
 * Description of AccountController
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Admin_AccountController extends Zend_Controller_Action
{
    function loginAction()
    {
        $this->_helper->layout->disableLayout();
        $sReturn = "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];

        $request = $this->getRequest();

        $username = ($request->getParam('username'))? $request->getParam('username') : '';
        $password = ($request->getParam('password'))? $request->getParam('password') : '';
        $lang = ($request->getParam('langselector'))? $request->getParam('langselector') : '';

        //$sso = new Pandamp_Session_Remote();

        //$this->view->broker = $sso->broker;

        if ($this->getRequest()->isPost())
        {
            $locale = Zend_Registry::get('Zend_Locale');
            $zl = $locale->getLanguage();

            if (strpos($sReturn,$zl))
            {
                $e = str_replace($zl, $lang, $_SERVER['REQUEST_URI']);
                $sReturn = "http://".$_SERVER['SERVER_NAME'].$e;
            } else {
                $sReturn = "http://".$_SERVER['SERVER_NAME']."/".$lang;
            }

            $authAdapter = new Pandamp_Auth_Manager($username, $password);

            $authResult = $authAdapter->authenticate();

            if ($authResult->isValid())
            {
                $this->_redirect($sReturn);
            }
            else
            {
                $messages = $authResult->getMessages();
                $this->view->message = $messages[0];
            }
        }
    }
    function logoutAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();

        $returnTo = ($this->_getParam('sReturn'))? $this->_getParam('sReturn') : '';
        //$sso = new Pandamp_Session_Remote();
        //$sso->logout();

        $this->_redirect(base64_decode($returnTo));
    }
}
