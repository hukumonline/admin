<?php
/**
 * @author	2011-2018 Nihki Prihadi <nihki@madaniyah.com>
 * @version $Id: Connection.php 1 2016-05-18 13:31
 */

require_once( 'Apache/Solr/Service.php' );
class Pandamp_Search_Index_Solr_Connection extends Pandamp_Search_Index_Abstract
{
	protected function _connect($config)
	{
		$solr = new Apache_Solr_Service( $config['host'], $config['port'], $config['path'] );
		
		return $solr;
	}
	
	public function search($querySolr, $start = 0 , $end = 2000, $aParams, $method="GET")
	{
		$solr = $this->getMasterConnection();
		return $solr->search( $querySolr, $start, $end, $aParams, $method);
	}
}