<?php
/**
 * module search for application
 * 
 * @author Nihki Prihadi <nihki@hukumonline.com>
 * @package Kutu
 * 
 */

require_once( 'Apache/Solr/Service.php' );

class Pandamp_Search_Adapter_Solrh extends Pandamp_Search_Adapter_Abstract  
{
	private $_index;
	private $_solr;
	private $_registry;
	private $_pdfExtractor;
	private $_wordExtractor;
	
	private $_conn;
	
	
	public function __construct($solrHost, $solrPort, $solrHomeDir)
	{
		$this->_solr = new Apache_Solr_Service( $solrHost, $solrPort, $solrHomeDir );
		
		$this->_conn = Zend_Registry::get('db4');
		$this->_conn->setFetchMode(Zend_Db::FETCH_OBJ);
	}
	public function indexCatalog($guid)
	{
		$solr = &$this->_solr;
		
		$tbl = new App_Model_Db_Table_Url();
		
		$rowset = $tbl->find($guid);
		if(count($rowset))
		{
			$row = $rowset->current();
			
			$documents = array();
			
			$documents[] = $this->_createSolrDocument($row);
			
			try {
				$solr->addDocuments( $documents );
				$solr->commit();
			}
			catch ( Exception $e ) {
				throw new Zend_Exception($e->getMessage());
			}
		}
	}
	public function reIndexCatalog()
	{
		gc_enable();
		$this->emptyIndex();
		
		$time_start = microtime(true);
		
		$solr = &$this->_solr;
		
		$formater = new Pandamp_Lib_Formater();
		
		$query="SELECT * FROM urls";
		$results = $this->_conn->query($query);
		
		$rowset = $results->fetchAll(PDO::FETCH_OBJ);
		  
		$documents = array();
		$rowCount = count($rowset);
		for($iCount=0;$iCount<$rowCount;$iCount++)
		{
			$row = $rowset[$iCount];
			$nextRow = $rowset[$iCount+1];
			
			echo '<li><span style="font:11px verdana,arial,helvetica,sans-serif;">[urutan:'.$iCount.']&nbsp;indexing:<font color=green>'.$row->kopel.'</font>[current id: '.$row->id.'  '.'next guid: '.$nextRow->id.'][createdDate:<i>'.$formater->get_date($row->createdate).'</i>]</span></li>';
			
		  	$documents[] = $this->_createSolrDocument($row);
		  	
  			if($iCount%500 == 0)
		  	{
			  	try 
			  	{
					$solr->addDocuments( $documents );
					$solr->commit();
//					$solr->optimize();
					$documents = array();
				}
				catch ( Exception $e ) 
				{
//					echo "Error occured when processing record starting from number: ". ($iCount - 1000) . ' to '.$iCount.' '.$row->guid;
					//$log->err($e->getMessage());
					throw new Zend_Exception($e->getMessage());
//					echo $e->getMessage().'<br>';

				}
		  	}
		  	flush();
		}
		
		echo '</ul></div></td></tr></table>';
		  
		try {
			$solr->addDocuments( $documents );
			$solr->commit();
//			$solr->optimize();
		}
		catch ( Exception $e ) {
			$log->err($e->getMessage());
			throw new Zend_Exception($e->getMessage());
//			echo $e->getMessage().'<br>';
		}
		
		$time_end = microtime(true);
		$time = $time_end - $time_start;
		
//		echo'<br>WAKTU EKSEKUSI: '. $time;
//		$log->info("WAKTU EKSEKUSI: ". $time." indexing catalog ".$iCount." dari ".$rowCount ." ".$username);
		
		echo'<br><br><span style="font:11px verdana,arial,helvetica,sans-serif;color:#00FF00">WAKTU EKSEKUSI: '. $time.'<br>indexing catalog '.$iCount.' dari '.$rowCount.'</span>';
		
		// log to assetSetting
		$tblAssetSetting = new Core_Models_Db_Table_AssetSetting();
		$rowAsset = $tblAssetSetting->fetchRow("application='INDEX CATALOG'");
		if ($rowAsset)
		{
			$rowAsset->valueText = "Update $rowCount indexing-urls at ".date("Y-m-d H:i:s").$username;
			$rowAsset->valueInt = $rowAsset->valueInt + 1;			
		}
		else 
		{
			$gman = new Pandamp_Core_Guid();
			$catalogGuid = $gman->generateGuid();
			$rowAsset = $tblAssetSetting->fetchNew();	
			$rowAsset->guid = $catalogGuid;
			$rowAsset->application = "INDEX URLS";
			$rowAsset->part = "KUTU";
			$rowAsset->valueType = "INDEX";
			$rowAsset->valueInt = 0;
			$rowAsset->valueText = $rowCount." Indexing urls at ".date("Y-m-d H:i:s").$username;
		}
		$rowAsset->save();
	}
	private function _createSolrDocument(&$row)
	{
		$part = new Apache_Solr_Document();
	  	$part->id = $row->id;
	  	$part->url = $row->url;
	  	$part->createdate = $this->_translateMySqlDateToSolrDate($row->createdate);
	  	$part->remoteip = $row->remoteip;
	  	$part->kopel = (isset($row->kopel))? $row->kopel : '';
	  	
	  	return $part;
	}
	
		
	public function _translateMySqlDateToSolrDate($mysqlDate)
	{
//		if(Zend_Date::isDate($mysqlDate, "yyyy-MM-dd HH:mm:ss"))
		if(true)
		{
			$aDateTime = explode(' ', $mysqlDate);
			if(!empty($aDateTime[0]) && strlen($aDateTime[0])==10)
				$aDateTime[0] .= 'T';
			else 
				$aDateTime[0] = '0000-00-00T';
			//if(isset($aDateTime[1]) && !empty($aDateTime[1]))
			if (false)
				$aDateTime[1] .= 'Z';
			else 
				$aDateTime[1] = '00:00:00Z';
				
			$solrDate = $aDateTime[0].$aDateTime[1];
			//echo '<br>'.$solrDate;
			return $solrDate;
		}
		else 
		{
			return '0000-00-00T00:00:00Z';
		}
		
	}
	
	public function translateSolrDate($date)
	{
		if (empty($date)) return;
		
		$aDateTime = str_replace(array('T','Z'),' ',$date);
		$mysqlDate = trim($aDateTime);
		
		return $mysqlDate;
	}
	
	public function deleteCatalogFromIndex($catalogGuid)
	{
		$solr = &$this->_solr;
		$solr->deleteById($catalogGuid);
		$solr->commit();
	}
	
	public function optimizeIndex()
	{
		$this->_solr->optimize();
	}
	
	public function emptyIndex()
	{
		$solr = &$this->_solr;
//		$solr->deleteByQuery('profile:klinik'); //deletes ALL documents - be careful :)
		$solr->deleteByQuery('*:*'); //deletes ALL documents - be careful :)
   		$solr->commit();
	}
	
	public function ping()
	{
		$solr = &$this->_solr;
		if ( $solr->ping() ) {
			return true;
		}
		
		return false;
	}
	
	/*public function find($query,$start = 0 ,$end = 2000)
	{
            $solr = &$this->_solr;
            $querySolr = $query;

            $aParams = array(
                'hl'=>'true',
                'hl.simple.pre' =>'<mark>',
                'hl.simple.post' =>'</mark>',
                'hl.fl' =>'commentQuestion,kategori,description,title,subTitle',
                'fl'=>'*,score',
                'facet'=>'true',
                'facet.field'=>array('profile','kategoriklinik','regulationType','sumber'),
                'facet.sort'=>'true',
                'facet.method'=>'enum',
                'facet.limit'=>'-1',
                'qt'=>'spellCheckCompRH',
                'spellcheck'=>'true',
                'spellcheck.collate'=>'true');

            return $solr->search( $querySolr,$start, $end, $aParams);
	}*/
	
	public function find($query,$start = 0 ,$end = 2000,$sortField=null)
	{
		$solr = &$this->_solr;
		$querySolr = $query;
		$aParams = array(
				'sort'=>$sortField,
				'spellcheck'=>'true',
				'qt'=>'spellCheckCompRH',
				'spellcheck.q'=>$querySolr,
				'spellcheck.collate'=>'true');
	
		return $solr->search( $querySolr,$start, $end, $aParams);
	}
	
	
	
	public function findAndSort($query, $start=0, $limit=20, $sortField)
	{
            $solr = &$this->_solr;
            $querySolr = $query;
            $s = $sortField;
            $aParams = array('sort'=>$s, 'q.op'=>'OR','qt'=>'spellCheckCompRH', 'spellcheck.q'=>$querySolr, 'spellcheck'=>'true','spellcheck.collate'=>'true');
//            $aParams = array('sort'=>$s, 'q.op'=>'OR');
            //array('qt'=>'spellCheckCompRH', 'spellcheck'=>'true','spellcheck.collate'=>'true', 'sort'=>$s);
            //echo $querySolr;
            //die;
            return $solr->searchByPost( $querySolr,$start, $limit, $aParams);
	}
	
	function clean_string_input($input)
	{
	    $interim = strip_tags($input);
	
	    if(get_magic_quotes_gpc())
	    {
	        $interim=stripslashes($interim);
	    }
	
	    // now check for pure ASCII input
	    // special characters that might appear here:
	    //   96: opening single quote (not strictly illegal, but substitute anyway)
	    //   145: opening single quote
	    //   146: closing single quote
	    //   147: opening double quote
	    //   148: closing double quote
	    //   133: ellipsis (...)
	    //   163: pound sign (this is safe, so no substitution required)
	    // these can be substituted for safe equivalents
	    $result = '';
	    $countInterim = strlen($interim);
	    for ($i=0; $i<$countInterim; $i++)
	    {
	        $char = $interim{$i};
	        $asciivalue = ord($char);
	        if ($asciivalue == 96)
	        {
	            $result .= '\\';
	        }
	        else if (($asciivalue > 31 && $asciivalue < 127) ||
	                 ($asciivalue == 163) || // pound sign
	                 ($asciivalue == 10) || // lf
	                 ($asciivalue == 13)) // cr
	        {
	            // it's already safe ASCII
	            $result .= $char;
	        }
	        else if ($asciivalue == 145) // opening single quote
	        {
	            $result .= '\\';
	        }
	        else if ($asciivalue == 146) // closing single quote
	        {
	            $result .= "'";
	        }
	        else if ($asciivalue == 147) // opening double quote
	        {
	            $result .= '"';
	        }
	        else if ($asciivalue == 148) // closing double quote
	        {
	            $result .= '"';
	        }
	        else if ($asciivalue == 133) // ellipsis
	        {
	            $result .= '...';
	        }
	    }
	
	    return $result;
	}
}