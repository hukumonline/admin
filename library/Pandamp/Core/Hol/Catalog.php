<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Catalog
 *
 * @author user
 */
class Pandamp_Core_Hol_Catalog
{
    public function save($aData)
    {
        if(empty($aData['profileGuid']))
            throw new Zend_Exception('Catalog Profile can not be EMPTY!');

            
        $profileGuid = $aData['profileGuid'];
            
        if ($profileGuid == 'klinik')
        	$title 	= $aData['fixedCommentTitle'];
        else 
        	$title 	= $aData['fixedTitle'];
     
        
        $slug = Pandamp_Utility_String::removeSign($title, '-', true);
        	
        $tblCatalog = new App_Model_Db_Table_Catalog();

        $gman = new Pandamp_Core_Guid();
        $catalogGuid = (isset($aData['guid']) && !empty($aData['guid']))? $aData['guid'] : $gman->generateGuid();
        $folderGuid = (isset($aData['folderGuid']) && !empty($aData['folderGuid']))? $aData['folderGuid'] : '';

        //if not empty, there are 2 possibilities
        $where = $tblCatalog->getAdapter()->quoteInto('guid=?', $catalogGuid);
        if($tblCatalog->fetchRow($where))
        {
            $rowCatalog = $tblCatalog->find($catalogGuid)->current();

            $rowCatalog->shortTitle = ($aData['shortTitle'])?$aData['shortTitle']:$slug;
            $rowCatalog->publishedDate = (isset($aData['publishedDate']))?$aData['publishedDate']:$rowCatalog->publishedDate;
            $rowCatalog->expiredDate = (isset($aData['expiredDate']))?$aData['expiredDate']:$rowCatalog->expiredDate;
            $rowCatalog->status = (isset($aData['status']))?$aData['status']:$rowCatalog->status;
            $rowCatalog->price = (isset($aData['price']))?$aData['price']:$rowCatalog->price;

        }
        else
        {
            $rowCatalog = $tblCatalog->fetchNew();

            $rowCatalog->guid = $catalogGuid;
            $rowCatalog->shortTitle = (isset($aData['shortTitle']))?$aData['shortTitle']:$slug;
            $rowCatalog->profileGuid = $profileGuid;
            $rowCatalog->publishedDate = (isset($aData['publishedDate']))? $aData['publishedDate']:'0000-00-00 00:00:00';
            $rowCatalog->expiredDate = (isset($aData['expiredDate']))? $aData['expiredDate'] : '0000-00-00 00:00:00';
            $rowCatalog->createdBy = (isset($aData['username']))?$aData['username']:'';
            $rowCatalog->modifiedBy = $rowCatalog->createdBy;
            $rowCatalog->createdDate = date("Y-m-d h:i:s");
            $rowCatalog->modifiedDate = $rowCatalog->createdDate;
            $rowCatalog->deletedDate = '0000-00-00 00:00:00';
            $rowCatalog->status = (isset($aData['status']))?$aData['status']:0;
            $rowCatalog->price = (isset($aData['price']))?$aData['price']:0;
        }
        try
        {
            $catalogGuid = $rowCatalog->save();
        }
        catch (Exception $e)
        {
            die($e->getMessage());
        }

        $tableProfileAttribute = new App_Model_Db_Table_ProfileAttribute();
        //$profileGuid = $rowCatalog->profileGuid;
        $where = $tableProfileAttribute->getAdapter()->quoteInto('profileGuid=?', $profileGuid);
        $rowsetProfileAttribute = $tableProfileAttribute->fetchAll($where,'viewOrder ASC');

        $rowsetCatalogAttribute = $rowCatalog->findDependentRowsetCatalogAttribute();
        foreach ($rowsetProfileAttribute as $rowProfileAttribute)
        {
            if($rowsetCatalogAttribute->findByAttributeGuid($rowProfileAttribute->attributeGuid))
            {
                $rowCatalogAttribute = $rowsetCatalogAttribute->findByAttributeGuid($rowProfileAttribute->attributeGuid);
            }
            else
            {
                $tblCatalogAttribute = new App_Model_Db_Table_CatalogAttribute();
                $rowCatalogAttribute = $tblCatalogAttribute->fetchNew();
                $rowCatalogAttribute->catalogGuid = $catalogGuid;
                $rowCatalogAttribute->attributeGuid = $rowProfileAttribute->attributeGuid;

            }

            $rowCatalogAttribute->value = (isset($aData[$rowProfileAttribute->attributeGuid]))?$aData[$rowProfileAttribute->attributeGuid]:'';

            $rowCatalogAttribute->save();
        }

        //save to table CatalogFolder only if folderGuid is not empty
        if (!empty($folderGuid))
        {
            $rowCatalog->copyToFolder($folderGuid);
        }

        //do indexing
        $indexingEngine = Pandamp_Search::manager();
        $indexingEngine->indexCatalog($catalogGuid);

        // create shortener url
        $kopel 	= Zend_Auth::getInstance()->getIdentity()->kopel;
        
        $cfg 	= Pandamp_Config::getConfig();
        
		$config = Pandamp_Application::getOption('resources');
		$indexingConfig = $config['indexing']['solr']['write'];
		$hukumn = new Pandamp_Search_Adapter_Solrh($indexingConfig['host'], $indexingConfig['port'], $indexingConfig['dir3']);
		
		$url_content = $cfg->web->url->base.'/berita/baca/'.$catalogGuid.'/'.$slug;
		
		//$q = "url:\"".$url_content."\" kopel:".$kopel;
		$q = "url:\"".$url_content."\"";
		
		$db = Zend_Registry::get('db4');
		
		$data = array('url' => $url_content,
		 			  'createdate' => date("Y-m-d h:i:s"),
					  'remoteip' => Pandamp_Lib_Formater::getHttpRealIp(),
					  'kopel' => $kopel);
					  
		$hits = $hukumn->find($q,0,1);
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
		
		// indexing shortener url
		$hukumn->indexCatalog($hid);
		        
        
		try {
			
		    $url = 'http://developers.facebook.com/tools/lint/?url={'.$url_content.'}&format=json';
		    $ch = curl_init($url);
		    curl_setopt($ch, CURLOPT_NOBODY, true);
		    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		    curl_exec($ch);
		    $retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		    curl_close($ch);
			
		}
		catch (Exception $e)
		{
			//die($e->getMessage());
		}
		
		/**
		 * Updated Dec 10, 2013 06:01
		 */
		$dir = $cfg->cdn->hol->static->url . DS . 'temp' . DS . 'cache';
		Pandamp_Core_FileCache::clear($dir . DS . 'url');
		Pandamp_Core_FileCache::clear($dir . DS . 'action');
		
		
		
        //after indexing, update isIndex and indexedDate in table KutuCatalog
        return $catalogGuid;
    }
    
    public function getFolders($catalogGuid)
    {
        $tblCatalog = new App_Model_Db_Table_Catalog();
        $rowCatalog = $tblCatalog->find($catalogGuid)->current();
        $rowsetFolder = $rowCatalog->findManyToManyRowset('App_Model_Db_Table_Folder', 'App_Model_Db_Table_CatalogFolder');

        return $rowsetFolder;
    }
    public function delete($catalogGuid)
    {
        $tblRelatedItem = new App_Model_Db_Table_RelatedItem();

        $tblCatalog = new App_Model_Db_Table_Catalog;
        $rowset = $tblCatalog->find($catalogGuid);
        if(count($rowset))
        {
            $row = $rowset->current();
            $profileGuid = $row->profileGuid;

            if($row->profileGuid == 'kutu_doc')
            {
                $rowRelatedItem = $tblRelatedItem->fetchRow("itemGuid='$row->guid' AND relateAs IN ('RELATED_FILE','RELATED_IMAGE','RELATED_VIDEO')");
            }

            $row->delete();

            //if deleted catalog is kutu_doc then re-index its parentGuid
            if($profileGuid == 'kutu_doc')
            {
                $indexingEngine = Pandamp_Search::manager();
                $indexingEngine->indexCatalog($rowRelatedItem->relatedGuid);
            }
        }

    }
    public function recycle($catalogGuid)
    {
    	$tblRelatedItem = new App_Model_Db_Table_RelatedItem();
        $tblCatalog = new App_Model_Db_Table_Catalog;
        $rowset = $tblCatalog->find($catalogGuid);
        if(count($rowset))
        {
            $row = $rowset->current();
            
            $profileGuid = $row->profileGuid;

			if($profileGuid !== 'kutu_doc') {
				$row->status = -2;                                          
				
            	$row->save();
            
	            $indexingEngine = Pandamp_Search::manager();
	            $indexingEngine->indexCatalog($row->guid);
			}
            
        }
    }
    public function restore($catalogGuid, $status)
    {
        $tblCatalog = new App_Model_Db_Table_Catalog;
        $rowset = $tblCatalog->find($catalogGuid);
        if(count($rowset))
        {
            $row = $rowset->current();
            
            $profileGuid = $row->profileGuid;

			if($profileGuid !== 'kutu_doc') {
				$row->status = $status;
				
            	$row->save();
            
	            $indexingEngine = Pandamp_Search::manager();
	            $indexingEngine->indexCatalog($row->guid);
			}
            
        }
    }
	public function changeUploadFile($aDataCatalog, $relatedGuid)
	{
		if($aDataCatalog['profileGuid']!='kutu_doc')
			throw new Zend_Exception('Profile does not match profile for FILE');
		
		if(empty($relatedGuid))
			throw new Zend_Exception('No RELATED GUID specified!');
		
		$id = 1 + ($aDataCatalog['id'] - 1);
		
		for ($x=1;$x <= $id; $x++) {
			$registry = Zend_Registry::getInstance();
			$files = $registry->get('files');
			
			if (isset($files['uploadedFile'.$x]))
			{
				$file = $files['uploadedFile'.$x];
			}
			
			$itemGuid = ($aDataCatalog['itemGuid'.$x])? $aDataCatalog['itemGuid'.$x] : '';
			
			$tblCatalog = new App_Model_Db_Table_Catalog();
			$rowset = $tblCatalog->find($itemGuid)->current();
			
			if (isset($rowset)) {
				$rowsetCatAtt = $rowset->findDependentRowsetCatalogAttribute();
				if (isset($rowsetCatAtt))
				{
					$systemname = $rowsetCatAtt->findByAttributeGuid('docSystemName')->value;
					$oriName = $rowsetCatAtt->findByAttributeGuid('docOriginalName')->value;
					
					$parentGuid = $relatedGuid;
					
					$fileBaru = strtoupper(str_replace(' ','_',$file['name']));
					
					if ($oriName !== $fileBaru)
					{
						echo "File $fileBaru tidak sama dengan file aslinya\n";
					}
					else 
					{
					    $registry = Zend_Registry::getInstance();
					    $config = $registry->get(Pandamp_Keys::REGISTRY_APP_OBJECT);
					    $cdn = $config->getOption('cdn');
					    
					    $sDir 	= $cdn['static']['dir']['files'].DIRECTORY_SEPARATOR.$parentGuid;
						$sDir1 	= $cdn['static']['dir']['files'].DIRECTORY_SEPARATOR.$systemname;
						$sDir2 	= $cdn['static']['dir']['files'].DIRECTORY_SEPARATOR.$parentGuid.DIRECTORY_SEPARATOR.$systemname;
					    
						//$sDir 	= ROOT_DIR.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR.$parentGuid;
						//$sDir1 	= ROOT_DIR.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR.$systemname;
						//$sDir2 	= ROOT_DIR.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR.$parentGuid.DIRECTORY_SEPARATOR.$systemname;
						
						if(file_exists($sDir1)) { unlink($sDir1); }
						if(file_exists($sDir2)) { unlink($sDir2); }
						
						if(is_dir($sDir))
				    	{
				    		move_uploaded_file($file['tmp_name'], $sDir . DIRECTORY_SEPARATOR . $fileBaru);
				    	}
				    	else 
				    	{
				    		if(mkdir($sDir))
				    		{
				    			move_uploaded_file($file['tmp_name'], $sDir . DIRECTORY_SEPARATOR . $fileBaru);
				    		}
				    		else 
				    		{
				    			move_uploaded_file($file['tmp_name'], ROOT_DIR.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR . $fileBaru);
				    		}
				    	}
						
					}
				}
			}
		}
	}
    public function uploadFile($aDataCatalog, $relatedGuid)
    {
        if($aDataCatalog['profileGuid']!='kutu_doc')
            throw new Zend_Exception('Profile does not match profile for FILE');

        if(empty($relatedGuid))
            throw new Zend_Exception('No RELATED GUID specified!');

	    $registry = Zend_Registry::getInstance();
	    $config = $registry->get(Pandamp_Keys::REGISTRY_APP_OBJECT);
	    $cdn = $config->getOption('cdn');
	    
        $id = 1 + ($aDataCatalog['id'] - 1);

        for ($x=1;$x < $id; $x++) {
            $title = ($aDataCatalog['fixedTitle'.$x])? $aDataCatalog['fixedTitle'.$x] : '';

            $registry = Zend_Registry::getInstance();
            $files = $registry->get('files');

            if(isset($files['uploadedFile'.$x]))
            {
                    $file = $files['uploadedFile'.$x];
                    //$this->checkTitle($aDataCatalog['fixedTitle'.$x]);
                    $this->checkSize($file['size'],$aDataCatalog['fixedTitle'.$x]);
            }

            $type = ($aDataCatalog['fixedType'.$x])? $aDataCatalog['fixedType'.$x] : '';

            if ($type == 'file')
                    $relatedType = 'RELATED_FILE';
            elseif ($type == 'image')
                    $relatedType = 'RELATED_IMAGE';
            elseif ($type == 'video')
                    $relatedType = 'RELATED_VIDEO';

            $tblCatalog = new App_Model_Db_Table_Catalog();

            $gman = new Pandamp_Core_Guid();
            $catalogGuid = (isset($aDataCatalog['guid']) && !empty($aDataCatalog['guid']))? $aDataCatalog['guid'] : $gman->generateGuid();
            $folderGuid = (isset($aDataCatalog['folderGuid']) && !empty($aDataCatalog['folderGuid']))? $aDataCatalog['folderGuid'] : '';

            $where = $tblCatalog->getAdapter()->quoteInto('guid=?', $catalogGuid);
            if($tblCatalog->fetchRow($where))
            {
                $rowCatalog = $tblCatalog->find($catalogGuid)->current();

                $rowCatalog->shortTitle = (isset($aDataCatalog['shortTitle']))?$aDataCatalog['shortTitle']:$rowCatalog->shortTitle;
                $rowCatalog->publishedDate = (isset($aDataCatalog['publishedDate']))?$aDataCatalog['publishedDate']:$rowCatalog->publishedDate;
                $rowCatalog->expiredDate = (isset($aDataCatalog['expiredDate']))?$aDataCatalog['expiredDate']:$rowCatalog->expiredDate;
                $rowCatalog->status = (isset($aDataCatalog['status']))?$aDataCatalog['status']:$rowCatalog->status;
            }
            else
            {
                $rowCatalog = $tblCatalog->fetchNew();

                $rowCatalog->guid = $catalogGuid;
                $rowCatalog->shortTitle = (isset($aDataCatalog['shortTitle']))?$aDataCatalog['shortTitle']:'';
                $rowCatalog->profileGuid = $aDataCatalog['profileGuid'];
                $rowCatalog->publishedDate = (isset($aDataCatalog['publishedDate']))?$aDataCatalog['publishedDate']:'0000-00-00 00:00:00';
                $rowCatalog->expiredDate = (isset($aDataCatalog['expiredDate']))?$aDataCatalog['expiredDate']:'0000-00-00 00:00:00';
                $rowCatalog->createdBy = (isset($aDataCatalog['username']))?$aDataCatalog['username']:'';
                $rowCatalog->modifiedBy = $rowCatalog->createdBy;
                $rowCatalog->createdDate = date("Y-m-d h:i:s");
                $rowCatalog->modifiedDate = $rowCatalog->createdDate;
                $rowCatalog->deletedDate = '0000-00-00 00:00:00';
                $rowCatalog->status = (isset($aDataCatalog['status']))?$aDataCatalog['status']:0;
            }

            $catalogGuid = $rowCatalog->save();

            $rowsetCatalogAttribute = $rowCatalog->findDependentRowsetCatalogAttribute();

            if(isset($files['uploadedFile'.$x]))
            {
                    if(isset($files['uploadedFile'.$x]['name']) && !empty($files['uploadedFile'.$x]['name']))
                    {
                        $this->_updateCatalogAttribute($rowsetCatalogAttribute, $catalogGuid, 'docSystemName', strtoupper(str_replace(' ','_',$file['name'])));
                        $this->_updateCatalogAttribute($rowsetCatalogAttribute, $catalogGuid, 'docOriginalName', strtoupper(str_replace(' ','_',$file['name'])));
                        $this->_updateCatalogAttribute($rowsetCatalogAttribute, $catalogGuid, 'docSize', $file['size']);
                        $this->_updateCatalogAttribute($rowsetCatalogAttribute, $catalogGuid, 'docMimeType', $file['type']);
                        $this->_updateCatalogAttribute($rowsetCatalogAttribute, $catalogGuid, 'fixedTitle', $title);
                        $this->_updateCatalogAttribute($rowsetCatalogAttribute, $catalogGuid, 'docViewOrder', 0);
                        if ($type == 'file')
                        {
                                //$sDir = ROOT_DIR.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR.$relatedGuid;
                                $sDir = $cdn['static']['dir']['files'].DIRECTORY_SEPARATOR.$relatedGuid;
                                if(is_dir($sDir))
                        {
                                move_uploaded_file($file['tmp_name'], $sDir . DIRECTORY_SEPARATOR . strtoupper(str_replace(' ','_',$file['name'])));
                        }
                        else
                        {
                                if(mkdir($sDir))
                                {
                                        move_uploaded_file($file['tmp_name'], $sDir . DIRECTORY_SEPARATOR . strtoupper(str_replace(' ','_',$file['name'])));
                                }
                                else
                                {
                                        move_uploaded_file($file['tmp_name'], $cdn['static']['dir']['files'].DIRECTORY_SEPARATOR . strtoupper(str_replace(' ','_',$file['name'])));
                                }
                        }
                        }
                        elseif ($type == 'image')
                        {
                        		$sDir = $cdn['static']['dir']['images'];
                                //$sDir = ROOT_DIR.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'images';
                                $file = $files['uploadedFile'.$x]['name'];
                                $ext = explode(".",$file);
                                $ext = strtolower(array_pop($ext));

                                if ($ext == "jpg" || $ext == "jpeg" || $ext == "gif" || $ext == "png")
                                {
                                    //$target_path = $sDir.DIRECTORY_SEPARATOR.$catalogGuid.".".$ext;
                                    $thumb_mode = $sDir.DIRECTORY_SEPARATOR.$catalogGuid.".".$ext;
                                    $thumb = $sDir.DIRECTORY_SEPARATOR.$relatedGuid.DIRECTORY_SEPARATOR.$catalogGuid.".".$ext;
                                    $target_path = $sDir.DIRECTORY_SEPARATOR.$relatedGuid;

                                    if(is_dir($target_path))
                                    {
                                        move_uploaded_file($files['uploadedFile'.$x]['tmp_name'], $target_path. DIRECTORY_SEPARATOR . $catalogGuid . "." .$ext);
                                        //chmod($target_path,0644);
                                        Pandamp_Lib_Formater::createthumb($thumb,$target_path.'/tn_'.$catalogGuid.".".$ext,130,130);
                                    }
                                    else
                                    {
                                        if(mkdir($target_path))
                                        {
                                            move_uploaded_file($files['uploadedFile'.$x]['tmp_name'], $target_path. DIRECTORY_SEPARATOR . $catalogGuid . "." .$ext);
                                            //chmod($target_path,0644);
                                            Pandamp_Lib_Formater::createthumb($thumb,$target_path.'/tn_'.$catalogGuid.".".$ext,130,130);
                                        }
                                        else
                                        {
                                            move_uploaded_file($files['uploadedFile'.$x]['tmp_name'], $sDir.DIRECTORY_SEPARATOR.$catalogGuid.".".$ext);
                                            //chmod($target_path,0644);
                                            Pandamp_Lib_Formater::createthumb($thumb_mode,$sDir.'/tn_'.$catalogGuid.".".$ext,130,130);
                                        }
                                    }

                                }
                        }
                        elseif ($type == 'video')
                        {
                        	$sDir = $cdn['static']['dir']['video'].DIRECTORY_SEPARATOR.$relatedGuid;
                            //$sDir = ROOT_DIR.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'video'.DIRECTORY_SEPARATOR.$relatedGuid;
                            if(is_dir($sDir))
                            {
                                move_uploaded_file($file['tmp_name'], $sDir . DIRECTORY_SEPARATOR . strtoupper(str_replace(' ','_',$file['name'])));
                            }
                        else
                        {
                            if(mkdir($sDir))
                            {
                                move_uploaded_file($file['tmp_name'], $sDir . DIRECTORY_SEPARATOR . strtoupper(str_replace(' ','_',$file['name'])));
                            }
                            else
                            {
                                move_uploaded_file($file['tmp_name'], $cdn['static']['dir']['video'].DIRECTORY_SEPARATOR . strtoupper(str_replace(' ','_',$file['name'])));
                            }
                        }
                        }

                    }
            }

            $this->relateTo($catalogGuid, $relatedGuid, $relatedType);

            $indexingEngine = Pandamp_Search::manager();
            $indexingEngine->indexCatalog($catalogGuid);
        }
    }
    public function relateTo($itemGuid, $relatedGuid, $as='RELATED_ITEM', $valRelation = 0)
    {
        $tblRelatedItem = new App_Model_Db_Table_RelatedItem();

        if(empty($itemGuid))
            throw new Zend_Exception('Can not relate to empty GUID');

        $rowsetRelatedItem = $tblRelatedItem->find($itemGuid, $relatedGuid, $as);
        if(count($rowsetRelatedItem) > 0)
        {
            $row = $rowsetRelatedItem->current();
            $row->valueIntRelation = $valRelation;
        }
        else
        {
            $row = $tblRelatedItem->createNew();
            $row->itemGuid = $itemGuid;
            $row->relatedGuid = $relatedGuid;
            $row->relateAs = $as;
            $row->valueIntRelation = $valRelation;
        }
        
        $row->save();
    }
	public function removeFromFolder($catalogGuid, $folderGuid)
	{
		$tblCatalogFolder = new App_Model_Db_Table_CatalogFolder();
		$rowset = $tblCatalogFolder->fetchAll("catalogGuid='$catalogGuid'");
		if(count($rowset)>1)
		{
			try
			{
				$tblCatalogFolder->delete("catalogGuid='$catalogGuid' AND folderGuid='$folderGuid'");
			}
			catch (Exception $e)
			{
				throw new Zend_Exception($e->getMessage());
			}
		}
		else
		{
			throw new Zend_Exception("Can not remove from the only FOLDER.");
		}
	}
    protected function _updateCatalogAttribute($rowsetCatalogAttribute,$catalogGuid,$attributeGuid, $value)
    {
        if($rowsetCatalogAttribute->findByAttributeGuid($attributeGuid))
        {
            $rowCatalogAttribute = $rowsetCatalogAttribute->findByAttributeGuid($attributeGuid);
        }
        else
        {
            $tblCatalogAttribute = new App_Model_Db_Table_CatalogAttribute();
            $rowCatalogAttribute = $tblCatalogAttribute->fetchNew();
            $rowCatalogAttribute->catalogGuid = $catalogGuid;
            $rowCatalogAttribute->attributeGuid = $attributeGuid;
        }

        $rowCatalogAttribute->value = $value;
        $rowCatalogAttribute->save();
    }
    private function checkSize($size,$title)
    {
    	// If the file is larger than 300kb
    	if ($size > 300000) {
    		echo "[$title] Sorry, your file is too large.";
    		exit();
    	}
    }
    private function checkTitle($title)
    {
        $zl = Zend_Registry::get("Zend_Locale");
        if ($zl->getLanguage() == 'id')
            $conn = Zend_Registry::get('db1');
        else
            $conn = Zend_Registry::get('db3');

        $db = $conn->query("SELECT * FROM KutuCatalog, KutuCatalogAttribute
                WHERE KutuCatalogAttribute.attributeGuid = 'fixedTitle'
                AND KutuCatalogAttribute.value = '$title'
                AND KutuCatalog.guid = KutuCatalogAttribute.catalogGuid");

        $rowset = $db->fetchAll(Zend_Db::FETCH_OBJ);

        if ($rowset)
        {
                echo $title.' is not available';
                exit();
        }

        /*
        $indexingEngine = Pandamp_Search::manager();
        $hits = $indexingEngine->find("title:$title");
        $solrNumFound = count($hits->response->docs);

        if ($solrNumFound !== 0)
        {
                echo $title.' is not available';
                exit();
        }
        */
    }

}
