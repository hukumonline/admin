<?php
class Pandamp_Search_Engine
{
	public static function factory($config = array())
	{
		$solrHost = $config['host'];
		$solrPort = $config['port'];
		$solrHomeDir = $config['homedir'];
		$newAdapter = new Pandamp_Search_Adapter_Solr($solrHost, $solrPort, $solrHomeDir);
		
		return $newAdapter;
	}
}