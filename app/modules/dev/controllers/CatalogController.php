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
}
