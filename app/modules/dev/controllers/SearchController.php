<?php

/**
 * Description of SearchController
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Dev_SearchController extends Zend_Controller_Action
{
    function  preDispatch()
    {
        $this->_helper->layout->setLayout('layout-customer-migration');
    }
    function solrAction()
    {
        $this->_helper->viewRenderer->setNoRender(TRUE);
        $title = "<h4>HUKUMONLINE INDONESIA: <small>search</small></h4><hr/>";

        echo $title.'<br>';

        $solr = new Apache_Solr_Service( 'localhost', '8983', '/solr/core0' );
        if ( ! $solr->ping() ) {
            echo 'Solr service not responding.';
            exit;
        }
        else
        {
            echo 'is ON<br>';
        }

        $start = 0;
        $limit = 50;
        
        //$querySolr = 'id:(hol10111 hol10112)';
        $rowset = App_Model_Show_CatalogFolder::show()->getCatalogGuidByFolderGuid("fb16", $start, $limit);

        $numi = count($rowset);
        $querySolr = "id:(";
        for($i=0;$i<$numi;$i++)
        {
            $row = $rowset[$i];
            $querySolr .= $row['guid'] .' ';
        }
        $querySolr .= ')';

        //$aParams = array('qt'=>'spellCheckCompRH', 'spellcheck.q'=>$querySolr, 'spellcheck'=>'true','spellcheck.collate'=>'true');
        $aParams=array('q.op'=>'OR');
        //$response = $solr->search( $querySolr,0, 10000, $aParams);
        $response = $solr->searchByPost( $querySolr,$start, $limit, $aParams);
        
        if ( $response->getHttpStatus() == 200 ) {
            echo '<pre>';
            print_r( $response->getRawResponse() );
            echo '</pre>';

            if ( $response->response->numFound > 0 ) {
                if(isset($response->spellcheck->suggestions->collation))
                    echo '<br>Did you mean: <strong>'.$response->spellcheck->suggestions->collation.'</strong>';
                    echo "found: ". $response->response->numFound,"<br />";

                $i=0;
                foreach ( $response->response->docs as $doc ) {
                    if(!isset($doc->subTitle))
                        $subTitle = '';
                    else
                        $subTitle = $doc->subTitle;

                    echo $i++." $doc->title <br /> $subTitle <br>";
                }

                echo '<br />';
            }
            else
            {
                if(isset($response->spellcheck->suggestions->collation))
                {
                    echo "No match. Maybe what you want to search is: ". $response->spellcheck->suggestions->collation;
                }
            }
        }
        else {
            echo 'test';
            echo $response->getHttpStatusMessage();
        }
    }
    function solrenAction()
    {
        $this->_helper->viewRenderer->setNoRender(TRUE);
        $title = "<h4>HUKUMONLINE ENGLISH: <small>search</small></h4><hr/>";

        echo $title.'<br>';

        $solr = new Apache_Solr_Service( 'localhost', '8983', '/solr/core1' );
        if ( ! $solr->ping() ) {
            echo 'Solr service not responding.';
            exit;
        }

        $indexingEngine = Pandamp_Search::manager();
        
    	$db = Zend_Registry::get('db3');
    	
    	//$query="SELECT * FROM KutuCatalog WHERE profileGuid = 'article'";
    	//$query="SELECT * FROM KutuCatalog WHERE profileGuid = 'consumer_goods'";
    	//$query="SELECT * FROM KutuCatalog WHERE profileGuid IN ('executive_alert','executive_summary','financial_services','general_corporate')";
    	//$query="SELECT * FROM KutuCatalog WHERE profileGuid IN ('hotile','hot_issue_ilb','hot_issue_ild','hot_news')";
    	$query="SELECT * FROM KutuCatalog WHERE profileGuid IN ('ilb','ild','ile')";
    	//$query="SELECT * FROM KutuCatalog WHERE profileGuid IN ('manufacturing_&_industry','news','oil_and_gas','telecommunications_and_media')";
    	
    	$results = $db->query($query);
    	$rowset = $results->fetchAll(PDO::FETCH_OBJ);
    	$rowCount = count($rowset);
    	echo $rowCount.'<br><br>';
    	for($iCount=0;$iCount<$rowCount;$iCount++) {
    		$row = $rowset[$iCount];
    		$nextRow = $rowset[$iCount+1];
    		$indexingEngine->indexCatalog($row->guid);  
    		$modelCatalog = App_Model_Show_Catalog::show()->getCatalogByGuid($row->guid);     
    		
            if ($modelCatalog['profileGuid'] == "klinik")
                $sTitle = App_Model_Show_CatalogAttribute::show()->getCatalogAttributeValue($row->guid, "fixedCommentTitle");
            else
                $sTitle = App_Model_Show_CatalogAttribute::show()->getCatalogAttributeValue($row->guid, "fixedTitle");
                
                
            $message = "
                <div class='box box-info closeable'>
                [urutan:$iCount]&nbsp;id&nbsp;:&nbsp;<abbr>".$row->guid." - ".$sTitle."</abbr> data has been successfully indexed.
                [next guid: ".$nextRow->guid."] - <i>".$modelCatalog['profileGuid']."</i></div>";
            echo $message.'<br>';
    		
            flush();
    	}
    }
}
