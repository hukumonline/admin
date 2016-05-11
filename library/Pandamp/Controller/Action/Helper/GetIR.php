<?php
/**
 * @author	2011-2018 Nihki Prihadi <nihki@madaniyah.com>
 * @version $Id: GetIR.php 1 2015-03-06 15:19Z $
 */

class Pandamp_Controller_Action_Helper_GetIR
{
	public function getIR($catalogGuid)
	{
		$relatedItemDb = new App_Model_Db_Table_RelatedItem();
		$relatedItems = $relatedItemDb->fetchAll("relatedGuid='$catalogGuid' AND relateAs='RELATED_PP'");
		
		if (count($relatedItems) > 0) {
			return $relatedItems;
		}
		
		return;
	}
}