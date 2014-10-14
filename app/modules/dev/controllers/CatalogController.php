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
    	
    	$tblRelatedItem = new App_Model_Db_Table_RelatedItem();
    	
    	$where = "relatedGuid='$guid' AND relateAs='ISROOT'";
    	$rowsetRelatedItem = $tblRelatedItem->fetchRow($where);
    	if ($rowsetRelatedItem)
    		echo App_Model_Show_CatalogAttribute::show()->getCatalogAttributeValue($rowsetRelatedItem->itemGuid,'fixedTitle').'<br>';
    	
    	$where = "(relatedGuid='$guid' OR itemGuid='$guid') AND (relateAs IN ('REPEAL','AMEND'))";
    	$rowsetRelatedItem = $tblRelatedItem->fetchRow($where);
    	if (isset($rowsetRelatedItem->valueStringRelation))
    		$guid = $rowsetRelatedItem->valueStringRelation;    		
    	
    	$where = "relatedGuid='$guid' AND relateAs IN ('REPEAL','AMEND')";
    	$rowsetRelatedItem = $tblRelatedItem->fetchAll($where);
    	echo App_Model_Show_CatalogAttribute::show()->getCatalogAttributeValue($guid,'fixedTitle').'<br>';
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
	    		$this->getchild($row->itemGuid);
    		} 
    		else
    		{
    			echo "<a href='$row->itemGuid'>$title</a> [".$status."]<br>";
    			$this->getchild($row->itemGuid);
    		}
    	} 
    }
    
    function getchild($guid,$level=0)
    {
    	$tblRelatedItem = new App_Model_Db_Table_RelatedItem();
    	$where = "relatedGuid='$guid' AND relateAs IN ('REPEAL','AMEND')";
    	$rowsetRelatedItem = $tblRelatedItem->fetchAll($where);
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
    			$this->getchild($row->itemGuid,$level+1);
    		}
    		else
    		{
	    		echo $sTab."<a href='$row->itemGuid'>$title</a> [".$status."]<br>";
	    		$this->getchild($row->itemGuid,$level+1);
    		}
    	}
    		 
    }
}
