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
    	set_time_limit(0);
    	ini_set('max_execution_time', '0'); 
    	
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
    	//$query="SELECT * FROM KutuCatalog WHERE profileGuid = 'ilb'";
    	//$query="SELECT * FROM KutuCatalog WHERE profileGuid IN ('ild','ile')";
    	//$query="SELECT * FROM KutuCatalog WHERE profileGuid IN ('partner','klinik','kategoriklinik','author')";
    	//$query="SELECT * FROM KutuCatalog WHERE profileGuid IN ('hot_issue_ile','ilb_english_rules','ild_english_rules','manufacturing_&_industry')";
    	$query="SELECT guid, profileGuid FROM KutuCatalog WHERE profileGuid = 'news'";
    	//$query="SELECT * FROM KutuCatalog WHERE profileGuid IN ('oil_and_gas','telecommunications_and_media')";
    	
    	$results = $db->query($query);
    	$rowset = $results->fetchAll(PDO::FETCH_OBJ);
    	$rowCount = count($rowset);
    	echo $rowCount.'<br><br>';
    	for($iCount=0;$iCount<$rowCount;$iCount++) {
    		$row = $rowset[$iCount];
    		$nextRow = $rowset[$iCount+1];
    		
    		//if ($iCount%500 == 0) {
    			$indexingEngine->indexCatalog($row->guid);  
    		//}
    		
    		$modelCatalog = App_Model_Show_Catalog::show()->getCatalogByGuid($row->guid);     
    		
            if ($modelCatalog['profileGuid'] == "klinik")
                $sTitle = App_Model_Show_CatalogAttribute::show()->getCatalogAttributeValue($row->guid, "fixedCommentTitle");
            else
                $sTitle = App_Model_Show_CatalogAttribute::show()->getCatalogAttributeValue($row->guid, "fixedTitle");
                
                
            $message = "
                <div class='box box-info closeable'>
                <b>[urutan:$iCount]</b>&nbsp;id&nbsp;:&nbsp;<abbr>".$row->guid." - ".$sTitle."</abbr> data has been successfully indexed.
                <b>[next guid: ".$nextRow->guid."]</b> - <i>".$modelCatalog['profileGuid']."</i></div>";
            echo $message.'<br>';
    		
            flush();
            sleep(30);
    	}
    }
    
    function indexAction()
    {
    	set_time_limit(0);
    	ini_set('max_execution_time', '0'); 
    	
    	$this->_helper->viewRenderer->setNoRender(TRUE);
    	$title = "<h4>HUKUMONLINE INDONESIA: <small>search</small></h4><hr/>";
    	
    	echo $title.'<br>';

    	$solrAdapter = Pandamp_Search_Engine::factory(array('host'=>'localhost', 'port'=>'8983','homedir'=>'/solr/core-catalog'));
    	$solrAdapter->reIndexCatalog();
    }
    
    function ibgAction()
    {
    	set_time_limit(0);
    	ini_set('max_execution_time', '0');
    	 
    	$this->_helper->viewRenderer->setNoRender(TRUE);
    	$title = "<h4>HUKUMONLINE INDONESIA: <small>search</small></h4><hr/>";
    	
    	echo $title.'<br>';
    	
    	$solr = new Apache_Solr_Service( '202.153.129.35', '8983', '/solr/core-catalog' );
    	if ( ! $solr->ping() ) {
    		echo 'Solr service not responding.';
    		exit;
    	}
    	
    	$indexingEngine = Pandamp_Search::manager();
    	
    	$db = Zend_Registry::get('db1');
    	 
    	$query="SELECT * FROM KutuCatalog WHERE guid IN ('lt5211a9dc6dda3','lt5211a831ab303','lt5211a6dbc1fd7','lt521182ee9af96','lt52117c0a1da99','lt5211b4d284ae1')";
    	
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
    		<b>[urutan:$iCount]</b>&nbsp;id&nbsp;:&nbsp;<abbr>".$row->guid." - ".$sTitle."</abbr> data has been successfully indexed.
                <b>[next guid: ".$nextRow->guid."]</b> - <i>".$modelCatalog['profileGuid']."</i></div>";
    		echo $message.'<br>';
    	
    		flush();
    		//sleep(30);
    	}
    	 
    }
    
    function deleteAction()
    {
    	$this->_helper->viewRenderer->setNoRender(TRUE);
    	$title = "<h4>HUKUMONLINE INDONESIA: <small>search</small></h4><hr/>";
    	 
    	echo $title.'<br>';
    	 
    	$solr = new Apache_Solr_Service( 'nihki:sirkulasi@202.153.129.35', '8983', '/solr/core-catalog' );
    	if ( ! $solr->ping() ) {
    		echo 'Solr service not responding.';
    		exit;
    	}
    	
    	$a = array('fl17956','fl1131');
    	 
    	foreach ($a as $c) {
	    	$indexingEngine = Pandamp_Search::manager();
	    	$indexingEngine->deleteCatalogFromIndex($c);
    	}
    }
}
