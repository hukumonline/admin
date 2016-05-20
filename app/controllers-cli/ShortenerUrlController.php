<?php
require_once( 'Apache/Solr/Service.php' );
class ShortenerUrlController extends Application_Controller_Cli
{
	public function reindexAction()
	{
		$request = $this->getRequest();
		$query = $request->getParam('q');
		$path = $request->getParam('path');
		
		if (empty($path)) {
			echo "Define solr path first.\n";
			exit;
		}
		else
		{
			echo "path:".$path."\n";
		}
		
		$solr = &$this->_solr;
		
		$solr->setPath('/solr/'.$path);
		
		if ( ! $solr->ping() ) {
			echo "Solr service not responding.\n";
			exit;
		}
		else
		{
			echo "is ON\n";
		}
		
		echo "Start indexing\n";
		
		$db = $this->getDbHandler('sh');
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
		$select = $db->select();
		$select->from('shorturls', '*');
		
		if (isset($query) && !empty($query))
		{
			$select->where($query);
		}
		
		$select->order('createdate DESC');
		
		//$sql = $select->__toString();
		
		$rowsFound = $db->fetchAll($select);
		
		echo 'There are '.count($rowsFound)." catalog(s)\n";
		
		$documents = array();
		
		$rowCount = count($rowsFound);
		for($iCount=0;$iCount<$rowCount;$iCount++) {
			$row = $rowsFound[$iCount];
				
			if (isset($rowsFound[$iCount+1])) {
				$nextRow = $rowsFound[$iCount+1];
				$n = "-next:[".$nextRow->id."]";
			}
			else
				$n = '';
				
				
			echo 'urutan: '.$iCount ." - ";
				
			$documents[$iCount] = $this->_createSolrDocument($row);
				
			if($iCount%1000 == 0)
			{
				try {
					$solr->addDocuments( $documents );
					$solr->commit();
					$documents = array();
				}
				catch (Exception $e)
				{
					echo "Error occured when processing record starting from number: ". ($iCount - 1000) . ' to '.$iCount."\n";
					throw new Zend_Exception($e->getMessage());
				}
			}
				
			echo "guid:[".$row->id."][".$row->createdate."]".$n."\n";
				
			flush();
		}
		
		//
		//
		// Load the documents into the index
		//
		try {
			$solr->addDocuments( $documents );
			$solr->commit();
			$solr->optimize();
		}
		catch ( Exception $e ) {
			echo $e->getMessage();
		}
		
		
		sleep(1);
		
		echo "Indexing completed\n";
	}
	
	public function shorturlAction()
	{
		$request = $this->getRequest();
		
		$catalogGuid = $request->getParam('guid');
		$folderGuid = $request->getParam('folderGuid');
		$lang = $request->getParam('lang','id');
		$ip = '175.103.48.153';
		$kopel = '00001';
		
		$catalog = $this->getCatalog($catalogGuid, ['profileGuid','shortTitle'],$lang);
		
		if ($catalog->profileGuid == 'kutu_doc') {
			echo "dokumen no need shorturl.\n";
			exit;
		}
		
		$web = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application-cli.ini','web');
		if ($lang == 'id') {
			if ($catalog->profileGuid == 'klinik')
				$url_content = $web->url->base.'/klinik/detail/'.$catalogGuid.'/'.$catalog->shortTitle;
			elseif (in_array($catalog->profileGuid, array('kutu_peraturan','kutu_putusan','kutu_peraturan_kolonial','kutu_rancangan_peraturan')))
				$url_content = $web->url->base.'/pusatdata/detail/'.$catalogGuid.'/'.$this->getLabelNode($folderGuid,$lang).'/'.$folderGuid.'/'.$catalog->shortTitle;
			else
				$url_content = $web->url->base.'/berita/baca/'.$catalogGuid.'/'.$catalog->shortTitle;
		}
		elseif ($lang == 'en')
		{
			$url_content = $web->en->url->base.'/pages/'.$catalogGuid.'/'.$catalog->shortTitle;
		}
		
		$q = "url:\"".$url_content."\"";
		
		$data = array('url' => $url_content,
				'createdate' => date("Y-m-d h:i:s"),
				'remoteip' => $ip,
				'kopel' => $kopel);
		
		
		$db = $this->getDbHandler('sh');
		$db->setFetchMode(Zend_Db::FETCH_OBJ);	
		$solr = $this->getSolrService('sh');
		
		if ( ! $solr->ping() ) {
			echo "Solr coreshorturl service not responding.\n";
			exit;
		}
		else
		{
			echo "is ON\n";
		}
		
		$hits = $solr->search($q,0,1,['fl'=>'id']);
		if (isset($hits->response->docs[0])) {
			$row = $hits->response->docs[0];
			$hid = $row->id;
			$this->debug($data);
			//$db->update('shorturls',$data,"id=$hid");
		}
		else
		{
			$recoveredData = file_get_contents(ROOT_DIR.DS.'data'.DS.'datashorturl.txt');
			$recoveredArray = unserialize($recoveredData);
			$start = array_shift($recoveredArray);
			foreach($recoveredArray as $v){
				if ($start + 1 != $v) {
					$missing = $start + 1;
					break;
				}
				$start = $v;
			}
			
			if (isset($missing)) {
				$numId = $missing;
				
				//$recoveredArray[] = $missing;
				//sort($recoveredArray);
				//file_put_contents(ROOT_DIR.DS.'data'.DS.'datashorturl.txt', serialize($recoveredArray));
			}
			else 
			{
				$sm = $db->select();
				$sm->from('shorturls',array(new Zend_Db_Expr('MAX(id)+1 as maxid')));
				$rowMax = $db->fetchRow($sm);
				$numId = $rowMax->maxid;
			}
			
			$data['id'] = $numId;
			$this->debug($data);
			//$insert = $db->insert('shorturls', $data);
				
			//$hid = $db->lastInsertId('shorturls', 'id');
		}
		
		
	}
	
	private function getCatalog($catalogGuid, $field, $lang)
	{
		$db = $this->getDbHandler($lang);
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
	
		$sql = $db->select();
		$sql->from('KutuCatalog', $field);
		$sql->where('guid=?',$catalogGuid);
		$sql->where('status!=?',-1); // not deleted
		$row = $db->fetchRow($sql);
	
		return ($row) ? $row : '';
	}
	
	private function getFolderByGuid($folderGuid, $lang)
	{
		$db = $this->getDbHandler($lang);
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
	
		$sql = $db->select();
		$sql->from('KutuFolder', '*');
		$sql->where('guid=?',$folderGuid);
		$row = $db->fetchRow($sql);
	
		return ($row) ? $row : '';
	}
	
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
		
		file_put_contents(ROOT_DIR.DS.'data'.DS.'datashorturl.txt', serialize($a));
		
		
		sleep(1);
		
		echo "Indexing completed\n";		
	}
	
	private function _createSolrDocument(&$row)
	{
		$part = new Apache_Solr_Document();
		$part->id = $row->id;
		$part->url = $row->url;
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
	
	private function getDbHandler($lang)
	{
		$multidb = Pandamp_Application::getResource('multidb');
		$multidb->init();
	
		$this->db = $multidb->getDb('db1');		//id
		$this->db4 = $multidb->getDb('db4');	//en
		$this->db3 = $multidb->getDb('db3');	//shortUrl
	
		if ($lang == "id")
			return $this->db;
		elseif ($lang == "en")
			return $this->db4;
		else
			return $this->db3;
	}
	
	private function getSolrService($lang)
	{
		$indexing = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application-cli.ini','indexing');
	
		$host = $indexing->solr->write->host;
		$port = $indexing->solr->write->port;
	
		if ($lang == "id")
			$path = $indexing->solr->write->dir1;
		elseif ($lang == "en")
		$path = $indexing->solr->write->dir2;
		else
			$path = $indexing->solr->write->dir3;
	
	
		$solr = new Apache_Solr_Service( $host, $port, $path );
	
		return $solr;
	}
	
	private function debug($data, $q=true)
	{
		echo '<pre>';
		print_r($data);
		echo '</pre>';
	
		echo "\n";
	
		if ($q) {
			die;
		}
	}
	
	private function getLabelNode($folderGuid, $lang)
	{
		$rowFolder = $this->getFolderByGuid($folderGuid,$lang);
		if ($rowFolder) {
			$path = explode("/",$rowFolder->path);
			$rpath = $path[0];
			$rowFolder1 = $this->getFolderByGuid($rpath,$lang);
			if ($rowFolder1) {
				$rowFolder2 = $this->getFolderByGuid($rowFolder1->parentGuid,$lang);
				if ($rowFolder2) {
					if ($rowFolder2->title == "Peraturan") {
						return "nprt";
					}
					elseif ($rowFolder2->title == "Putusan") {
						return "npts";
					}
					else
					{
						return "node";
					}
				}
				else
				{
					return "node";
				}
			}
			else
			{
				return "node";
			}
		}
		else
		{
			return "node";
		}
	
	}
}