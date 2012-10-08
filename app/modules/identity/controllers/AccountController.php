<?php
/**
 * @author	2012-2013 Nihki Prihadi <nihki@madaniyah.com>
 * @version $Id: AccountController.php 1 2012-10-05 14:45Z $
 */

class Identity_AccountController extends Zend_Controller_Action 
{
    function  preDispatch()
    {
        $this->_helper->layout->setLayout('layout-login');
    }
	public function loginAction()
	{
		$request 	= $this->getRequest();
		$returnUrl 	= $request->getParam('returnUrl', null);
		
		$this->view->assign('returnUrl', $returnUrl);
	}
	
	/**
	 * Login user
	 *
	 */
	public function kloginAction()
	{
		$this->_helper->getHelper('viewRenderer')->setNoRender();
		$this->_helper->getHelper('layout')->disableLayout();
		
		$response = array();
		
		$request  = $this->getRequest();
		
		/**
		 * Redirect to dashboard if user has logged in already
		 */ 
		if ($request->isPost()) {
			$username = $request->getPost('u');
			$password = $request->getPost('p');
			$remember = $request->getPost('s');
			
			$authMan = new Pandamp_Auth_Manager($username, $password);
			$authResult = $authMan->authenticate();
					
			$zendAuth = Zend_Auth::getInstance();
			if($zendAuth->hasIdentity())
			{
				if($authResult->isValid()) {
					$returnUrl = base64_decode($request->getPost('r'));
					if(!empty($returnUrl))
					{
						if(strpos($returnUrl,'?'))
							$sAddition = '&';
						else 
							$sAddition = '?';
							
						$data = array(
							'success'		=> true,
							'msg'			=> 'Logging in',
							'message'		=> "$returnUrl".$sAddition."PHPSESSID=".Zend_Session::getId(),
						);
						
						Pandamp_Lib_Formater::writeLog();
						
						if (isset($remember) && $remember == 'yes') {
						$hol = new Pandamp_Core_Hol_Auth();
						$hol->user = $username;
						$hol->user_pw = $password;
						$hol->save_login = $remember;
						$hol->login_saver();
						}
						
						$this->_helper->FlashMessenger('Successful authentication');
					}					
				}
				else 
				{
					if($authResult->getCode() != -51)
					{
						Zend_Auth::getInstance()->clearIdentity();
					}
					$messages = $authResult->getMessages();
					$data = array(
						'error'		=> $messages[0],
						'success'	=> false,
					);
				}
			}
			else 
			{
				$messages = $authResult->getMessages();
				$data = array(
					'error'		=> $messages[0],
					'failure'	=> true,
				);
			}
		}
		
		$this->getResponse()->setBody(Zend_Json::encode($data));	
	}
	
	/**
	 * User logout
	 */
	public function logoutAction()
	{
		$sReturn = $this->getRequest()->getParam('returnUrl', null);
		$iReturn = base64_decode($sReturn);
		
		Pandamp_Lib_Formater::updateUserLog();
		
        $auth = Zend_Auth::getInstance();
        $auth->clearIdentity();
        
        $this->_helper->FlashMessenger('You were logged out');
        return $this->_redirect($iReturn);
	}
}
