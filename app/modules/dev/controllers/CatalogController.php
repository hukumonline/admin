<?php

/**
 * Description of CatalogController
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Dev_CatalogController extends Zend_Controller_Action
{
    function  preDispatch()
    {
        $this->_helper->layout->setLayout('layout-customer-migration');
    }
    function doindexAction()
    {
        $this->_helper->viewRenderer->setNoRender(TRUE);
        $title = "<h4>HUKUMONLINE INDONESIA: <small>indexing</small></h4><hr/>";

        echo $title.'<br>';

        $modelCatalogFolder = new App_Model_Db_Table_CatalogFolder();
        $rowset = $modelCatalogFolder->fetchAll("folderGuid='fb16'",NULL,2,0);
        $solrAdapter = Pandamp_Search::manager();
        //$solrAdapter->emptyIndex();
        $solrAdapter->reIndexCatalog();
        //$solrAdapter->indexCatalog("hol10111");

        /*
        $numi = count($rowset);
        for($i=0;$i<$numi;$i++)
        {
            $row = $rowset[$i];
            $solrAdapter->indexCatalog($row['catalogGuid']);
            //$solrAdapter->deleteCatalogFromIndex($row['catalogGuid']);
            $message = "
                <div class='box box-info closeable'>
                CatalogGuid&nbsp;:&nbsp;<abbr>".$row['catalogGuid']."</abbr> data has been successfully indexed.
                </div>";
            echo $message.'<br>';
        }
         *
         */
    }
    function doindexckAction()
    {
        $this->_helper->viewRenderer->setNoRender(TRUE);
        $title = "<h4>HUKUMONLINE INDONESIA: <small>indexing kategori klinik</small></h4><hr/>";

        echo $title.'<br>';

    	$indexingEngine = Pandamp_Search::manager();
    	
		$query 			= "profile:klinik kategoriklinik:lt501649fa53cd2 status:99";
        $hits 			= $indexingEngine->find($query);
        $solrNumFound 	= count($hits->response->docs);
        
        //$sSolr = "id:(";
        for($ii=0;$ii<$solrNumFound;$ii++) {
        	if(isset($hits->response->docs[$ii]))
        	{
        		$row = $hits->response->docs[$ii];
        		$indexingEngine->indexCatalog($row->id);        		
	            $message = "
	                <div class='box box-info closeable'>
	                id&nbsp;:&nbsp;<abbr>".$row->id." - ".$row->title."</abbr> data has been successfully indexed.
	                </div>";
	            echo $message.'<br>';
        		//$sSolr .= $row->id .' OR ';
        	}        	
        }
        //$sSolr .= ')';
        //echo $sSolr;
    }
    
    function historyAction()
    {
    	$this->_helper->viewRenderer->setNoRender(TRUE);
    	
    	$request = $this->getRequest();
    	
    	$guid = $request->getParam('guid');
    	
    	$s = '';
    	
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
	    			
	    			
    				echo App_Model_Show_CatalogAttribute::show()->getCatalogAttributeValue($rowsetRelatedItem_ro->itemGuid,'fixedTitle').'<br>';
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
    			
    			
    			echo App_Model_Show_CatalogAttribute::show()->getCatalogAttributeValue($rowsetRelatedItem_r->itemGuid,'fixedTitle').'<br>';
    		}
    		
    	}
    	
    	echo App_Model_Show_CatalogAttribute::show()->getCatalogAttributeValue($guid,'fixedTitle').$s.'<br>';
    	
    	foreach ($rowsetRelatedItem as $row) {
    		if ($row->relateAs === "REPEAL") {
    			$status = "dicabut";
    		}
    		if ($row->relateAs === "AMEND") {
    			$status = "dirubah";
    		}
    		$title = App_Model_Show_CatalogAttribute::show()->getCatalogAttributeValue($row->itemGuid,'fixedTitle');
    		if ($row->relateAs === "AMEND") {
	    		echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='$row->itemGuid'>$title</a> [".$status."]<br>";
	    		$this->isroot($row->itemGuid);
// 	    		$this->getchild($row->itemGuid);
    		} 
    		else
    		{
    			echo "<a href='$row->itemGuid'>$title</a> [".$status."]<br>";
//     			$this->isroot($row->itemGuid);
    			$this->getchild($row->itemGuid);
    		}
    	} 
    }
    
    function getchild($guid,$level=0)
    {
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
    			$status = "dirubah";
    		}
    		$title = App_Model_Show_CatalogAttribute::show()->getCatalogAttributeValue($row->itemGuid,'fixedTitle');
    		if ($row->relateAs === "AMEND") {
    			echo $sTab."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='$row->itemGuid'>$title</a> [".$status."]<br>";
//     			echo $sTab."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
//     			$this->isroot($row->itemGuid);
    			$this->getchild($row->itemGuid,$level+1);
    		}
    		else
    		{
	    		echo $sTab."<a href='$row->itemGuid'>$title</a> [".$status."]<br>";
// 	    		echo $sTab;
// 	    		$this->isroot($row->itemGuid);
	    		$this->getchild($row->itemGuid,$level+1);
    		}
    	}
    		 
    }
    
    function isroot($guid)
    {
    	$where = "itemGuid='$guid' AND relateAs='ISROOT'";
    	$tblRelatedItem = new App_Model_Db_Table_RelatedItem();
    	$rowsetRelatedItem = $tblRelatedItem->fetchRow($where);
    	if ($rowsetRelatedItem) {
    		$title = App_Model_Show_CatalogAttribute::show()->getCatalogAttributeValue($rowsetRelatedItem->relatedGuid,'fixedTitle');
    		echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='$rowsetRelatedItem->relatedGuid'>$title</a>[mencabut sebagian]<br>";
    	}
    }
}
