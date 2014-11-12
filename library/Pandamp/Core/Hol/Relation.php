<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Relation
 *
 * @author user
 */
class Pandamp_Core_Hol_Relation
{
    private $catalogGuid;

    public function getHistorynew($guid,$node)
    {
    	$newh = $s = '';
    	
    	$tblRelatedItem = new App_Model_Db_Table_RelatedItem();
    	 
    	$where_r = "relatedGuid='$guid' AND relateAs='ISROOT'";
    	$rowsetRelatedItem_r = $tblRelatedItem->fetchRow($where_r);
    	
    	$where = "relatedGuid='$guid' AND relateAs IN ('REPEAL','AMEND')";
    	$rowsetRelatedItem = $tblRelatedItem->fetchAll($where,'relatedGuid DESC');
    	if (count($rowsetRelatedItem) == 0) {
    		$where = "itemGuid='$guid' AND relateAs IN ('REPEAL','AMEND')";
    		$rowsetRelatedItem = $tblRelatedItem->fetchRow($where);
    		if (isset($rowsetRelatedItem->valueStringRelation)) {
    			$where_ro = "valueStringRelation='$rowsetRelatedItem->valueStringRelation' AND relateAs='ISROOT'";
    			$rowsetRelatedItem_ro = $tblRelatedItem->fetchRow($where_ro);
    			if ($rowsetRelatedItem_ro) {
    				if ($rowsetRelatedItem_ro->relateAs == 'ISROOT')
    					$s = '[mencabut sebagian]';
    	
    	
    				$newh .= "<a href='".ROOT_URL.DS.'id'.DS.'dms/catalog/detail/guid/'.$rowsetRelatedItem_ro->itemGuid.'/node/'.$this->getNode($rowsetRelatedItem_ro->itemGuid)."'>".App_Model_Show_CatalogAttribute::show()->getCatalogAttributeValue($rowsetRelatedItem_ro->itemGuid,'fixedTitle')."</a>&nbsp<a href='javascript:;' class='historynew' data-guid='$rowsetRelatedItem_ro->relatedGuid' data-historyid='$rowsetRelatedItem_ro->itemGuid' data-status='$rowsetRelatedItem_ro->relateAs'>Delete</a><br>";
    			}
    			 
    			$guid = $rowsetRelatedItem->valueStringRelation;
    			$where = "relatedGuid='$guid' AND relateAs IN ('REPEAL','AMEND')";
    			$rowsetRelatedItem = $tblRelatedItem->fetchAll($where,'relatedGuid DESC');
    		}
    	}
    	else
    	{
    		if ($rowsetRelatedItem_r) {
    			if ($rowsetRelatedItem_r->relateAs == 'ISROOT')
    				$s = '[mencabut sebagian]';
    			 
    			 
    			$newh .= "<a href='".ROOT_URL.DS.'id'.DS.'dms/catalog/detail/guid/'.$rowsetRelatedItem_r->itemGuid.'/node/'.$this->getNode($rowsetRelatedItem_r->itemGuid)."'>".App_Model_Show_CatalogAttribute::show()->getCatalogAttributeValue($rowsetRelatedItem_r->itemGuid,'fixedTitle')."</a>&nbsp<a href='javascript:;' class='historynew' data-guid='$rowsetRelatedItem_r->relatedGuid' data-historyid='$rowsetRelatedItem_r->itemGuid' data-status='$rowsetRelatedItem_r->relateAs'>Delete</a><br>";
    		}
    	
    	}
    	 
    	$newh .= App_Model_Show_CatalogAttribute::show()->getCatalogAttributeValue($guid,'fixedTitle').$s.'<br>';
    	 
    	foreach ($rowsetRelatedItem as $row) {
    		if ($row->relateAs === "REPEAL") {
    			$status = "dicabut";
    		}
    		if ($row->relateAs === "AMEND") {
    			$status = "merubah";
    		}
    		$title = App_Model_Show_CatalogAttribute::show()->getCatalogAttributeValue($row->itemGuid,'fixedTitle');
    		if ($row->relateAs === "AMEND") {
    			$newh .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='".ROOT_URL.DS.'id'.DS.'dms/catalog/detail/guid/'.$row->itemGuid.'/node/'.$this->getNode($row->itemGuid)."'>$title</a> [".$status."]&nbsp<a href='javascript:;' class='historynew' data-guid='$row->relatedGuid' data-historyid='$row->itemGuid' data-status='$row->relateAs'>Delete</a><br>";
    					$newh .= $this->isroot($row->itemGuid);
    					// 	    		$this->getchild($row->itemGuid);
    		}
    		else
    		{
    		$newh .= "<a href='".ROOT_URL.DS.'id'.DS.'dms/catalog/detail/guid/'.$row->itemGuid.'/node/'.$this->getNode($row->itemGuid)."'>$title</a> [".$status."]&nbsp<a href='javascript:;' class='historynew' data-guid='$row->relatedGuid' data-historyid='$row->itemGuid' data-status='$row->relateAs'>Delete</a><br>";
    		//     			$this->isroot($row->itemGuid);
    		$newh .= $this->getchild($row->itemGuid);
    		}
    	}
    	 
    	return $newh;
    }
    
    function getchild($guid,$level=0)
    {
    	$c='';
    	$tblRelatedItem = new App_Model_Db_Table_RelatedItem();
    	$where = "relatedGuid='$guid' AND relateAs IN ('REPEAL','AMEND')";
    	$rowsetRelatedItem = $tblRelatedItem->fetchAll($where,'relatedGuid DESC');
    	foreach ($rowsetRelatedItem as $row) {
    		$sTab="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
    		for($i=0;$i<$level;$i++)
    			$sTab.="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
    					 
    			if ($row->relateAs === "REPEAL") {
    					$status = "dicabut";
    			}
    							if ($row->relateAs === "AMEND") {
    									$status = "merubah";
    									}
    									$title = App_Model_Show_CatalogAttribute::show()->getCatalogAttributeValue($row->itemGuid,'fixedTitle');
    									if ($row->relateAs === "AMEND") {
    										$c .= $sTab."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='".ROOT_URL.DS.'id'.DS.'dms/catalog/detail/guid/'.$row->itemGuid.'/node/'.$this->getNode($row->itemGuid)."'>$title</a> [".$status."]&nbsp<a href='javascript:;' class='historynew' data-guid='$row->relatedGuid' data-historyid='$row->itemGuid' data-status='$row->relateAs'>Delete</a><br>";
    										//     			echo $sTab."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
    									//     			$this->isroot($row->itemGuid);
    									$c .= $this->getchild($row->itemGuid,$level+1);
    	}
    	else
    	{
    		$c .= $sTab."<a href='".ROOT_URL.DS.'id'.DS.'dms/catalog/detail/guid/'.$row->itemGuid.'/node/'.$this->getNode($row->itemGuid)."'>$title</a> [".$status."]&nbsp<a href='javascript:;' class='historynew' data-guid='$row->relatedGuid' data-historyid='$row->itemGuid' data-status='$row->relateAs'>Delete</a><br>";
// 	    		echo $sTab;
    		// 	    		$this->isroot($row->itemGuid);
    		$c .= $this->getchild($row->itemGuid,$level+1);
    	}
    	}
    	
    	return $c; 
    }
    
    function isroot($guid)
    {
    $where = "itemGuid='$guid' AND relateAs='ISROOT'";
    $tblRelatedItem = new App_Model_Db_Table_RelatedItem();
    $rowsetRelatedItem = $tblRelatedItem->fetchRow($where);
    if ($rowsetRelatedItem) {
    $title = App_Model_Show_CatalogAttribute::show()->getCatalogAttributeValue($rowsetRelatedItem->relatedGuid,'fixedTitle');
    return "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='".ROOT_URL.DS.'id'.DS.'dms/catalog/detail/guid/'.$rowsetRelatedItem->relatedGuid.'/node/'.$this->getNode($rowsetRelatedItem->relatedGuid)."'>$title</a>[mencabut sebagian]&nbsp<a href='javascript:;' class='historynew' data-guid='$rowsetRelatedItem->relatedGuid' data-historyid='$rowsetRelatedItem->itemGuid' data-status='$rowsetRelatedItem->relateAs'>Delete</a><br>";
    }
    }
    public function getNode($catalogGuid)
    {
    	$modelCatalogFolder = new App_Model_Db_Table_CatalogFolder();
    	$rowset = $modelCatalogFolder->fetchRow("catalogGuid='".$catalogGuid."'");
    
    	if ($rowset)
    		return $rowset->folderGuid;
    	else
    		return;
    }
    
    
    public function getHistory($catalogGuid)
    {
        return $this->getRelatedItem($catalogGuid,'RELATED_HISTORY');
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

    function delete($itemGuid, $relatedGuid, $relateAs)
    {
        if(empty($relateAs))
                throw new Zend_Exception('relateAs can not be empty!');

	    $registry = Zend_Registry::getInstance();
	    $config = $registry->get(Pandamp_Keys::REGISTRY_APP_OBJECT);
	    $cdn = $config->getOption('cdn');
	    
        $tblRelatedItem = new App_Model_Db_Table_RelatedItem();

        $rowsetRelatedDocs = $tblRelatedItem->fetchAll("itemGuid='$itemGuid' AND relatedGuid='$relatedGuid' AND relateAs='$relateAs'");
        if($rowsetRelatedDocs)
        {
            foreach ($rowsetRelatedDocs as $rowRelatedDoc)
            {
                $systemname = App_Model_Show_CatalogAttribute::show()->getCatalogAttributeValue($rowRelatedDoc->itemGuid,'docSystemName');
                $parentGuid = $rowRelatedDoc->relatedGuid;

                //$sDir1 = ROOT_DIR.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR.$systemname;
                $sDir1 = $cdn['static']['dir']['files'].DIRECTORY_SEPARATOR.$systemname;
                $sDir2 = $cdn['static']['dir']['files'].DIRECTORY_SEPARATOR.$parentGuid.DIRECTORY_SEPARATOR.$systemname;
                //$sDir2 = ROOT_DIR.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR.$parentGuid.DIRECTORY_SEPARATOR.$systemname;

                if(file_exists($sDir1))
                {
                    unlink($sDir1);
                }
                else
                    if(file_exists($sDir2))
                    {
                        unlink($sDir2);
                    }

                $systemname = $rowRelatedDoc->itemGuid;
                $parentGuid = $rowRelatedDoc->relatedGuid;

                $sDir = $cdn['static']['dir']['images'];
                $sDir2 = $cdn['static']['dir']['images'].DIRECTORY_SEPARATOR.$parentGuid;
                
                //$sDir = ROOT_DIR.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'images';
                //$sDir2 = ROOT_DIR.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.$parentGuid;

                if (file_exists($sDir."/".$systemname.".gif"))      { unlink($sDir."/".$systemname.".gif"); 	}
                if (file_exists($sDir."/tn_".$systemname.".gif"))   { unlink($sDir."/tn_".$systemname.".gif"); 	}
                if (file_exists($sDir."/".$systemname.".jpg"))      { unlink($sDir."/".$systemname.".jpg"); 	}
                if (file_exists($sDir."/tn_".$systemname.".jpg"))   { unlink($sDir."/tn_".$systemname.".jpg"); 	}
                if (file_exists($sDir."/".$systemname.".jpeg"))     { unlink($sDir."/".$systemname.".jpeg"); 	}
                if (file_exists($sDir."/tn_".$systemname.".jpeg"))  { unlink($sDir."/tn_".$systemname.".jpeg"); }
                if (file_exists($sDir."/".$systemname.".png"))      { unlink($sDir."/".$systemname.".png"); 	}
                if (file_exists($sDir."/tn_".$systemname.".png"))   { unlink($sDir."/tn_".$systemname.".png"); 	}

                if (file_exists($sDir2."/".$systemname.".gif"))      { unlink($sDir2."/".$systemname.".gif"); 	}
                if (file_exists($sDir2."/tn_".$systemname.".gif"))   { unlink($sDir2."/tn_".$systemname.".gif"); 	}
                if (file_exists($sDir2."/".$systemname.".jpg"))      { unlink($sDir2."/".$systemname.".jpg"); 	}
                if (file_exists($sDir2."/tn_".$systemname.".jpg"))   { unlink($sDir2."/tn_".$systemname.".jpg"); 	}
                if (file_exists($sDir2."/".$systemname.".jpeg"))     { unlink($sDir2."/".$systemname.".jpeg"); 	}
                if (file_exists($sDir2."/tn_".$systemname.".jpeg"))  { unlink($sDir2."/tn_".$systemname.".jpeg"); }
                if (file_exists($sDir2."/".$systemname.".png"))      { unlink($sDir2."/".$systemname.".png"); 	}
                if (file_exists($sDir2."/tn_".$systemname.".png"))   { unlink($sDir2."/tn_".$systemname.".png"); 	}




                $tblCatalog = new App_Model_Db_Table_Catalog();
                $rowCatalog = $tblCatalog->find($rowRelatedDoc->itemGuid)->current();
                $rowCatalog->delete();
            }
        }

        if($tblRelatedItem->delete("itemGuid='$itemGuid' AND relatedGuid='$relatedGuid' AND relateAs='$relateAs'"))
        {
            return true;
        }
        else
        {
            return false;
        }
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
    public function getLegalBasic($catalogGuid)
    {
        $tblRelatedItem = new App_Model_Db_Table_RelatedItem();

        $where = "relatedGuid='$catalogGuid' AND relateAs='RELATED_BASE'";
        $rowsetRelatedItem = $tblRelatedItem->fetchAll($where);

        return $rowsetRelatedItem;
    }
    public function getImplementRegulation($catalogGuid)
    {
        $tblRelatedItem = new App_Model_Db_Table_RelatedItem();

        $where = "itemGuid='$catalogGuid' AND relateAs='RELATED_BASE'";
        $rowsetRelatedItem = $tblRelatedItem->fetchAll($where);

        return $rowsetRelatedItem;
    }
    public function getFiles($catalogGuid)
    {
        $tblRelatedItem = new App_Model_Db_Table_RelatedItem();

        //$where = "relatedGuid='$catalogGuid' AND relateAs='RELATED_FILE'";
        $where = "relatedGuid='$catalogGuid' AND relateAs IN ('RELATED_FILE','RELATED_VIDEO')";
        $rowsetRelatedItem = $tblRelatedItem->fetchAll($where);

        return $rowsetRelatedItem;
    }
    public function getFilesImg($catalogGuid)
    {
        $tblRelatedItem = new App_Model_Db_Table_RelatedItem();

        //$where = "relatedGuid='$catalogGuid' AND relateAs='RELATED_FILE'";
        $where = "relatedGuid='$catalogGuid' AND relateAs = 'RELATED_IMAGE'";
        $rowsetRelatedItem = $tblRelatedItem->fetchAll($where);

        return $rowsetRelatedItem;
    }
    public function getOthers($catalogGuid)
    {
        return $this->getRelatedItem($catalogGuid,'RELATED_ITEM');
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
