<?php
class Pandamp_Controller_Action_Helper_ShortUrl
{
	function shortUrl($query, $offset=0, $limit=2000, $aParams)
	{
		try {
			$solr = Pandamp_Search_Engine::factory()->getShorturlConnection();
			$hits = $solr->search($query, $offset, $limit, $aParams);
			
			if ( $hits->getHttpStatus() == 200 )
				return $hits;
		
		}
		catch (Exception $e)
		{
			
		}
		
	}
}