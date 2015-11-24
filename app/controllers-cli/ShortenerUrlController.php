<?php
require_once( 'Apache/Solr/Service.php' );
class ShortenerUrlController extends Application_Controller_Cli
{
	public function migrateAction()
	{
		$solr = &$this->_solr;
		$solr->setPath('/solr/coreshorturl');
			
		if ( ! $solr->ping() ) {
			echo "Solr coreshorturl service not responding.\n";
			exit;
		}
		else
		{
			echo "is ON\n";
		}

		echo "Start indexing\n";
		
		$query = "select b.id, b.url as link, b.createdate, b.remoteip, b.kopel from clicks as a,urls as b where a.urlid = b.id and b.url like '%www.hukumonline.com%' group by a.urlid, b.url order by b.createdate asc";
		$results = $this->db3->query($query);
		$rowsetAttr = $results->fetchAll(PDO::FETCH_OBJ);
		$rowCount = count($rowsetAttr);
		
		echo 'There are '.$rowCount." url(s)\n";
		
		$documents = array();
		
		for($i=0;$i<$rowCount;$i++)
		{
			$row = $rowsetAttr[$i];
			$data = [
				'id' => $row->id,
				'url' => $row->link,
				'createdate' => $row->createdate,
				'remoteip' => $row->remoteip,
				'kopel' => $row->kopel
			];
			
			$this->db3->insert('shorturls',$data);

			if (isset($rowsetAttr[$i+1])) {
				$nextRow = $rowsetAttr[$i+1];
				$n = "-next:[".$nextRow->id."]";
			}
			else
				$n = '';
				
				
			echo 'urutan: '.$i ." - ";
			
			$documents[$i] = $this->_createSolrDocument($row);
				
			if($i%1000 == 0)
			{
				try {
					$solr->addDocuments( $documents );
					$solr->commit();
					$documents = array();
				}
				catch (Exception $e)
				{
					echo "Error occured when processing record starting from number: ". ($i - 1000) . ' to '.$i."\n";
					throw new Zend_Exception($e->getMessage());
				}
			}
			
			$a[$i] = $row->id;
			
				
			echo "id:[".$row->id."][".$row->createdate."]".$n."\n";
				
			flush();
		}
		
		try {
			$solr->addDocuments( $documents );
			$solr->commit();
			$solr->optimize();
		}
		catch ( Exception $e ) {
			echo $e->getMessage();
		}
		
		file_put_contents(ROOT_DIR.DS.'data'.DS.'datashorturl', serialize($a));
		
		
		sleep(1);
		
		echo "Indexing completed\n";		
	}
	
	private function _createSolrDocument(&$row)
	{
		$part = new Apache_Solr_Document();
		$part->id = $row->id;
		$part->url = $row->link;
		$part->createdate = $this->getDateInSolrFormat($row->createdate);
		$part->remoteip = $row->remoteip;
		$part->kopel = (isset($row->kopel))? $row->kopel : '';
	
		return $part;
	}
	
	private function getDateInSolrFormat($date) {
		if($date=='0000-00-00 00:00:00' OR $date=='0000-00-00' OR $date=='' OR $date==NULL) {
			//return '0000-00-00T00:00:00Z';
			return '1999-12-31T23:59:59Z';
		}
		else
		{
			return date("Y-m-d\\TH:i:s\\Z",strtotime($date));
		}
	}
}