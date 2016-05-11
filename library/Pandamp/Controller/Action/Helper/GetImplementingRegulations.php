<?php
/**
 * @author	2011-2018 Nihki Prihadi <nihki@madaniyah.com>
 * @version $Id: GetImplementingRegulations.php 1 2013-12-05 17:18Z $
 */

class Pandamp_Controller_Action_Helper_GetImplementingRegulations
{
	public function getImplementingRegulations($catalogGuid)
	{
		$relatedItemDb = new App_Model_Db_Table_RelatedItem();
		$relatedItems = $relatedItemDb->fetchAll("itemGuid='$catalogGuid' AND relateAs='RELATED_BASE'");
		
		if (count($relatedItems) > 0) {
			return $relatedItems;
		}
		
		return;
	}
}