<?php
/**
 * /usr/local/zend/bin/php cli.php solr assetsetting arg=test ar=rep
 */

require_once( 'Apache/Solr/Service.php' );

class SolrController extends Application_Controller_Cli
{
	private $_pdfExtractor = 'pdftotext';
	private $_wordExtractor = 'antiword';
	//private $_imageUrl = 'http://images.hukumonline.dev/frontend';
	
	public function emptyIndexAction()
	{
		$request = $this->getRequest();
		
		$path = $request->getParam('path');
		
		$solr = &$this->_solr;
		$solr->setPath('/solr/'.$path);
		$solr->deleteByQuery('*:*');
		$solr->commit();
		
		echo "Empty documents succeded\n";
	}
	
	public function indexCatalogAction()
	{
		$request = $this->getRequest();
		
		$catalogGuid = $request->getParam('guid');
		$path = $request->getParam('path');
		
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
		
		echo "Start indexing guid:$catalogGuid\n";
		
		$db = $this->db;
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
		$select = $db->select();
		$select->from('KutuCatalog', '*');
		$select->where("guid='$catalogGuid'");
		
		$row = $db->fetchRow($select);
	
		if(isset($row) && ! empty($row))
		{
			$documents = array();
			
			$documents[] = $this->_createSolrDocument($row);
			
			try {
				$solr->addDocuments( $documents );
				$solr->commit();
				//$solr->optimize();
			}
			catch ( Exception $e ) {
				throw new Zend_Exception($e->getMessage());
			}
		}
		
		echo "Indexing completed\n";
	}
	
	public function deleteCatalogFromIndexAction()
	{
		$request = $this->getRequest();
		
		$path = $request->getParam('path');
		$catalogGuid = $request->getParam('guid');
		
		$solr = &$this->_solr;
		$solr->setPath('/solr/'.$path);
		$solr->deleteById($catalogGuid);
		$solr->commit();
		
		echo "document:$catalogGuid was successfully deleted\n";
	}
	
	public function reindexAction()
	{
		$request = $this->getRequest();
		
		$lang = $request->getParam('lang','id');
		$path = $request->getParam('path');
		$query = $request->getParam('q');
		
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
		
		$db = $this->db;
		
		if ($lang == 'en') {
			$db = Zend_Registry::get('db4');
		}
		
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
		$select = $db->select();
		$select->from('KutuCatalog', '*');
		
		if (isset($query) && !empty($query))
		{
			$select->where($query);
		}
		
		$select->order('createdDate ASC');
		
		//$sql = $select->__toString();
		
		$rowsFound = $db->fetchAll($select);
		
		echo 'There are '.count($rowsFound)." catalog(s)\n";
		
		$documents = array();
		
		$rowCount = count($rowsFound);
		for($iCount=0;$iCount<$rowCount;$iCount++) {
			$row = $rowsFound[$iCount];
			
			if (isset($rowsFound[$iCount+1])) {
				$nextRow = $rowsFound[$iCount+1];
				$n = "-next:[".$nextRow->guid."]";
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
			
			echo "guid:[".$row->guid."][".$row->createdDate."]".$n."\n";
			
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
	
	public function shortenerAction()
	{
		$request = $this->getRequest();
		
		$url = $request->getParam('url');
		$ip = $request->getParam('ip');
		$kopel = $request->getParam('kopel');
		$host = $request->getParam('host');
		$port = $request->getParam('port');
		$dir = $request->getParam('dir');
		
		$hukumn = new Pandamp_Search_Adapter_Solrh($host, $port, $dir);
		
		echo "Start shortener uri:$url\n";
		
		$data = array('url' => $url,
				'createdate' => date("Y-m-d h:i:s"),
				'remoteip' => $ip,
				'kopel' => $kopel);
		
		
		$db = $this->db3;
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
		
		$hits = $hukumn->find("url:\"".$url."\"",0,1);
		if (isset($hits->response->docs[0]))
		{
			$row = $hits->response->docs[0];
			$hid = $row->id;
				
			$db->update('urls',$data,"id=$hid");
		}
		else
		{
			$insert = $db->insert('urls', $data);
				
			$hid = $db->lastInsertId('urls', 'id');
		}
		
		
		$select = $db->select();
		$select->from('urls', '*');
		$select->where("url='$url'");
		
		$row = $db->fetchRow($select);
		
		
		
		
		sleep(1);
		
		echo "Indexing completed\n";
	}
	
	public function cekAction()
	{
		$solr = &$this->_solr;
		
		$solr->setPath('/solr/corehol');
		
		if ( ! $solr->ping() ) {
			echo "Solr service not responding.\n";
			exit;
		}
		else
		{
			echo "is ON\n";
		}
		
		echo "Start checking\n";
		
		$db = $this->db;
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
		$select = $db->select();
		$select->from('KutuCatalog', '*');
		$select->order('createdDate ASC');
		
		//$sql = $select->__toString();
		
		$rowsFound = $db->fetchAll($select);
		
		$documents = array();
		
		$rowCount = count($rowsFound);
		for($iCount=0;$iCount<$rowCount;$iCount++) {
			$row = $rowsFound[$iCount];
			
			$q = $solr->search("id:$row->guid");
			if (!$q) {
				echo $row->guid."\n";
			}
			
		}
		
	}
	
	public function assetsettingAction()
	{
		$request = $this->getRequest();
		
		$query = $request->getParam('q');
		
		$solr = &$this->_solr;
		$solr->setPath('/solr/assetsetting');
		
		if ( ! $solr->ping() ) {
			echo "Solr service not responding.\n";
			exit;
		}
		else
		{
			echo "is ON\n";
		}
		
		echo "Start indexing\n";
		
		$db = $this->db;
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
		$select = $db->select();
		$select->from('KutuAssetSetting', '*');
		
		if (isset($query) && !empty($query))
		{
			$select->where($query);
		}
		
		$select->order('valueDatetime ASC');
		
		//$sql = $select->__toString();
		
		$rowsFound = $db->fetchAll($select);
		
		echo 'There are '.count($rowsFound)." asset(s)\n";
		
		$documents = array();
		
		$rowCount = count($rowsFound);
		for($iCount=0;$iCount<$rowCount;$iCount++) {
			$row = $rowsFound[$iCount];
			
			echo 'urutan: '.$iCount ." - ";
			
			$part = new Apache_Solr_Document();
			$part->id = $row->id;
			$part->guid = $row->guid;
			$part->contentType = $row->contentType;
			$part->application = $row->application;
			$part->part = $row->part;
			$part->detail = $row->detail;
			$part->valueType = $row->valueType;
			$part->valueInt = $row->valueInt;
			$part->valueFloat = $row->valueFloat;
			$part->valueText = $row->valueText;
			$part->valueDatetime = $this->getDateInSolrFormat($row->valueDatetime);
			
			$documents[$iCount] = $part;
			
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
						
			
			echo "id:[".$row->id."][".$row->valueDatetime."]\n";
				
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
	
	public function folderAction()
	{
		$request = $this->getRequest();
		
		$query = $request->getParam('q');
		
		$solr = &$this->_solr;
		$solr->setPath('/solr/folder');
		
		if ( ! $solr->ping() ) {
			echo "Solr service not responding.\n";
			exit;
		}
		else
		{
			echo "is ON\n";
		}
		
		echo "Start indexing\n";
		
		$db = $this->db;
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
		$select = $db->select();
		$select->from('KutuFolder', '*');
		
		if (isset($query) && !empty($query))
		{
			$select->where($query);
		}
		
		$select->order('guid ASC');
		
		//$sql = $select->__toString();
		
		$rowsFound = $db->fetchAll($select);
		
		echo 'There are '.count($rowsFound)." asset(s)\n";
		
		$documents = array();
		
		$rowCount = count($rowsFound);
		for($iCount=0;$iCount<$rowCount;$iCount++) {
			$row = $rowsFound[$iCount];
				
			echo 'urutan: '.$iCount ." - ";
				
			$part = new Apache_Solr_Document();
			$part->id = $row->guid;
			$part->title = $row->title;
			$part->description = $row->description;
			$part->parentGuid = $row->parentGuid;
			$part->path = $row->path;
			$part->type = $row->type;
			$part->viewOrder = $row->viewOrder;
			$part->cmsParams = $row->cmsParams;
				
			$documents[$iCount] = $part;
				
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
		
				
			echo "id:[".$row->guid."][".$row->title."]\n";
		
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
	
	private function _createShortenerDocument(&$row)
	{
		$part = new Apache_Solr_Document();
		$part->id = $row->id;
		$part->url = $row->url;
		$part->createdate = $this->getDateInSolrFormat($row->createdate);
		$part->remoteip = $row->remoteip;
		$part->kopel = (isset($row->kopel))? $row->kopel : '';
		
		return $part;
	}
	
	private function _createSolrDocument(&$row)
	{
		$part = new Apache_Solr_Document();
		$part->id = $row->guid;
		$part->shortTitle = trim(strip_tags($row->shortTitle));
		$part->profile = $row->profileGuid;
		$part->publishedDate = $this->getDateInSolrFormat($row->publishedDate);
		$part->expiredDate = $this->getDateInSolrFormat($row->expiredDate);
		$part->createdBy = $row->createdBy;
		$part->createdDate = $this->getDateInSolrFormat($row->createdDate);
		$part->modifiedBy = $row->modifiedBy;
		$part->modifiedDate = $this->getDateInSolrFormat($row->modifiedDate);
		$part->deletedBy = $row->deletedBy;
		$part->deletedDate = $this->getDateInSolrFormat($row->deletedDate);
		$part->price = (!$row->price==null)? $row->price : 0;
		$part->sticky = (!$row->sticky==null)? $row->sticky : 0;
		$part->status = (!$row->status==null)? $row->status : 0;
		
		if ($row->profileGuid !== "kutu_doc") {
		$part->desktop = $this->getCountCatalog($row->guid, $row->profileGuid, 'desktop');
		$part->mobile = $this->getCountCatalog($row->guid, $row->profileGuid, 'mobile');
		
		$related = null;
		$cf = array();
		$queryCF="SELECT * FROM KutuCatalogFolder where catalogGuid='".$row->guid."'";
		$resultsCF = $this->db->query($queryCF);
		$rowsetAttrCF = $resultsCF->fetchAll(PDO::FETCH_OBJ);
		$rowCountCF = count($rowsetAttrCF);
		for($x=0;$x<$rowCountCF;$x++)
		{
			$rowAttrCF = $rowsetAttrCF[$x];
			$cf[] = $rowAttrCF->folderGuid;
		}
		
		$part->kategoriId = $cf;
		
		$web = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application-cli.ini','web');
		
		//$part->shortenerUrl = $this->generateShortener($row->shortTitle);
		
		if (in_array($row->profileGuid, array('article','klinik','partner'))) {
			$part->fileImage = $this->fileImageUrl($row->guid);
		}
		
		if ($row->profileGuid == "klinik") {
			$rk = $this->getRelated($row->guid,"RELATED_Clinic",false,"relatedGuid desc");
			if ($rk)
			{
				$rowClinic = array();
				for($v=0;$v<count($rk);$v++)
				{
					$rowRelatedClinic = $rk[$v];
					$catalogClinic = $this->getCatalog($rowRelatedClinic->itemGuid, ['shortTitle']);
					$rowClinic[$v]['title'] = strip_tags(trim($this->getCatalogAttribute($rowRelatedClinic->itemGuid, 'fixedTitle')));
					$rowClinic[$v]['pageUrl'] = $web->url->base."/klinik/detail/".$rowRelatedClinic->itemGuid."/".$catalogClinic->shortTitle;
				}
				
				$related = Zend_Json::encode($rowClinic);
			}
		}
		if ($row->profileGuid == "article") {
			$rk = $this->getRelated($row->guid,"RELATED_OTHER",false,"relatedGuid desc");
			if ($rk)
			{
				$rowClinic = array();
				for($v=0;$v<count($rk);$v++)
				{
					$rowRelatedClinic = $rk[$v];
					$catalogClinic = $this->getCatalog($rowRelatedClinic->itemGuid, ['shortTitle']);
					$rowClinic[$v]['title'] = strip_tags(trim($this->getCatalogAttribute($rowRelatedClinic->itemGuid, 'fixedTitle')));
					$rowClinic[$v]['pageUrl'] = $web->url->base."/berita/baca/".$rowRelatedClinic->itemGuid."/".$catalogClinic->shortTitle;
				}
				
				$related = Zend_Json::encode($rowClinic);
			}
		}
		if (in_array($row->profileGuid, array('kutu_peraturan','kutu_rancangan_peraturan','kutu_peraturan_kolonial','kutu_putusan')))
		{
			$relateAs = array('RELATED_PP','RELATED_BASE','RELATED_HISTORY');
			$rk = $this->getRelated($row->guid,$relateAs,false,"relatedGuid desc",true);
			if ($rk)
			{
				$grouped = $this->array_group_by($rk, 'relateAs');
			
				$related = Zend_Json::encode($grouped);
			}
		}
		
		
		$part->relatedItem = $related;
		}
		
		$docSystemName = null;
		$docOriginalName = null;
		$docMimeType = null;
		
		$query="SELECT * FROM KutuCatalogAttribute where catalogGuid='".$row->guid."'";
		$results = $this->db->query($query);
		$rowsetAttr = $results->fetchAll(PDO::FETCH_OBJ);
		if ($rowsetAttr) {
		$rowCount = count($rowsetAttr);
		for($i=0;$i<$rowCount;$i++)
		{
			$rowAttr = $rowsetAttr[$i];
			switch ($rowAttr->attributeGuid)
			{
				case 'fixedCommentTitle':
				case 'fixedTitle':
					if(empty($rowAttr->value))
					{
						$part->title = trim(strip_tags($row->shortTitle));
					}
					else
					{
						$part->title = (new Pandamp_Utility_Posts)->sanitize_post_title($rowAttr->value);
					}
					break;
				case 'fixedSubTitle':
					$part->subTitle = $rowAttr->value;
					break;
				case 'fixedContent':
					//$part->content = $this->clean_string_input($rowAttr->value);
					$part->content = (new Pandamp_Utility_Posts)->sanitize_post_content($rowAttr->value);
					break;
				case 'fixedKeywords':
					$part->keywords = array_map('trim', explode(',', $rowAttr->value));
					break;
				case 'fixedDescription':
					$part->description = $rowAttr->value;
					break;
				case 'fixedAuthor':
					$part->author = $rowAttr->value;
					break;
				case 'fixedComments':
					$part->comments = $rowAttr->value;
					break;
				case 'fixedNumber':
				case 'prtNomor':
				case 'ptsNomor':
					$part->number = $rowAttr->value;
					break;
				case 'fixedYear':
				case 'ptsTahun':
				case 'prtTahun':
					$part->year = (int)$rowAttr->value;
					break;
				case 'fixedDate':
				case 'prtDisahkan':
				case 'ptsDibaca':
					$part->fixedDate = $this->getDateInSolrFormat($rowAttr->value);
					break;
				case 'fixedLanguage':
					$part->language = $rowAttr->value;
					break;
				case 'fixedCommentQuestion':
					$part->question = (new Pandamp_Utility_Posts)->sanitize_post_content($rowAttr->value);
					break;
				case 'fixedAnswer':
					//$part->answer = $this->clean_string_input($rowAttr->value);
					$part->answer = (new Pandamp_Utility_Posts)->sanitize_post_content($rowAttr->value);
					break;
				case 'fixedSelectNama':
					$part->kontributor = $rowAttr->value;
					if ($kt = $this->fileImageUrl($rowAttr->value)) {
						$part->kontributorImage = $kt;
					}
					break;
				case 'fixedSource' :
				case 'fixedSelectMitra':
					$part->sumber = $rowAttr->value;
					if ($fiu = $this->fileImageUrl($rowAttr->value)) {
						$part->sumberImage = $fiu;
					}
					break;
				case 'fixedSelect':
					 
					if (!empty($rowAttr->value))
					{
						$part->kategoriklinik = $rowAttr->value;
						switch (strtolower($part->kategoriklinik))
						{
							case "lt48310d3da83db":
								$part->kategori = 'Environmental Law';
								break;
							case "lt482c0755f313e":
								$part->kategori = 'Telecommunications Law';
								break;
							case "lt482c09820860c":
								$part->kategori = 'Company Law';
								break;
							case "lt482c0a130343d":
								$part->kategori = 'Guarantee and Addiction of Debt';
								break;
							case "lt482c0a7437d49":
								$part->kategori = 'Finance and Banking Law';
								break;
							case "lt48310cf2a8c82":
								$part->kategori = 'Consumer Protection Law';
								break;
							case "lt48310d59db6d8":
								$part->kategori = 'General Law';
								break;
							case "lt482c090b12c96":
								$part->kategori = 'Property Law';
								break;
							case "lt482c09a4e06a6":
								$part->kategori = 'Family Law';
								break;
							case "lt482c0a30d9ace":
								$part->kategori = 'Bankruptcy Law';
								break;
							case "lt48310cb53f93a":
								$part->kategori = 'Employment / Labour Law';
								break;
							case "lt48310d0ddbfcc":
								$part->kategori = 'Mergers and Acquisitions';
								break;
							case "lt482c093dca3fd":
								$part->kategori = 'Human Rights Law';
								break;
							case "lt482c09ca8f83e":
								$part->kategori = 'Criminal Law';
								break;
							case "lt482c0a55ccee6":
								$part->kategori = 'Intellectual Property Law';
								break;
							case "lt48310cd5258aa":
								$part->kategori = 'Agreements';
								break;
						}
					}
				
					break;
				case 'fixedKategoriKlinik':
					$part->kategoriklinik = $rowAttr->value;
					 
					//$queryKK="SELECT value FROM KutuCatalogAttribute where catalogGuid='".$part->kategoriklinik."' AND attributeGuid='fixedTitle'";
					//$resultsKK = $this->db->query($queryKK);
						
					//$rowsetAttrKK = $resultsKK->fetchAll(PDO::FETCH_OBJ);
						
					//$part->kategori = $rowsetAttrKK[0]->value;
					$part->kategori = $this->getCatalogAttribute($rowAttr->value, 'fixedTitle');
						
					break;	
				case 'prtJenis':
					$part->regulationType = $rowAttr->value;
					switch(strtolower($part->regulationType))
					{
						case 'konstitusi':
							$part->regulationOrder = 1;
							break;
						case 'tap mpr':
							$part->regulationOrder = 11;
							break;
						case 'tus mpr':
							$part->regulationOrder = 21;
							break;
						case 'undang-undang':
						case 'uu':
							$part->regulationOrder = 31;
							break;
						case 'undang-undang darurat':
							$part->regulationOrder = 41;
							break;
						case 'perpu':
							$part->regulationOrder = 51;
							break;
						case 'pp':
							$part->regulationOrder = 61;
							break;
						case 'perpres':
							$part->regulationOrder = 71;
							break;
						case 'penpres':
							$part->regulationOrder = 81;
							break;
						case 'keppres':
							$part->regulationOrder = 91;
							break;
						case 'inpres':
							$part->regulationOrder = 101;
							break;
						case 'konvensi internasional':
							$part->regulationOrder = 111;
							break;
						case 'keputusan bersama':
							$part->regulationOrder = 121;
							break;
						case 'keputusan dewan':
							$part->regulationOrder = 131;
							break;
						case 'kepmen':
							$part->regulationOrder = 141;
							break;
						case 'permen':
							$part->regulationOrder = 151;
							break;
						case 'inmen':
							$part->regulationOrder = 161;
							break;
						case 'pengumuman menteri':
							$part->regulationOrder = 171;
							break;
						case 'surat edaran menteri':
							$part->regulationOrder = 181;
							break;
						case 'surat menteri':
							$part->regulationOrder = 191;
							break;
						case 'keputusan asisten menteri':
							$part->regulationOrder = 201;
							break;
						case 'surat asisten menteri':
							$part->regulationOrder = 211;
							break;
						case "keputusan menteri negara/ketua lembaga/badan":
							$part->regulationOrder = 221;
							break;
						case "peraturan menteri negara/ketua lembaga/badan":
							$part->regulationOrder = 231;
							break;
						case "instruksi menteri negara/ketua lembaga/badan":
							$part->regulationOrder = 241;
							break;
						case "pengumuman menteri negara/ketua lembaga/badan":
							$part->regulationOrder = 251;
							break;
						case "surat edaran menteri negara/ketua lembaga/badan":
							$part->regulationOrder = 261;
							break;
						case "surat menteri negara/ketua lembaga/badan":
							$part->regulationOrder = 271;
							break;
						case "keputusan lembaga/badan":
							$part->regulationOrder = 281;
							break;
						case "peraturan lembaga/badan":
							$part->regulationOrder = 291;
							break;
						case "instruksi lembaga/badan":
							$part->regulationOrder = 301;
							break;
						case "pengumuman lembaga/badan":
							$part->regulationOrder = 311;
							break;
						case "surat edaran lembaga/badan":
							$part->regulationOrder = 321;
							break;
						case "surat lembaga/badan":
							$part->regulationOrder = 331;
							break;
						case "keputusan kepala/ketua lembaga/badan":
							$part->regulationOrder = 341;
							break;
						case "peraturan kepala/ketua lembaga/badan":
							$part->regulationOrder = 351;
							break;
						case "instruksi kepala/ketua lembaga/badan":
							$part->regulationOrder = 361;
							break;
						case "pengumuman kepala/ketua lembaga/badan":
							$part->regulationOrder = 371;
							break;
						case "surat edaran kepala/ketua lembaga/badan":
							$part->regulationOrder = 381;
							break;
						case "surat kepala/ketua lembaga/badan":
							$part->regulationOrder = 391;
							break;
						case "keputusan komisi":
							$part->regulationOrder = 401;
							break;
						case "peraturan komisi":
							$part->regulationOrder = 411;
							break;
						case "instruksi komisi":
							$part->regulationOrder = 421;
							break;
						case "pengumuman komisi":
							$part->regulationOrder = 431;
							break;
						case "surat edaran komisi":
							$part->regulationOrder = 441;
							break;
						case "surat komisi":
							$part->regulationOrder = 451;
							break;
						case "keputusan panitia":
							$part->regulationOrder = 461;
							break;
						case "peraturan panitia":
							$part->regulationOrder = 471;
							break;
						case "instruksi panitia":
							$part->regulationOrder = 481;
							break;
						case "pengumuman panitia":
							$part->regulationOrder = 491;
							break;
						case "surat edaran panitia":
							$part->regulationOrder = 501;
							break;
						case "surat panitia":
							$part->regulationOrder = 511;
							break;
						case "keputusan direktur jenderal":
							$part->regulationOrder = 521;
							break;
						case "surat edaran direktur jenderal":
							$part->regulationOrder = 531;
							break;
						case "surat direktur jenderal":
							$part->regulationOrder = 541;
							break;
						case "instruksi direktur jenderal":
							$part->regulationOrder = 551;
							break;
						case "peraturan direktur jenderal":
							$part->regulationOrder = 561;
							break;
						case "peraturan inspektur jenderal":
							$part->regulationOrder = 571;
							break;
						case "instruksi inspektur jenderal":
							$part->regulationOrder = 581;
							break;
						case "pengumuman inspektur jenderal":
							$part->regulationOrder = 591;
							break;
						case "surat edaran inspektur jenderal":
							$part->regulationOrder = 601;
							break;
						case "surat inspektur jenderal":
							$part->regulationOrder = 611;
							break;
						case "peraturan daerah tingkat i":
							$part->regulationOrder = 621;
							break;
						case "peraturan daerah tingkat ii":
							$part->regulationOrder = 631;
							break;
						case "keputusan gubernur":
							$part->regulationOrder = 641;
							break;
						case "peraturan gubernur":
							$part->regulationOrder = 651;
							break;
						case "instruksi gubernur":
							$part->regulationOrder = 661;
							break;
						case "pengumuman gubernur":
							$part->regulationOrder = 671;
							break;
						case "surat edaran gubernur":
							$part->regulationOrder = 681;
							break;
						case "surat gubernur":
							$part->regulationOrder = 691;
							break;
						case "keputusan bupati/walikota":
							$part->regulationOrder = 701;
							break;
						case "peraturan bupati/walikota":
							$part->regulationOrder = 711;
							break;
						case "instruksi bupati/walikota":
							$part->regulationOrder = 721;
							break;
						case "pengumuman bupati/walikota":
							$part->regulationOrder = 731;
							break;
						case "surat edaran bupati/walikota":
							$part->regulationOrder = 741;
							break;
						case "surat bupati/walikota":
							$part->regulationOrder = 751;
							break;
						case "keputusan direksi":
							$part->regulationOrder = 761;
							break;
						case "peraturan direksi":
							$part->regulationOrder = 771;
							break;
						case "instruksi direksi":
							$part->regulationOrder = 781;
							break;
						case "pengumuman direksi":
							$part->regulationOrder = 791;
							break;
						case "surat edaran direksi":
							$part->regulationOrder = 801;
							break;
						case "surat direksi":
							$part->regulationOrder = 811;
							break;
						case "keputusan direktur":
							$part->regulationOrder = 821;
							break;
						case "peraturan direktur":
							$part->regulationOrder = 831;
							break;
						case "instruksi direktur":
							$part->regulationOrder = 841;
							break;
						case "pengumuman direktur":
							$part->regulationOrder = 851;
							break;
						case "surat edaran direktur":
							$part->regulationOrder = 861;
							break;
						case "surat direktur":
							$part->regulationOrder = 871;
							break;
						default:
							$part->regulationOrder = 9999;
							break;
					}
					break;
				case 'ptsJenisLembaga':
					$part->regulationType = $rowAttr->value;
					switch(strtolower($part->regulationType))
					{
						case 'ma':
						case 'mk':
							$part->regulationOrder = 1;
							break;
						case 'pt':
						case 'pttun':
						case 'pta':
						case 'mahmiltinggi':
							$part->regulationOrder = 20;
							break;
						case 'pn':
						case 'ptun':
						case 'pa':
						case 'pniaga':
						case 'mahmil':
							$part->regulationOrder = 30;
							break;
						default:
							$part->regulationOrder = 9999;
							break;
					}
					break;
				case 'docMimeType':
					$part->mimeType = $rowAttr->value;
					$docMimeType = $rowAttr->value;
					break;
				case 'docOriginalName':
					$part->fileName = $rowAttr->value;
					$docOriginalName = $rowAttr->value;
					break;
				case 'docSystemName':
					$part->systemName = $rowAttr->value;
					$docSystemName = $rowAttr->value;
					break;
				case 'docSize':
					$part->fileSize = $rowAttr->value;
					break;
				default:
					if(isset($part->_text_))
					{
						$part->_text_ .= ' '.$rowAttr->value;
					}
					else
					{
						$part->_text_ = $rowAttr->value;
					}					
			}
		}
		if($row->profileGuid=='kutu_doc')
		{
			//extract text from the file
			if (isset($docSystemName) || isset($docOriginalName) || isset($docMimeType)) {
				$sContent = $this->_extractText($row->guid, $docSystemName, $docOriginalName, $docMimeType);
				//$sContent = $this->clean_string_input($sContent);
			}
			else
				$sContent = '';
			
			
			if(isset($part->content))
			{
				$part->content .= ' '.$sContent;
			}
			else
			{
				$part->content = $sContent;
			}
		}
		}
		return $part;
	}
	
	private function fileImageUrl($guid)
	{
		$fileImage=null;
		$rowImage = $this->getRelated($guid,'RELATED_IMAGE',false,"relatedGuid DESC");
		if ($rowImage) {
			$i=0;
			foreach ($rowImage as $row)
			{
				$rowDocSystemName = $this->getCatalogAttribute($row->itemGuid, 'docSystemName');
				if ($rowDocSystemName)
				{
					//$catalogGuid = pathinfo($rowDocSystemName,PATHINFO_FILENAME);
					$ext = pathinfo($rowDocSystemName,PATHINFO_EXTENSION);
					$ext = strtolower($ext);
					
					/*$catalog = $this->getCatalog($catalogGuid, ['createdBy','createdDate']);
					if ($catalog) {
						// Pada file ImageController cli
						// pd1 ini dilakukan juga strip_tags(trim($catalog->createdBy))
						// karena ternyata pada data terdapat spasi atau kadang karakter khusus
						$pd1 = strip_tags(trim($catalog->createdBy));
						$pd2 = date('Y',strtotime($catalog->createdDate));
						$pd3 = date('m',strtotime($catalog->createdDate));
						$pd4 = date('d',strtotime($catalog->createdDate));
						
						$config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini','cdn');
						
						$imageDir = $config->static->dir->images.'/upload';
						$imageUrl = $config->static->url->images.'/upload';
						
						if (file_exists($imageDir.'/'.$pd1.'/'.$pd2.'/'.$pd3.'/'.$pd4.'/'.$rowDocSystemName))
						{
							$fileImage['original'] = $imageUrl.'/'.$pd1.'/'.$pd2.'/'.$pd3.'/'.$pd4.'/'.$rowDocSystemName;
								
							$file = new Zend_Config_Ini(APPLICATION_PATH . '/configs/image.ini', 'size');
							$keys = array_keys($file->toArray());
							foreach ($keys as $key)
							{
								if (file_exists($imageDir.'/'.$pd1.'/'.$pd2.'/'.$pd3.'/'.$pd4.'/'.$catalogGuid.'_'.$key.'.'.$ext))
								{
									$fileImage[$key] = $imageUrl.'/'.$pd1.'/'.$pd2.'/'.$pd3.'/'.$pd4.'/'.$catalogGuid.'_'.$key.'.'.$ext;
								}
							
							}
						}
					}*/
					
					/*if (file_exists(ROOT_DIR.'/images/frontend/'.$guid.'/'.$row->itemGuid.'.'.strtolower($ext))) {
						$fileImage['original'] = $this->_imageUrl.'/'.$guid.'/'.$row->itemGuid.'.'.strtolower($ext);
					}
					else if (file_exists(ROOT_DIR.'/images/frontend/'.$row->itemGuid.'.'.strtolower($ext)))
					{
						$fileImage['original'] = $this->_imageUrl.'/'.$row->itemGuid.'.'.strtolower($ext);
					}
					
					if (file_exists(ROOT_DIR.'/images/frontend/'.$guid.'/tn_'.$row->itemGuid.'.'.strtolower($ext))) {
						$fileImage['thumbnail'] = $this->_imageUrl.'/'.$guid.'/tn_'.$row->itemGuid.'.'.strtolower($ext);
					}
					else if (file_exists(ROOT_DIR.'/images/frontend/tn_'.$row->itemGuid.'.'.strtolower($ext))) {
						$fileImage['thumbnail'] = $this->_imageUrl.'/tn_'.$row->itemGuid.'.'.strtolower($ext);
					}*/
					
					/*if ($catalogGuid !== $row->itemGuid)
					{
						$ig = $this->getItemRelated($catalogGuid,'RELATED_IMAGE');
						$guid = $ig->relatedGuid; 
					}*/
					
					/*if ($ori = $this->giu($guid, $catalogGuid, $ext, null, "local")) {
						$fileImage[$i]['original'] = $ori;
					}
					
					$file = new Zend_Config_Ini(APPLICATION_PATH . '/configs/image.ini','size');
					$keys = array_keys($file->toArray());
					foreach ($keys as $key)
					{
						if ($img = $this->giu($guid, $catalogGuid, $ext, $key.'_', "local")) {
							$fileImage[$i][$key] = $img;
						}
					}
					
					if ($th = $this->giu($guid, $catalogGuid, $ext, "tn_", "local")) {
						$fileImage[$i]['thumbnail'] = $th;
					}
					
					if ($caption = $this->getCatalogAttribute($catalogGuid, "fixedTitle"))
					{
						$fileImage[$i]['caption'] = strip_tags(trim($caption));
					}*/
					
					
					if ($ori = $this->giu($guid, $row->itemGuid, $ext, null, "local")) {
						$fileImage[$i]['original'] = $ori;
					}
					if ($th = $this->giu($guid, $row->itemGuid, $ext, "tn_", "local")) {
						$fileImage[$i]['thumbnail'] = $th;
					}
						
					if ($caption = $this->getCatalogAttribute($row->itemGuid, "fixedTitle"))
					{
						$fileImage[$i]['caption'] = strip_tags(trim($caption));
					}
					
				}
				$i++;
			}
			
			return Zend_Json::encode($fileImage);
		}
		
		return;
	}
	
	protected function generateShortener($shortTitle)
	{
		$shortUrl = null;
		
		$db = $this->db3;
		
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
		
		$sql = $db->select();
		
		$sql->from('urls', ['id']);
		$sql->where("url LIKE '%$shortTitle%'");
		
		$row = $db->fetchRow($sql);
		
		if (isset($row) && !empty($row))
		{
			$hex = dechex($row->id);
			
			$web = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application-cli.ini','web');
			$shortUrl = $web->url->short.'/'.$hex;
		}
		
		return $shortUrl;
	}
	
	protected function getItemRelated($itemGuid,$relateAs)
	{
		$db = $this->db;
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
		$sql = $db->select();
		$sql->from('KutuRelatedItem', '*');
		$sql->where('itemGuid=?',$itemGuid);
		$sql->where('relateAs=?',$relateAs);
		return $db->fetchRow($sql);
	}
	protected function getRelated($relatedGuid,$relateAs,$asRow,$order=null,$multi=false)
	{
		$db = $this->db;
	
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
	
		$sql = $db->select();
	
		$sql->from('KutuRelatedItem', '*');
		$sql->where('relatedGuid=?',$relatedGuid);
		
		if ($multi) {
			$data = $this->implode_with_keys(", ", $relateAs, "'");
			$sql->where("relateAs IN ($data)");
		}
		else 
			$sql->where('relateAs=?',$relateAs);
		
		
		if ($order !== null) {
			$sql->order($order);	
		}
		
		if ($asRow) {
			return $db->fetchRow($sql);
		}
		
		return $db->fetchAll($sql);
	
	}
	
	protected function getCatalog($catalogGuid, $field)
	{
		$db = $this->db;
		
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
		
		$sql = $db->select();
		$sql->from('KutuCatalog', $field);
		$sql->where('guid=?',$catalogGuid);
		$row = $db->fetchRow($sql);
		
		return ($row) ? $row : '';
	}
	
	protected function getCatalogAttribute($guid,$attributeGuid)
	{
		$db = $this->db;
	
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
	
		$sql = $db->select();
		$sql->from('KutuCatalogAttribute', ['value']);
		$sql->where('catalogGuid=?',$guid);
		$sql->where('attributeGuid=?',$attributeGuid);
		$row = $db->fetchRow($sql);
	
		$sql = $sql->__toString();
	
		return ($row) ? $row->value : '';
	}
	
	public function getCountCatalog($guid, $profileGuid, $type)
	{
		$valueText=null;
		if (isset($profileGuid) && !in_array($profileGuid, array('partner','author','kategoriklinik','comment','about_us','kutu_contact','kutu_email','kutu_kotik','kutu_mitra','kutu_signup'))) {
			switch ($type) {
				case 'desktop':
					if (in_array($profileGuid, array('article','isuhangat'))) {
						$valueText = 'TICKER';
					}
					else if ($profileGuid=='klinik') {
						$valueText = 'klinik';
					}
					else if (in_array($profileGuid, array('kutu_peraturan','kutu_rancangan_peraturan','kutu_peraturan_kolonial','kutu_putusan')))
					{
						$valueText = 'pusatdata';
					}
						
					break;
						
				case 'mobile':
					if (in_array($profileGuid, array('article','isuhangat'))) {
						$valueText = 'TICKER-MOBILE';
					}
					else if ($profileGuid=='klinik') {
						$valueText = 'klinik-mobile';
					}
					else if (in_array($profileGuid, array('kutu_peraturan','kutu_rancangan_peraturan','kutu_peraturan_kolonial','kutu_putusan')))
					{
						$valueText = 'pusatdata-mobile';
					}
	
					break;
			}
			
			$db = $this->db;
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			$sql = $db->select();
			$sql->from('KutuAssetSetting', ['valueInt']);
			$sql->where('guid=?',$guid);
			
			if ($profileGuid=='kutu_doc') {
				$sql->where('application=?','kutu_doc');
			}
			else
			{
				$sql->where('valueText=?',$valueText);
			}
			
			
			
			$row = $db->fetchRow($sql);
			
			//$sql = $sql->__toString();
			//print_r($sql);die;
			
			return ($row) ? $row->valueInt : 0;
				
			
	
		}
	
		return 0;
	}	
	
	public function giu($guid, $itemguid, $ext, $prefix=null,$default="remote")
	{
		$config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application-cli.ini','cdn');
		
		$imageDir = $config->static->dir->images;
		$imageUrl = $config->static->url->images;
		
		$url1 = @getimagesize($imageUrl.'/'.$guid.'/'.$prefix.$itemguid.'.'.$ext);
		$url2 = @getimagesize($imageUrl.'/'.$prefix.$itemguid.'.'.$ext);
		
		if ($default=="remote") {
			$chkImg1 = is_array($url1);
			$chkImg2 = is_array($url2);
		}
		else
		{
			$chkImg1 = file_exists($imageDir.'/'.$guid.'/'.$prefix.$itemguid.'.'.$ext);
			$chkImg2 = file_exists($imageDir.'/'.$prefix.$itemguid.'.'.$ext);
		}
		
		if ($chkImg1) {
			$image = $imageUrl.'/'.$guid.'/'.$prefix.$itemguid.'.'.$ext;
		}
		else if ($chkImg2)
		{
			$image = $imageUrl.'/'.$prefix.$itemguid.'.'.$ext;
		}
		else
		{
			$image = null;
		}
		
		return $image;
	}
	
	public function getDateInSolrFormat($date) {
		if($date=='0000-00-00 00:00:00' OR $date=='0000-00-00' OR $date=='' OR $date==NULL) {
			//return '0000-00-00T00:00:00Z';
			return '1999-12-31T23:59:59Z';
		}
		else
		{
			return date("Y-m-d\\TH:i:s\\Z",strtotime($date));
		}
	}
	
	private function _extractText($guid, $systemName, $fileName, $mimeType)
	{
		$query="SELECT * FROM KutuRelatedItem where itemGuid='$guid' AND relateAs='RELATED_FILE'";
		$results = $this->db->query($query);
		
		$rowset = $results->fetchAll(PDO::FETCH_OBJ);
		
		if(count($rowset))
		{
			$row = $rowset[0];
			$parentCatalogGuid = $row->relatedGuid;
				
			if(!empty($systemName))
				$fileName = $systemName;
			
			$sDir1 = ROOT_DIR.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR.$fileName;
			$sDir2 = ROOT_DIR.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR.$parentCatalogGuid.DIRECTORY_SEPARATOR.$fileName;
			
			$sDir = '';
			if(file_exists($sDir1))
			{
				$sDir = $sDir1;
			}
			else
				if(file_exists($sDir2))
				{
					$sDir = $sDir2;
				}
				
			if(!empty($sDir))
			{
				$outpath = $sDir.'.txt';
				
				switch ($mimeType)
				{
					case 'application/pdf':
						$pdfExtractor = $this->_pdfExtractor;
						system("$pdfExtractor ".$sDir.' '.$outpath, $ret);
						if ($ret == 0)
						{
							$value = file_get_contents($outpath);
							unlink($outpath);
							echo 'content PDF: '. $sDir.' ' . strlen($value)."\n";
							if(strlen($value) > 20)
								return (new Pandamp_Utility_Posts)->sanitize_post_content($value);
							else
							{
								echo "content file kosong\n";
								return '';
							}
						}
						if ($ret == 127)
							print "Could not find pdftotext tool.\n";
							return '';
						if ($ret == 1)
							print "Could not find pdf file.\n";
							return '';
						break;
					case 'text/html':
					case 'text/plain':
						$docHtml = Zend_Search_Lucene_Document_Html::loadHTMLFile($sDir);
						return $docHtml->getFieldValue('body');
						break;
					case 'application/x-javascript':
					case 'application/octet-stream':
					case 'application/msword':
						if(strpos(strtolower($fileName), '.doc'))
						{
							$extractor = $this->_wordExtractor;
							system("$extractor -m cp850.txt ".$sDir.' > '.$outpath, $ret);
							if ($ret == 0)
							{
								$value = file_get_contents($outpath);
								unlink($outpath);
								//echo $value;
								return $value;
							}
							if ($ret == 127)
								//print "Could not find pdftotext tool.";
								return '';
							if ($ret == 1)
								//print "Could not find pdf file.";
								return '';
						}
						else
						{
							return '';
						}
						break;
					default :
						return '';
						break;
				}
			}
		}
		
		return;
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
	
	// http://snippets.khromov.se/convert-comma-separated-values-to-array-in-php/
	function comma_separated_to_array($string, $separator = ',')
	{
		//Explode on comma
		$vals = explode($separator, $string);
	
		//Trim whitespace
		foreach($vals as $key => $val) {
			$vals[$key] = trim($val);
		}
		//Return empty array if no items found
		//http://php.net/manual/en/function.explode.php#114273
		return array_diff($vals, array(""));
	}
	
	public function implode_with_keys($glue, $array, $valwrap)
	{
		if (is_array($array)) {
			foreach ($array as $key => $value) {
				$ret[] = $valwrap.$value.$valwrap;
			}
			return implode($glue,$ret);
		}
	}
	
	function array_group_by($arr, $key)
	{
		if (!is_array($arr)) {
			trigger_error('array_group_by(): The first argument should be an array', E_USER_ERROR);
		}
		if (!is_string($key) && !is_int($key) && !is_float($key)) {
			trigger_error('array_group_by(): The key should be a string or an integer', E_USER_ERROR);
		}
	
		// Load the new array, splitting by the target key
		$grouped = array();
		foreach ($arr as $value) {
			$grouped[$value->$key][] = $value;
		}
	
		// Recursively build a nested grouping if more parameters are supplied
		// Each grouped array value is grouped according to the next sequential key
		if (func_num_args() > 2) {
			$args = func_get_args();
	
			foreach ($grouped as $key => $value) {
				$parms = array_merge(array($value), array_slice($args, 2, func_num_args()));
				$grouped[$key] = call_user_func_array('array_group_by', $parms);
			}
		}
	
		return $grouped;
	}	
}