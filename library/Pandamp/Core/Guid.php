<?php

/**
 * module guid for universal prefix
 * 
 * @author Himawan Anindya Putra
 * @package Kutu
 * 
 */

class Pandamp_Core_Guid
{
	public function generateGuid($prefix=null)
	{
		$registry = Zend_Registry::getInstance(); 
		$application = $registry->get(Pandamp_Keys::REGISTRY_APP_OBJECT);
		$aGuidConfig =  $application->getOption('guid');
		$prefix = $aGuidConfig['prefix'];
		return uniqid($prefix);
	}	
}
