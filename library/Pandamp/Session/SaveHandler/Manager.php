<?php
class Pandamp_Session_SaveHandler_Manager
{
	public function setSaveHandler()
	{
		$registry = Zend_Registry::getInstance(); 
		$application = $registry->get(Pandamp_Keys::REGISTRY_APP_OBJECT);
		$application->getBootstrap()->bootstrap('session');
		$saveHandler = $application->getBootstrap()->getResource('session');
		Zend_Session::setSaveHandler($saveHandler);
	}
}
?>