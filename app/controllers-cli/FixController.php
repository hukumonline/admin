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
			
			$title = $this->getCatalogAttribute($row->guid, 'fixedCommentTitle');
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
	
	protected function addHitsBySolr($jsonData)
	{
		$indexing = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application-cli.ini','indexing');
	
		$ch = curl_init('http://'.$indexing->solr->write->host.':'.$indexing->solr->write->port.$indexing->solr->write->dir1.'/update?commitWithin=10000');
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
}