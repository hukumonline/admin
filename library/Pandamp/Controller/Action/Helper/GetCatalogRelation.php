<?php
/**
 * @author	2011-2018 Nihki Prihadi <nihki@madaniyah.com>
 * @version $Id: GetCatalogRelation.php 1 2013-12-06 09:51Z $
 */

class Pandamp_Controller_Action_Helper_GetCatalogRelation
{
	public function getCatalogRelation($catalogGuid)
	{
		$relatedItemDb = new App_Model_Db_Table_RelatedItem();
		$relatedItem = $relatedItemDb->fetchAll("relatedGuid='$catalogGuid' AND relateAs IN ('RELATED_OTHER','RELATED_ISSUE','RELATED_Clinic')");
		
		if (count($relatedItem) > 0) return $relatedItem;
		
		return;
	}
}