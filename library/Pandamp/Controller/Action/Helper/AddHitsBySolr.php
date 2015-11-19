<?php
class Pandamp_Controller_Action_Helper_AddHitsBySolr
{
	function addHitsBySolr($jsonData)
	{
		$registry = Zend_Registry::getInstance();
		$application=Zend_Registry::get(Pandamp_Keys::REGISTRY_APP_OBJECT);
		
		$res=$application->getOption('resources')['indexing']['solr']['write'];
		
		//$link= $res["host"].":".$res["port"].$res["dir1"].'/update?commit=true';
		$link= $res["host"].":".$res["port"].$res["dir1"].'/update?commitWithin=10000';
		
		//$ch = curl_init('localhost:8983/solr/corehol/update?commit=true');
		$ch = curl_init($link);
		//curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/json',
		'Content-Length: ' . strlen($jsonData))
		);
		return curl_exec($ch);
	}
}