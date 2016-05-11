<?php
/**
 * @author	2011-2018 Nihki Prihadi <nihki@madaniyah.com>
 * @version $Id: GetHistory.php 1 2013-12-05 18:00Z $
 */

class Pandamp_Controller_Action_Helper_GetHistory
{
	private $catalogGuid;
	
	public function getHistory($catalogGuid)
	{
		$history = $this->getRelatedItem($catalogGuid,'RELATED_HISTORY');
		
		$content = 0;
		$data = array();
		
		foreach ($history as $h) {
			if ($h['node'] == $catalogGuid) continue;
			
			$data[$content]['node'] = $h['node'];
			$data[$content]['title'] = $h['title'];
			$data[$content]['subTitle'] = $h['subTitle'];
			$data[$content]['description'] = $h['description'];
			$data[$content]['fixedDate'] = $h['fixedDate'];
			$data[$content]['nodeLeft'] = $h['nodeLeft'];
			$data[$content]['nodeRight'] = $h['nodeRight'];
			
			$content++;
		}
		
		if (count($data) > 0) return $data;
		
		return;
	}
	
	public function getRelatedItem($catalogGuid, $relateAs)
	{
		$this->catalogGuid = $catalogGuid;
	
		$a2 = array();
		$aNodesTraversed = array();
		$this->_traverseHistory($aNodesTraversed, $a2,$catalogGuid, $relateAs);
	
		$tblCatalogAttribute  = new App_Model_Db_Table_CatalogAttribute();
		$aTmp2['node'] = $catalogGuid;
		$aTmp2['nodeLeft'] = 'tmpLeft';
		$aTmp2['nodeRight'] =  'tmpRight';
		$aTmp2['description'] = '';
		$aTmp2['relationType'] = '';
	
		$where2 = "catalogGuid='$catalogGuid' AND attributeGuid='fixedTitle'";
		$rowCatalogAttribute = $tblCatalogAttribute->fetchRow($where2);
		if(isset($rowCatalogAttribute->value))
			$aTmp2['title'] = $rowCatalogAttribute->value;
		else
			$aTmp2['title'] = 'No-Title';
	
		$where2 = "catalogGuid='$catalogGuid' AND attributeGuid='fixedSubTitle'";
		$rowCatalogAttribute = $tblCatalogAttribute->fetchRow($where2);
		if(isset($rowCatalogAttribute->value))
			$aTmp2['subTitle'] = $rowCatalogAttribute->value;
		else
			$aTmp2['subTitle'] = 'No-Title';
	
		$where2 = "catalogGuid='$catalogGuid' AND attributeGuid='fixedDate'";
		$rowCatalogAttribute = $tblCatalogAttribute->fetchRow($where2);
		if(isset($rowCatalogAttribute->value))
			$aTmp2['fixedDate'] = $rowCatalogAttribute->value;
		else
			$aTmp2['fixedDate'] = '00-00-00';
	
		array_push($a2, $aTmp2);
	
		UtilHistorySort::sort($a2, 'fixedDate', false);
	
		return $a2;
	}
	
	function _traverseHistory(&$aNodesTraversed, &$a2, $node, $relateAs='RELATED_ITEM')
	{
		array_push($aNodesTraversed, $node);
		$aTmp = $this->_getNodes($node, $relateAs);
	
		foreach ($aTmp as $node2)
		{
			if(!$this->_checkTraverse($aNodesTraversed, $node2['node']))
			{
				array_push($a2, $node2);
				$this->_traverseHistory($aNodesTraversed, $a2, $node2['node'], $relateAs);
			}
		}
		return true;
	}
	
	function _checkTraverse($a, $node)
	{
		foreach($a as $row)
		{
			if($row == $node)
			{
				return true;
			}
		}
		return false;
	}
	
	function _getNodes($node, $relateAs='RELATED_ITEM')
	{
		$a = array();
	
		$tblRelatedItem = new App_Model_Db_Table_RelatedItem();
		$tblCatalogAttribute  = new App_Model_Db_Table_CatalogAttribute();
	
		$where = "relatedGuid='$node' AND relateAs='$relateAs'";
		$rowsetRelatedItem = $tblRelatedItem->fetchAll($where);
	
		foreach ($rowsetRelatedItem as $row)
		{
			$aTmp2['node'] = $row->itemGuid;
			$aTmp2['nodeLeft'] = $row->itemGuid;
			$aTmp2['nodeRight'] =  $node;
			$aTmp2['description'] = $row->description;
			$aTmp2['relationType'] = $row->relationType;
	
			$where2 = "catalogGuid='$row->itemGuid' AND attributeGuid='fixedTitle'";
			$rowCatalogAttribute = $tblCatalogAttribute->fetchRow($where2);
			if(isset($rowCatalogAttribute->value))
				$aTmp2['title'] = $rowCatalogAttribute->value;
			else
				$aTmp2['title'] = 'No-Title';
	
			$where2 = "catalogGuid='$row->itemGuid' AND attributeGuid='fixedSubTitle'";
			$rowCatalogAttribute = $tblCatalogAttribute->fetchRow($where2);
			if(isset($rowCatalogAttribute->value))
				$aTmp2['subTitle'] = $rowCatalogAttribute->value;
			else
				$aTmp2['subTitle'] = 'No-Title';
	
			$where2 = "catalogGuid='$row->itemGuid' AND attributeGuid='fixedDate'";
			$rowCatalogAttribute = $tblCatalogAttribute->fetchRow($where2);
			if(isset($rowCatalogAttribute->value))
				$aTmp2['fixedDate'] = $rowCatalogAttribute->value;
			else
				$aTmp2['fixedDate'] = '';
	
			array_push($a, $aTmp2);
		}
	
		$where = "itemGuid='$node' AND relateAs='$relateAs'";
		$rowsetRelatedItem = $tblRelatedItem->fetchAll($where);
	
		foreach ($rowsetRelatedItem as $row)
		{
			$aTmp2['node'] = $row->relatedGuid;
			$aTmp2['nodeLeft'] = $node;
			$aTmp2['nodeRight'] =  $row->relatedGuid;
			$aTmp2['description'] = $row->description;
			$aTmp2['relationType'] = $row->relationType;
	
			$where2 = "catalogGuid='$row->relatedGuid' AND attributeGuid='fixedTitle'";
			$rowCatalogAttribute = $tblCatalogAttribute->fetchRow($where2);
			if(isset($rowCatalogAttribute->value))
				$aTmp2['title'] = $rowCatalogAttribute->value;
			else
				$aTmp2['title'] = 'No-Title';
	
			$where2 = "catalogGuid='$row->relatedGuid' AND attributeGuid='fixedSubTitle'";
			$rowCatalogAttribute = $tblCatalogAttribute->fetchRow($where2);
			if(isset($rowCatalogAttribute->value))
				$aTmp2['subTitle'] = $rowCatalogAttribute->value;
			else
				$aTmp2['subTitle'] = 'No-Title';
	
			$where2 = "catalogGuid='$row->relatedGuid' AND attributeGuid='fixedDate'";
			$rowCatalogAttribute = $tblCatalogAttribute->fetchRow($where2);
			if(isset($rowCatalogAttribute->value))
				$aTmp2['fixedDate'] = $rowCatalogAttribute->value;
			else
				$aTmp2['fixedDate'] = '';
	
			array_push($a, $aTmp2);
		}
	
		return $a;
	}
	
}

class UtilHistorySort
{
	static private $sortfield = null;
	static private $sortorder = 1;
	static private function sort_callback(&$a, &$b) {
		if($a[self::$sortfield] == $b[self::$sortfield]) return 0;
		return ($a[self::$sortfield] < $b[self::$sortfield])? -self::$sortorder : self::$sortorder;
	}
	static function sort(&$v, $field, $asc=true) {
		self::$sortfield = $field;
		self::$sortorder = $asc? 1 : -1;
		usort($v, array('UtilHistorySort', 'sort_callback'));
	}
}