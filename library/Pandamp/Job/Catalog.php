<?php
require_once( 'Apache/Solr/Service.php' );

class Pandamp_Job_Catalog extends Pandamp_Job_Base
{
	private $_pdfExtractor = 'pdftotext';
	private $_wordExtractor = 'antiword';
	public function runJob()
	{
		$folderGuid = (isset($this->params['folderGuid']))? $this->params['folderGuid'] : '';
		$catalogGuid = (isset($this->params['guid']))? $this->params['guid'] : '';
		$ip = (isset($this->params['ip']))? $this->params['ip'] : '';
		$kopel = (isset($this->params['kopel']))? $this->params['kopel'] : '';
		$lang = (isset($this->params['lang']))? $this->params['lang'] : 'id';
		
		if ($lang !== 'en') {
			$this->toShortUrl($catalogGuid, $folderGuid, $ip, $kopel, $lang);
		}
		
		$this->toSolr($catalogGuid, $lang);
		$this->toDoc($catalogGuid, $lang);
		
		$this->addCache($folderGuid);
		
		return true;
	}
	
	public function addCache($folderGuid)
	{
		/**
		 * aktual, utama, berita, klinik, klinik published, editorial, fokus, after office, tajuk, tokoh, isu hangat, resensi, jeda, kolom, info, pojok peradilan, berita foto, talks, past event, gallery, komunitas, surat pembaca, RECHTSCHOOL
		 * fb29, lt4aaa29322bdbb, fb16, lt4a0a533e31979, lt4b11e8c86c8a4, lt54b470ce7255c, fb4, lt51b824118f00d, fb18, fb12, lt4a6f7d5377193, fb17, fb14, fb7, fb9, lt55dd40da17f5c, lt4de5c32a53bd4, lt4c93230c9d0a5, lt4a607b5e3c2f2, lt4f0fefa26f140, fb19, fb8, lt51822eae8c808 
		 */
		if (!in_array($folderGuid, ['fb29','lt4aaa29322bdbb','fb16','lt4a0a533e31979','lt4b11e8c86c8a4','lt54b470ce7255c','fb4','lt51b824118f00d','fb18','fb12','lt4a6f7d5377193','fb17','fb14','fb7','fb9','lt55dd40da17f5c','lt4de5c32a53bd4','lt4c93230c9d0a5','lt4a607b5e3c2f2','lt4f0fefa26f140','fb19','fb8','lt51822eae8c808'])) {
			return;
		}
		
		$db = $this->getDbHandler('hid');
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
		
		$sql = $db->select();
		
		$sql->from('KutuSetting', ['dataCache']);
		$sql->where('id=?',1);
		
		$row = $db->fetchRow($sql);
		
		$un = unserialize($row->dataCache);
		if($un!="")
		{
			if (!in_array($folderGuid, $un))
			{
				$un[] = $folderGuid;
			}
			$un = serialize($un);
		}
		else
		{
			$un = serialize([$folderGuid]);
		}
		
		
		$data = ['dataCache'=>$un];
		$db->update('KutuSetting',$data,"id=1");
	}
	
	public function toShortUrl($catalogGuid, $folderGuid = null, $ip = null, $kopel = null, $lang)
	{
		$web = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application-cli.ini','web');
		
		$catalog = $this->getCatalog($catalogGuid, ['profileGuid','shortTitle'], $lang);
		
		if (!$catalog) {
			return true;
		}
		
		if ($catalog->profileGuid == 'kutu_doc') {
			$this->log()->notice('guid: ' . $catalogGuid . ' adalah dokumen tidak perlu shortUrl');
			return;
		}
		
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
			$this->log()->err('Solr shortUrl service not responding');
				
			return;
		}

		$this->log()->info('start shortUrl id: ' . $catalogGuid);
		
		$hits = $solr->search($q,0,1,['fl'=>'id']);
		if (isset($hits->response->docs[0])) {
			$row = $hits->response->docs[0];
			$hid = $row->id;
			$db->update('shorturls',$data,"id=$hid");
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
				
				$recoveredArray[] = $missing;
				sort($recoveredArray);
				file_put_contents(ROOT_DIR.DS.'data'.DS.'datashorturl.txt', serialize($recoveredArray));
			}
			else 
			{
				$sm = $db->select();
				$sm->from('shorturls',array(new Zend_Db_Expr('MAX(id)+1 as maxid')));
				$rowMax = $db->fetchRow($sm);
				$numId = $rowMax->maxid;
			}
			
			$data['id'] = $numId;
			
			$insert = $db->insert('shorturls', $data);
				
			//$hid = $db->lastInsertId('shorturls', 'id');
			$hid = $numId;
		}
		
		
		$select = $db->select();
		$select->from('shorturls', '*');
		$select->where("id='$hid'");
		
		$row = $db->fetchRow($select);
		
		if(isset($row) && ! empty($row))
		{
			$documents = array();
			
			$documents[] = $this->_createSolrShortUrlDocument($row);
			
			try {
				$solr->addDocuments( $documents );
				$solr->commit();
			}
			catch ( Exception $e ) {
				throw new Zend_Exception($e->getMessage());
			}
		}
	}
	
	public function toDoc($catalogGuid, $lang)
	{
		$relateAs = array('RELATED_FILE','RELATED_IMAGE');
		$docs = $this->getRelated($catalogGuid,$relateAs,$lang,false,"relatedGuid desc",true);
		if ($docs)
		{
			foreach ($docs as $doc)
			{
				$this->toSolr($doc->itemGuid, $lang);
			}
		}
	}
	
	public function toSolr($catalogGuid, $lang)
	{
		$solr = $this->getSolrService($lang);
		
		if ( ! $solr->ping() ) {
			$this->log()->err('Solr service not responding');
			
			return;
		}

		$this->log()->info('index guid: ' . $catalogGuid);
		
		$db = $this->getDbHandler($lang);
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
		
		$select = $db->select();
		$select->from('KutuCatalog', '*');
		$select->where("guid='$catalogGuid'");
		
		$row = $db->fetchRow($select);
		
		if(isset($row) && ! empty($row))
		{
			$documents = array();
				
			$documents[] = $this->_createSolrDocument($row, $lang);
				
			try {
				$solr->addDocuments( $documents );
				$solr->commit();
				//$solr->optimize();
			}
			catch ( Exception $e ) {
				throw new Zend_Exception($e->getMessage());
			}
		}
	}
	
	private function _createSolrShortUrlDocument(&$row)
	{
		$part = new Apache_Solr_Document();
		$part->id = $row->id;
		$part->url = $row->url;
		$part->createdate = $this->getDateInSolrFormat($row->createdate);
		$part->remoteip = $row->remoteip;
		$part->kopel = (isset($row->kopel))? $row->kopel : '';
	
		return $part;
	}
	
	private function _createSolrDocument(&$row, $lang)
	{
		$part = new Apache_Solr_Document();
		$part->id = $row->guid;
		$part->shortTitle = $row->shortTitle;
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
		
		$db = $this->getDbHandler($lang);
		
		if ($row->profileGuid !== "kutu_doc") {
		if ($lang !== 'en') {	
		$part->desktop = $this->getCountCatalog($row->guid, $row->profileGuid, $lang, 'desktop');
		$part->mobile = $this->getCountCatalog($row->guid, $row->profileGuid, $lang, 'mobile');
		}
		
		$related = null;
		$cf = array();
		
		$queryCF = "SELECT * FROM KutuCatalogFolder where catalogGuid='".$row->guid."'";
		$resultsCF = $db->query($queryCF);
		$rowsetAttrCF = $resultsCF->fetchAll(PDO::FETCH_OBJ);
		$rowCountCF = count($rowsetAttrCF);
		for($x=0;$x<$rowCountCF;$x++)
		{
			$rowAttrCF = $rowsetAttrCF[$x];
			$cf[] = $rowAttrCF->folderGuid;
		}
		
		$part->kategoriId = $cf;
		
		$part->shortenerUrl = $this->generateShortener($row->shortTitle);
		
		
		$part->fileImage = $this->fileImageUrl($row->guid, $lang);
		
		$web = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application-cli.ini','web');
		if ($row->profileGuid == "klinik") {
			$rk = $this->getRelated($row->guid,"RELATED_Clinic",$lang,false,"relatedGuid desc");
			if ($rk)
			{
				$rowClinic = array();
				for($v=0;$v<count($rk);$v++)
				{
					$rowRelatedClinic = $rk[$v];
					$catalogClinic = $this->getCatalog($rowRelatedClinic->itemGuid, ['shortTitle'],$lang);
					$rowClinic[$v]['title'] = strip_tags(trim($this->getCatalogAttribute($rowRelatedClinic->itemGuid, 'fixedCommentTitle',$lang)));
					$rowClinic[$v]['pageUrl'] = $web->url->base."/klinik/detail/".$rowRelatedClinic->itemGuid."/".$catalogClinic->shortTitle;
				}
		
				$related = Zend_Json::encode($rowClinic);
			}
		}		
		if ($row->profileGuid == "article") {
			$rk = $this->getRelated($row->guid,"RELATED_OTHER",$lang,false,"relatedGuid desc");
			if ($rk)
			{
				$rowClinic = array();
				for($v=0;$v<count($rk);$v++)
				{
					$rowRelatedClinic = $rk[$v];
					$catalogClinic = $this->getCatalog($rowRelatedClinic->itemGuid, ['shortTitle'],$lang);
					$rowClinic[$v]['title'] = strip_tags(trim($this->getCatalogAttribute($rowRelatedClinic->itemGuid, 'fixedTitle',$lang)));
					$rowClinic[$v]['pageUrl'] = $web->url->base."/berita/baca/".$rowRelatedClinic->itemGuid."/".$catalogClinic->shortTitle;
				}
		
				$related = Zend_Json::encode($rowClinic);
			}
		}
		if (in_array($row->profileGuid, array('kutu_peraturan','kutu_rancangan_peraturan','kutu_peraturan_kolonial','kutu_putusan')))
		{
			$relateAs = array('RELATED_PP','RELATED_BASE','RELATED_HISTORY');
			$rk = $this->getRelated($row->guid,$relateAs,$lang,false,"relatedGuid desc",true);
			if ($rk)
			{
				$grouped = $this->array_group_by($rk, 'relateAs');
					
				$related = Zend_Json::encode($grouped);
			}
		}
		
		if (null != $related) {
		$part->relatedItem = $related;
		}
		
		}
		
		$docSystemName = null;
		$docOriginalName = null;
		$docMimeType = null;
		
		$query="SELECT * FROM KutuCatalogAttribute where catalogGuid='".$row->guid."'";
		$results = $db->query($query);
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
							$part->title = $row->shortTitle;
						}
						else
						{
							$part->title = $rowAttr->value;
						}
						break;
					case 'fixedSubTitle':
						$part->subTitle = $rowAttr->value;
						break;
					case 'fixedContent':
						//$part->content = $this->clean_string_input($rowAttr->value);
						$part->content = (new Pandamp_Utility_Posts)->sanitize_post_content($rowAttr->value);
						break;
					case 'fixedNarsum':
						$part->narsum = array_map('trim', explode(',', $rowAttr->value));
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
						$part->question = (new Pandamp_Utility_Posts)->sanitize_post_title($rowAttr->value);
						break;
					case 'fixedAnswer':
						//$part->answer = $this->clean_string_input($rowAttr->value);
						$part->answer = (new Pandamp_Utility_Posts)->sanitize_post_content($rowAttr->value);
						break;
					case 'fixedSelectNama':
						$part->kontributor = $rowAttr->value;
						if ($kt = $this->fileImageUrl($rowAttr->value,$lang)) {
							$part->kontributorImage = $kt;
						}
						break;
					case 'fixedSource' :
					case 'fixedSelectMitra':
						$part->sumber = $rowAttr->value;
						if ($fiu = $this->fileImageUrl($rowAttr->value,$lang)) {
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
						$part->kategori = $this->getCatalogAttribute($rowAttr->value, 'fixedTitle',$lang);
	
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
					$sContent = $this->_extractText($row->guid, $docSystemName, $docOriginalName, $docMimeType, $lang);
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
	
	private function _extractText($guid, $systemName, $fileName, $mimeType, $lang)
	{
		$db = $this->getDbHandler($lang);
		
		$query="SELECT * FROM KutuRelatedItem where itemGuid='$guid' AND relateAs='RELATED_FILE'";
		$results = $db->query($query);
	
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
	
	
	private function fileImageUrl($guid, $lang)
	{
		$fileImage=null;
		$rowImage = $this->getRelated($guid,'RELATED_IMAGE',$lang,false,"relatedGuid DESC");
		if ($rowImage) {
			$i=0;
			foreach ($rowImage as $row)
			{
				$this->log()->info('RELATED_IMAGE itemGuid:'.$row->itemGuid);
				
				$rowDocSystemName = $this->getCatalogAttribute($row->itemGuid, 'docSystemName', $lang);
				if ($rowDocSystemName)
				{
					$catalogGuid = pathinfo($rowDocSystemName,PATHINFO_FILENAME);
					$ext = pathinfo($rowDocSystemName,PATHINFO_EXTENSION);
					$ext = strtolower($ext);
					
					// @TODO query ke catalog
					/*$catalog = $this->getCatalog($catalogGuid, ['createdBy','createdDate'], $lang);
					if ($catalog) {
						$pd1 = $catalog->createdBy;
						$pd2 = date('Y',strtotime($catalog->createdDate));
						$pd3 = date('m',strtotime($catalog->createdDate));
						$pd4 = date('d',strtotime($catalog->createdDate));
						$cdn = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application-cli.ini','cdn');
						$imageDir = $cdn->static->dir->images.'/upload';
						$imageUrl = $cdn->static->url->images.'/upload';
						if (file_exists($imageDir.'/'.$pd1.'/'.$pd2.'/'.$pd3.'/'.$pd4.'/'.$rowDocSystemName))
						{
							$fileImage['original'] = $imageUrl.'/'.$pd1.'/'.$pd2.'/'.$pd3.'/'.$pd4.'/'.$rowDocSystemName;
							$file = new Zend_Config_Ini(APPLICATION_PATH . '/configs/image.ini','size');
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
					
					if (substr($catalogGuid,0,2) !== 'lt') {
						$catalogGuid = $row->itemGuid;
					}
						
					$ig = $this->getItemRelated($catalogGuid,'RELATED_IMAGE',$lang);
					if ($ig)
						$guid = $ig->relatedGuid;
					
					
					if ($ori = $this->giu($guid, $catalogGuid, $ext, null, "local")) {
						$fileImage[$i]['original'] = $ori;
					}

					if ($th = $this->giu($guid, $catalogGuid, $ext, "tn_", "local")) {
						$fileImage[$i]['thumbnail'] = $th;
					}
						
					$file = new Zend_Config_Ini(APPLICATION_PATH . '/configs/image.ini','size');
					$keys = array_keys($file->toArray());
					foreach ($keys as $key)
					{
						if ($img = $this->giu($guid, $catalogGuid, $ext, $key.'_', "local")) {
							$fileImage[$i][$key] = $img;
						}
					}
					

					if ($caption = $this->getCatalogAttribute($catalogGuid, "fixedTitle", $lang))
					{
						$fileImage[$i]['caption'] = strip_tags(trim($caption));
					}
						
				}
				$i++;
			}
				
			$this->log()->info('Guid:'.$guid.' imagenya adalah: ' . Zend_Json::encode($fileImage));
			return Zend_Json::encode($fileImage);
		}
		else
		{
			$this->log()->warn('RELATED_IMAGE untuk guid:'.$guid.' kosong');
		}
	
		return;
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
	
	private function getCatalogAttribute($guid,$attributeGuid,$lang)
	{
		$db = $this->getDbHandler($lang);
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
	
		$sql = $db->select();
		$sql->from('KutuCatalogAttribute', ['value']);
		$sql->where('catalogGuid=?',$guid);
		$sql->where('attributeGuid=?',$attributeGuid);
		$row = $db->fetchRow($sql);
	
		$sql = $sql->__toString();
	
		return ($row) ? $row->value : '';
	}
	
	protected function getItemRelated($itemGuid,$relateAs,$lang)
	{
		$db = $this->getDbHandler($lang);
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
		$sql = $db->select();
		$sql->from('KutuRelatedItem', '*');
		$sql->where('itemGuid=?',$itemGuid);
		$sql->where('relateAs=?',$relateAs);
		return $db->fetchRow($sql);
	}
	
	private function getRelated($relatedGuid,$relateAs,$lang,$asRow,$order=null,$multi=false)
	{
		$db = $this->getDbHandler($lang);
	
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
	
	private function getCountCatalog($guid, $profileGuid, $lang, $type)
	{
		$valueText=null;
		if (isset($profileGuid) && !in_array($profileGuid, array('partner','narsum','author','kategoriklinik','comment','about_us','kutu_contact','kutu_email','kutu_kotik','kutu_mitra','kutu_signup'))) {
			switch ($type) {
				case 'desktop':
					if (in_array($profileGuid, array('article','talks','isuhangat','kutu_agenda','video'))) {
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
					if (in_array($profileGuid, array('article','talks','isuhangat','kutu_agenda','video'))) {
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
				
			$db = $this->getDbHandler($lang);
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
	
	private function generateShortener($shortTitle)
	{
		$shortUrl = null;
	
		$db = $this->getDbHandler('sh');
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
	
		$sql = $db->select();
	
		$sql->from('shorturls', ['id']);
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
	
	private function log()
	{
		$logger = new Zend_Log();
		
		$writer = new Zend_Log_Writer_Stream(APPLICATION_PATH . "/../temp/log/application.log");
		
		// @TODO Filter only Log::CRIT
		//$filter = new Zend_Log_Filter_Priority(Zend_Log::CRIT);
		//$writer->addFilter($filter);
		
		$logger->addWriter($writer);
		
		return $logger;
	}
	
	private function getDbHandler($lang)
	{
		$multidb = Pandamp_Application::getResource('multidb');
		$multidb->init();
		
		$this->db = $multidb->getDb('db1');		//id
		$this->db2 = $multidb->getDb('db2');	//hid
		$this->db4 = $multidb->getDb('db4');	//en
		$this->db3 = $multidb->getDb('db3');	//shortUrl
		
		if ($lang == "id")
			return $this->db;
		elseif ($lang == "hid")
			return $this->db2;
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
	
	private function implode_with_keys($glue, $array, $valwrap)
	{
		if (is_array($array)) {
			foreach ($array as $key => $value) {
				$ret[] = $valwrap.$value.$valwrap;
			}
			return implode($glue,$ret);
		}
	}
	
	private function array_group_by($arr, $key)
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