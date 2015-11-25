<?php

class ImageController extends Application_Controller_Cli
{
	public function newlocAction()
	{
		$request = $this->getRequest();
		$query = $request->getParam('q');
		
		/*$size = [
			'square' => 'crop_99_103', //legalpanelsidecontent
			'thumbnail' => 'resize_100_53', //terbaru
			'multimedia' => 'resize_245_169', //multimediabxslider
			'small' => 'resize_213_142', //klinik
			'headsmall' => 'resize_213_160', //header berita
			'crop' => 'crop_324_169', //ijt
			'cropnext' => 'crop_325_183', //nextevent
			'mainhead' => 'resize_462_309', //utama
			'medium' => 'resize_646_431' //detailberita
		];*/
		
		$tool = 'gd';
		
		$size = new Zend_Config_Ini(APPLICATION_PATH . '/configs/image.ini','size');
		
		$sizes 	= array();
		foreach ($size->toArray() as $key => $value) {
			list($method, $width, $height) = explode('_', $value);
			$sizes[$key] = array('method' => $method, 'width' => $width, 'height' => $height);
		}
		
		/**
		 * Generate thumbnails
		 */
		$thumbnailSizes = array_keys($sizes);
		
		$db = $this->db;
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
		$select = $db->select();
		$select->from('KutuCatalog', '*');
		
		if (isset($query) && !empty($query))
		{
			$select->where($query);
		}
		
		$select->order('createdDate ASC');
		
		$rowsFound = $db->fetchAll($select);
		
		echo 'There are '.count($rowsFound)." catalog(s)\n";
		
		$rowCount = count($rowsFound);
		for($iCount=0;$iCount<$rowCount;$iCount++) {
			$row = $rowsFound[$iCount];
			$fileImage=null;
			$rowsetRelatedItem = $this->getDocumentById($row->guid, 'RELATED_IMAGE', true, "relatedGuid DESC");
			if ($rowsetRelatedItem) {
				$g=0;
				foreach ($rowsetRelatedItem as $related) {
				//get image url
				$rowDocSystemName = $this->getCatalogAttribute($related->itemGuid, 'docSystemName');
				if ($rowDocSystemName)
				{
					$ext = pathinfo($rowDocSystemName,PATHINFO_EXTENSION);
					$ext = strtolower($ext);
					
					if ($image = $this->giu($row->guid, $related->itemGuid, $ext, null, "local")) {
						$fileImage[$g]['original'] = $image;
					}
					
					$cdn = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application-cli.ini','cdn');
					
					//$dir = $cdn->static->dir->images . DIRECTORY_SEPARATOR . 'upload';
					$dir = $cdn->static->dir->images;
					
					//$catalogDb = $this->getCatalog($related->itemGuid, ['createdBy','createdDate']);
					
					//$path = implode(DS, array(strip_tags(trim($catalogDb->createdBy)), date('Y',strtotime($catalogDb->createdDate)), date('m',strtotime($catalogDb->createdDate)), date('d',strtotime($catalogDb->createdDate))));
					$path = implode(DS, [$row->guid]);
					Pandamp_Utility_File::createDirs($dir, $path);
					
					//$fileName  = uniqid('lt');
					$fileName = $related->itemGuid;
					
					// taruh file original nya di folder tujuan baru
					$fileku	= $dir . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . $fileName . '.' . $ext;
					//file_put_contents($fileku, file_get_contents($image));
					
					$service = null;
					$service = new Pandamp_Image_GD();
					
					$baseUrl = $cdn->static->url->images;
					
					if ($th = $this->giu($row->guid, $fileName, $ext, "tn_", "local")) {
						$fileImage[$g]['thumbnail'] = $th;
					}
						
					//mulai optimasi
					foreach ($thumbnailSizes as $s) {
						$service->setFile($image);
						$method = $sizes[$s]['method'];
						$width 	= $sizes[$s]['width'];
						$height = $sizes[$s]['height'];
							
						$f 		 = $s . '_' . $fileName . '.' . $ext;
						$newFile = $dir . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . $f;
						
						/**
						 * Create thumbnail
						 */
						switch ($method) {
							case 'resize':
								$service->resizeLimit($newFile, $width, $height);
								break;
							case 'crop':
								$service->crop($newFile, $width, $height);
								break;
						}
						
						if ($img = $this->giu($row->guid, $fileName, $ext, $s.'_', "local")) {
							$fileImage[$g][$s] = $img;
						}
						
						
					} // end foreach optimasi
					
					if ($caption = $this->getCatalogAttribute($fileName, "fixedTitle"))
					{
						$fileImage[$g]['caption'] = strip_tags(trim($caption));
					}
					
					//beritahu nama file baru catalogAttribute
					$db->update('KutuCatalogAttribute',['value' => $fileName . '.' . $ext],"catalogGuid='$related->itemGuid' AND attributeGuid='docSystemName'");
					
					
					try {
						
						//update document
						$this->addHitsBySolr(json_encode([[
								"id" => $related->itemGuid,
								"systemName" => ["set" => $fileName . '.' . $ext],
								"modifiedDate" => ["set" => date("Y-m-d\\TH:i:s\\Z")]
							]]));
					}
					catch (Zend_Exception $e)
					{
						$this->log()->err($e->getMessage());
					}
										
					
				}
				
				$g++;
				} //end foreach
				
				//update catalog
				$this->addHitsBySolr(json_encode([[
						"id" => $row->guid,
						"fileImage" => ["set" => Zend_Json::encode($fileImage)],
						"modifiedDate" => ["set" => date("Y-m-d\\TH:i:s\\Z")]
					]]));
				
				$this->log()->info(Zend_Json::encode($fileImage));
			}
			
		}
		
		sleep(1);
		
		echo "Images optimizing completed\n";
	}
	
	// fileImage old format
	public function fileimageAction()
	{
		$request = $this->getRequest();
		$query = $request->getParam('q');
		
		$db = $this->db;
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
		$select = $db->select();
		$select->from('KutuCatalog', '*');
		
		if (isset($query) && !empty($query))
		{
			$select->where($query);
		}
		
		$select->order('createdDate ASC');
		
		$rowsFound = $db->fetchAll($select);
		
		echo 'There are '.count($rowsFound)." catalog(s)\n";
		
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
			
			$rowsetRelatedItem = $this->getDocumentById($row->guid, 'RELATED_IMAGE', true, "relatedGuid DESC");
			if ($rowsetRelatedItem) {
				$fileImage='';
				$i=0;
				foreach ($rowsetRelatedItem as $image)
				{
					$rowDocSystemName = $this->getCatalogAttribute($image->itemGuid, 'docSystemName');
					if ($rowDocSystemName)
					{
						$ext = pathinfo($rowDocSystemName,PATHINFO_EXTENSION);
						$ext = strtolower($ext);
						if ($ori = $this->giu($row->guid, $image->itemGuid, $ext, null, "local")) {
							$fileImage[$i]['original'] = $ori;
						}
						if ($th = $this->giu($row->guid, $image->itemGuid, $ext, "tn_", "local")) {
							$fileImage[$i]['thumbnail'] = $th;
						}
							
						if ($caption = $this->getCatalogAttribute($image->itemGuid, "fixedTitle"))
						{
							$fileImage[$i]['caption'] = strip_tags(trim($caption));
						}
					}
					
					$i++;
				}
				
				
				try {
					$this->addHitsBySolr(json_encode([[
							"id" => $row->guid,
							"fileImage" => ["set" => Zend_Json::encode($fileImage)]
						]]));
				}
				catch (Zend_Exception $e)
				{
					$this->log()->err($e->getMessage());
				}
				
			}
			
			echo "guid:[".$row->guid."][".$row->createdDate."]".$n."\n";
				
			flush();
		}
		
		
		sleep(1);
		
		echo "Update fileImage completed\n";
	}
	
	protected function addHitsBySolr($jsonData)
	{
		$indexing = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application-cli.ini','indexing');
		
		//$registry = Zend_Registry::getInstance();
		//$application=Zend_Registry::get(Pandamp_Keys::REGISTRY_APP_OBJECT);
	
		//$res=$application->getOption('resources')['indexing']['solr']['write'];
	
		//$link= $res["host"].":".$res["port"].$res["dir1"].'/update?commit=true';
		//$link= $res["host"].":".$res["port"].$res["dir1"].'/update?commitWithin=10000';
	
		$ch = curl_init('http://'.$indexing->solr->write->host.':'.$indexing->solr->write->port.$indexing->solr->write->dir1.'/update?commitWithin=10000');
		//$this->log()->info('http://'.$indexing->solr->write->host.':'.$indexing->solr->write->port.$indexing->solr->write->dir1.'/update?commitWithin=10000');
		//$ch = curl_init($link);
		//curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/json',
		'Content-Length: ' . strlen($jsonData))
		);
		return curl_exec($ch);
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
	
	protected function getDocumentById($catalogGuid, $relateAs, $multi=false, $order=null)
	{
		$db = $this->db;
	
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
	
		$sql = $db->select();
		$sql->from('KutuRelatedItem', '*');
		$sql->where('relatedGuid=?',$catalogGuid);
		$sql->where('relateAs=?',$relateAs);
		
		if ($order !== null) {
			$sql->order($order);
		}
		
		if ($multi)
			$row = $db->fetchAll($sql);
		else
			$row = $db->fetchRow($sql);
		
	
		return ($row) ? $row : '';
	}
	
	protected function getItemRelated($itemGuid, $relateAs)
	{
		$db = $this->db;
	
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
	
		$sql = $db->select();
		$sql->from('KutuRelatedItem', '*');
		$sql->where('itemGuid=?',$itemGuid);
		$sql->where('relateAs=?',$relateAs);
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
	
		//$sql = $sql->__toString();
	
		return ($row) ? $row->value : '';
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
	
	protected function log()
	{
		$logger = new Zend_Log();
	
		$writer = new Zend_Log_Writer_Stream(APPLICATION_PATH . "/../temp/log/application.log");
	
		// @TODO Filter only Log::CRIT
		//$filter = new Zend_Log_Filter_Priority(Zend_Log::CRIT);
		//$writer->addFilter($filter);
	
		$logger->addWriter($writer);
	
		return $logger;
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
}
