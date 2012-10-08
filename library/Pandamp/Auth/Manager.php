<?php
class Pandamp_Auth_Manager
{
	private $_identity;
	private $_credential;
	private $_authResult;
	
	public function __construct($identity, $credential)
	{
		$this->_identity = $identity;
		$this->_credential = $credential;
	}
	public function authenticate()
	{
		$registry = Zend_Registry::getInstance(); 
		$config = $registry->get(Pandamp_Keys::REGISTRY_APP_OBJECT);
		
		$config = Pandamp_Application::getResource('identity');
		
		$authAdapter = $config->authAdapter;
		
		$authAdapter->setIdentity($this->_identity)
			->setCredential($this->_credential);
		
		$auth = Zend_Auth::getInstance();
		$this->_authResult = $auth->authenticate($authAdapter);
		
		if ($this->_authResult->isValid())
		{
			$data = $authAdapter->getResultRowObject();
			$auth->getStorage()->write($data);	
			return $this->_authResult;
		} else {
			if($this->_authResult->getCode() != -51)
			{
				Zend_Auth::getInstance()->clearIdentity();
			}
			return $this->_authResult;
		}
	}
}