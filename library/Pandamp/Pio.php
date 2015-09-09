<?php
class Pandamp_Pio
{
	public static function manager()
	{
		$registry = Zend_Registry::getInstance();
		$application = Zend_Registry::get(Pandamp_Keys::REGISTRY_APP_OBJECT);
		$application->getBootstrap()->bootstrap('pio');
		return $application->getBootstrap()->getResource('pio');
	}
}