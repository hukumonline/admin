<?php
class Pandamp_Application_Resource_Pio extends Zend_Application_Resource_ResourceAbstract
{
	public function init()
	{
		$options = array_change_key_case($this->getOptions(), CASE_LOWER);
		
		$eventClient = new Pandamp_Predictionio_EventClient($options['host'], $options['apikey']);
		return $eventClient;
	}
}