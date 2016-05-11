<?php
/**
 * @author	2011-2018 Nihki Prihadi <nihki@madaniyah.com>
 * @version $Id: GetLegalBasis.php 1 2013-12-05 15:57Z $
 */

class Pandamp_Controller_Action_Helper_GetLegalBasis
{
	public function getLegalBasis($catalogGuid)
	{
		$relatedItemDb = new App_Model_Db_Table_RelatedItem();
		$relatedItems = $relatedItemDb->fetchAll("relatedGuid='$catalogGuid' AND relateAs='RELATED_BASE'");
		
		if (count($relatedItems) > 0) {
			return $relatedItems;
		}
		
		return;
	}
}