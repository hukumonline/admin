<?php
class Pandamp_Controller_Action_Helper_GetItemRelated
{
	public function getItemRelated($itemGuid, $relateAs)
	{
		$relItemDb = new App_Model_Db_Table_RelatedItem();
		$relItem = $relItemDb->fetchRow("itemGuid='$itemGuid' AND relateAs='$relateAs'");
		return $relItem;
	}
}