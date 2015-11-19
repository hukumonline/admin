<?php

class ImageController extends Application_Controller_Cli
{
	public function newlocAction()
	{
		$request = $this->getRequest();
		$query = $request->getParam('q');
		
		$size = [
			'square' => 'crop_99_103', //legalpanelsidecontent
			'thumbnail' => 'resize_100_53', //terbaru
			'multimedia' => 'resize_245_169', //multimediabxslider
			'small' => 'resize_213_142', //klinik
			'headsmall' => 'resize_213_160', //header berita
			'crop' => 'crop_324_169', //ijt
			'cropnext' => 'crop_325_183', //nextevent
			'mainhead' => 'resize_462_309', //utama
			'medium' => 'resize_646_431' //detailberita
		];
		
		$tool = 'gd';
		
		$sizes 	= array();
		foreach ($size as $key => $value) {
			list($method, $width, $height) = explode('_', $value);
			$sizes[$key] = array('method' => $method, 'width' => $width, 'height' => $height);
		}
		
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
			
			$rowsetRelatedItem = $this->getDocumentById($row->guid, 'RELATED_IMAGE');
			if ($rowsetRelatedItem) {
				//get image url
				$rowDocSystemName = $this->getCatalogAttribute($rowsetRelatedItem->itemGuid, 'docSystemName');
				if ($rowDocSystemName)
				{
					$ext = pathinfo($rowDocSystemName,PATHINFO_EXTENSION);
					$ext = strtolower($ext);

					$image = $this->giu($row->guid, $rowsetRelatedItem->itemGuid, $ext, null, "remote");
					
					$cdn = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini','cdn');
					
					$dir = $cdn->static->dir->images . DIRECTORY_SEPARATOR . 'upload';
					
					$catalogDb = $this->getCatalog($rowsetRelatedItem->itemGuid, ['createdBy','createdDate']);
					
					$path = implode(DS, array(strip_tags(trim($catalogDb->createdBy)), date('Y',strtotime($catalogDb->createdDate)), date('m',strtotime($catalogDb->createdDate)), date('d',strtotime($catalogDb->createdDate))));
					Pandamp_Utility_File::createDirs($dir, $path);
					
					//$fileName  = uniqid('lt');
					$fileName  = $rowsetRelatedItem->itemGuid;
					$fileku	   = $dir . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . $fileName . '.' . $ext;
					
					file_put_contents($fileku, file_get_contents($image));
					
					/**
					 * Generate thumbnails
					 */
					$thumbnailSizes = array_keys($sizes);
						
					$service = null;
					$service = new Pandamp_Image_GD();
					
					$baseUrl = $cdn->static->url->images;
					
					//mulai optimasi
					foreach ($thumbnailSizes as $s) {
						$service->setFile($image);
						$method = $sizes[$s]['method'];
						$width 	= $sizes[$s]['width'];
						$height = $sizes[$s]['height'];
							
						$f 		 = $fileName . '_' . $s . '.' . $ext;
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
						
						//beritahu nama file baru catalogAttribute
						$db->update('KutuCatalogAttribute',['value' => $fileName . '.' . $ext],"catalogGuid='$rowsetRelatedItem->itemGuid' AND attributeGuid='docSystemName'");
						
						
						/*try {
							$this->addHitsBySolr(json_encode([[
									"id" => $rowsetRelatedItem->itemGuid,
									"fileName" => ["set" => $fileName . '.' . $ext],
									"modifiedDate" => ["set" => date("Y-m-d\\TH:i:s\\Z")]
								]]));
						}
						catch (Zend_Exception $e)
						{
							
						}*/
						
					}
					
					
				}
				
			}
			
		}
		
		sleep(1);
		
		echo "Images optimizing completed\n";
	}
	
	protected function addHitsBySolr($jsonData)
	{
		//$registry = Zend_Registry::getInstance();
		//$application=Zend_Registry::get(Pandamp_Keys::REGISTRY_APP_OBJECT);
	
		//$res=$application->getOption('resources')['indexing']['solr']['write'];
	
		//$link= $res["host"].":".$res["port"].$res["dir1"].'/update?commit=true';
		//$link= $res["host"].":".$res["port"].$res["dir1"].'/update?commitWithin=10000';
	
		$ch = curl_init('nihki:sirkulasi@localhost:8983/solr/corehol/update?commit=true');
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
	
	protected function getDocumentById($catalogGuid, $relateAs)
	{
		$db = $this->db;
	
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
	
		$sql = $db->select();
		$sql->from('KutuRelatedItem', '*');
		$sql->where('relatedGuid=?',$catalogGuid);
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
		$config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini','cdn');
	
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
}
