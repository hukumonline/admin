<?php
use GuzzleHttp\Stream\__construct;

require_once("vendor/autoload.php");
use predictionio\EventClient,
	predictionio\EngineClient,
	GuzzleHttp\Client;

class Pandamp_Predictionio_EventClient
{
	private $_client;
	
	public function __construct($pioHost, $pioApiKey)
	{
		$this->_client = new EventClient($pioApiKey, $pioHost);
		
		
	}
	
	public function ping()
	{
		$pio = &$this->_client;
		
		$response = null;
		
		try {
			$response = $pio->getStatus();
		}
		catch (Exception $e)
		{
			
		}
		
		if (Zend_Json::decode($response)['status'] == 'alive')
		{
			return true;
		}
		
		return false;
	}
	
	public function addEvent($data = [])
	{
		//if (self::checkExist($data['guid'])) return;
		
		//Zend_Controller_Action_HelperBroker::addPrefix('Pandamp_Controller_Action_Helper');
		//$i = Zend_Controller_Action_HelperBroker::getStaticHelper('GetNumber')->generate('pio');
		
		$client_response = $this->_client->setItem($data['guid'],[
			'category' => $data['category']
		]);
		
		/*if ($client_response['eventId'])
			Zend_Controller_Action_HelperBroker::getStaticHelper('GetNumber')->counter('pio');*/
		
		//return $client_response;
	}
	
	public function sendQuery($data = [])
	{
		$client = new EngineClient('http://localhost:8000');
		return $client->sendQuery($data);
	}
	
	protected function checkExist($guid)
	{
		$multidb = Pandamp_Application::getResource('multidb');
		$multidb->init();
		
		$registry = Zend_Registry::getInstance();
		$application = Zend_Registry::get(Pandamp_Keys::REGISTRY_APP_OBJECT);
		
		$db = $multidb->getDb('db6');
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
		$select = $db->select();
		$select->from($application->getOption('resources')['pio']['eventfield'], '*');
		$select->where("properties LIKE '%$guid%'");
		$row = $db->fetchRow($select);
		
		return $row;
	}
} 