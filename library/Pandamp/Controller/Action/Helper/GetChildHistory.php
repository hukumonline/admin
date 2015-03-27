<?php
class Pandamp_Controller_Action_Helper_GetChildHistory
{
	public function getChildHistory($guid,$parent)
	{
		$helper = new Pandamp_Core_Hol_Relation();
		
		$tblRelatedItem = new App_Model_Db_Table_RelatedItem();
		$h1 = $tblRelatedItem->fetchAll("relatedGuid='$guid' AND valueStringRelation='$parent' AND relateAs IN ('ISROOT','AMEND','REPEAL','ESTABLISH')");
		if (count($h1) > 0) {
			$data1 = array();
			$content1 = 0;
			foreach ($h1 as $h1story)
			{
				$data1[$content1]['itemGuid'] = $h1story->itemGuid;
				$data1[$content1]['relatedGuid'] = $h1story->relatedGuid;
				$data1[$content1]['relateAs'] = $h1story->relateAs;
				
				$content1++;
			}
		}
		
		$h2 = $tblRelatedItem->fetchAll("itemGuid='$guid' AND valueStringRelation='$parent' AND relateAs IN ('ISROOT','AMEND','REPEAL','ESTABLISH')");
		if (count($h2) > 0) {
			$data2 = array();
			$content2 = 0;
			foreach ($h2 as $h)
			{
				$data2[$content2]['itemGuid'] = $h->relatedGuid;
				$data2[$content2]['relatedGuid'] = $h->relatedGuid;
				$data2[$content2]['relateAs'] = $h->relateAs;
				
				$content2++;
			}
			
			if (count($h1) > 0) {
				$merge = array_merge($data1, $data2);
				$merge = $helper->findperaturanyear($merge, $parent);
				return $merge;
			}	
			
			return $helper->findperaturanyear($data2, $parent);
			
		}
		
		if (isset($data1))
			return $helper->findperaturanyear($data1, $parent);
	}
}