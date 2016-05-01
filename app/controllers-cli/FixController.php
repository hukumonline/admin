<?php
class FixController extends Application_Controller_Cli
{
	public function stAction()
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

			
			if ($row->profileGuid=='klinik')
				$title = $this->getCatalogAttribute($row->guid, 'fixedCommentTitle');
			else
				$title = $this->getCatalogAttribute($row->guid, 'fixedTitle');
			
			
			if ($title) {
				$slug = Pandamp_Utility_String::removeSign($title, '-', true);
				
				$db->update('KutuCatalog',['shortTitle'=>$slug],"guid='$row->guid'");
				
				try {
				
					//update document
					$this->addHitsBySolr(json_encode([[
							"id" => $row->guid,
							"shortTitle" => ["set" => $slug],
							"modifiedDate" => ["set" => date("Y-m-d\\TH:i:s\\Z")]
						]]));
				}
				catch (Zend_Exception $e)
				{
					throw new Zend_Exception($e->getMessage());
				}
			}
			
			echo "guid:[".$row->guid."][".$row->createdDate."]".$n."\n";
				
			flush();
		}
		
		sleep(1);
		
		echo "Fix st completed\n";
	}
	
	public function counterAction()
	{
		$request = $this->getRequest();
		$day = $request->getParam('q');
		$db = $this->db;
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
		$select = $db->select();
		$select->from('KutuCatalog', '*');
		$select->where("profileGuid NOT IN ('partner','narsum','author','kategoriklinik','comment','about_us','kutu_contact','kutu_email','kutu_kotik','kutu_mitra','kutu_signup')");
		$select->where("status=?",99);
		
		if ($day==1)
			$select->where("createdDate >= DATE_SUB(NOW(),INTERVAL 1 DAY)"); // NOW() return exactly to the second
		else
			$select->where("createdDate >= DATE_SUB(CURDATE(),INTERVAL $day DAY)"); // within the $day last
		
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
		
			try {
				
				$desktop = $this->getCountCatalog($row->guid, $row->profileGuid, 'desktop');
				$mobile = $this->getCountCatalog($row->guid, $row->profileGuid, 'mobile');
	
				//update document
				$this->addHitsBySolr(json_encode([[
						"id" => $row->guid,
						"desktop" => ["set" => $desktop],
						"mobile" => ["set" => $mobile]
					]]));
			}
			catch (Zend_Exception $e)
			{
				throw new Zend_Exception($e->getMessage());
			}
				
			echo "guid:[".$row->guid."]desktop:[".$desktop."]mobile:[".$mobile."][".$row->createdDate."]".$n."\n";
		
			flush();
		}
		
		sleep(1);
		
		echo "Fix st completed\n";
	}
	
	protected function addHitsBySolr($jsonData)
	{
		$indexing = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application-cli.ini','indexing');
	
		$ch = curl_init('http://'.$indexing->solr->write->host.':'.$indexing->solr->write->port.$indexing->solr->write->dir1.'/update?commitWithin=100000');
		curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/json',
		'Content-Length: ' . strlen($jsonData))
		);
		return curl_exec($ch);
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
		return ($row) ? $row->value : '';
	}
	
	private function getCountCatalog($guid, $profileGuid, $type)
	{
		$valueText=null;
		if (isset($profileGuid) && !in_array($profileGuid, array('partner','narsum','author','kategoriklinik','comment','about_us','kutu_contact','kutu_email','kutu_kotik','kutu_mitra','kutu_signup'))) {
			switch ($type) {
				case 'desktop':
					if (in_array($profileGuid, array('article','talks','isuhangat','kutu_agenda'))) {
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
					if (in_array($profileGuid, array('article','talks','isuhangat','kutu_agenda'))) {
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
}