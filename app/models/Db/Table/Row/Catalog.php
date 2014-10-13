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
class App_Model_Db_Table_Row_Catalog extends Zend_Db_Table_Row_Abstract
{
    protected function _insert()
    {
        if(empty($this->guid))
        {
            $guidMan = new Pandamp_Core_Guid();
            $this->guid = $guidMan->generateGuid();
        }

        /*
        if(!empty($this->shortTitle))
        {
            $sTitleLower = strtolower($this->shortTitle);
            $sTitleLower = preg_replace("/[^a-zA-Z0-9 s]/", "", $sTitleLower);
            $sTitleLower = str_replace(' ', '-', $sTitleLower);
            $this->shortTitle = $sTitleLower;
        }
        */

        $today = date('Y-m-d H:i:s');

        if(empty($this->createdDate))
            $this->createdDate = $today;
        if(empty($this->modifiedDate) || $this->modifiedDate=='0000-00-00 00:00:00')
            $this->modifiedDate = $today;

        $this->deletedDate = '0000-00-00 00:00:00';

        if(empty($this->createdBy))
        {
            $auth = Zend_Auth::getInstance();
            if($auth->hasIdentity())
            {
            	$identity = $auth->getIdentity();
                $this->createdBy = $identity->username;
            }
        }

        if(empty($this->modifiedBy))
            $this->modifiedBy = $this->createdBy;
        if(empty($this->status))
            $this->status = 0;
        
    }
    protected function _update()
    {
        $today = date('Y-m-d H:i:s');
        $this->modifiedDate = $today;

        $userName = '';
        $auth = Zend_Auth::getInstance();
        if($auth->hasIdentity())
        {
        	$identity = $auth->getIdentity();
            $userName = $identity->username;
        }

        $this->modifiedBy = $userName;
    }
    protected function _postDelete()
    {
        //find related docs and delete them
        $tblRelatedItem = new App_Model_Db_Table_RelatedItem();
        $rowsetRelatedDocs = $tblRelatedItem->fetchAll("relatedGuid='$this->guid' AND relateAs IN ('RELATED_FILE','RELATED_IMAGE','RELATED_VIDEO')");
        if(count($rowsetRelatedDocs))
        {
            foreach ($rowsetRelatedDocs as $rowRelatedDoc)
            {
                $tblCatalog = new App_Model_Db_Table_Catalog();
                $rowCatalog = $tblCatalog->find($rowRelatedDoc->itemGuid)->current();
                if ($rowCatalog) $rowCatalog->delete();
            }
        }

	    $registry = Zend_Registry::getInstance();
	    $config = $registry->get(Pandamp_Keys::REGISTRY_APP_OBJECT);
	    $cdn = $config->getOption('cdn');
	    
        if($this->profileGuid == 'kutu_doc')
        {
            //get parentGuid
            $tblRelatedItem = new App_Model_Db_Table_RelatedItem();
            $rowsetRelatedItem = $tblRelatedItem->fetchAll("itemGuid='$this->guid' AND relateAs IN ('RELATED_FILE','RELATED_IMAGE','RELATED_VIDEO')");
            if(count($rowsetRelatedItem))
            {
                foreach($rowsetRelatedItem as $rowRelatedItem)
                {
                    //must delete the physical files
                    $rowsetCatAtt = $this->findDependentRowsetCatalogAttribute();
                    $systemname = $rowsetCatAtt->findByAttributeGuid('docSystemName')->value;
                    $parentGuid = $rowRelatedItem->relatedGuid;

	                $sDir1 = $cdn['static']['dir']['files'].DIRECTORY_SEPARATOR.$systemname;
	                $sDir2 = $cdn['static']['dir']['files'].DIRECTORY_SEPARATOR.$parentGuid.DIRECTORY_SEPARATOR.$systemname;
	                
                    //$sDir1 = ROOT_DIR.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR.$systemname;
                    //$sDir2 = ROOT_DIR.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR.$parentGuid.DIRECTORY_SEPARATOR.$systemname;

                    if(file_exists($sDir1))
                    {
                        //delete file
                        unlink($sDir1);
                    }
                    else
                        if(file_exists($sDir2))
                        {
                            //delete file
                            unlink($sDir2);
                        }
                }
            }
        }

        //delete from table CatalogAttribute
        $tblCatalogAttribute = new App_Model_Db_Table_CatalogAttribute();
        $tblCatalogAttribute->delete("catalogGuid='$this->guid'");

        //delete catalogGuid from table CatalogFolder
        $tblCatalogFolder = new App_Model_Db_Table_CatalogFolder();
        $tblCatalogFolder->delete("catalogGuid='$this->guid'");

        //delete guid from table AssetSetting
        $tblAssetSetting = new App_Model_Db_Table_AssetSetting();
        $tblAssetSetting->delete("guid='$this->guid'");

        //delete from table relatedItem
        $tblRelatedItem = new App_Model_Db_Table_RelatedItem();
        $tblRelatedItem->delete("itemGuid='$this->guid'");
        $tblRelatedItem->delete("relatedGuid='$this->guid'");

        $indexingEngine = Pandamp_Search::manager();

        try {
            $hits = $indexingEngine->deleteCatalogFromIndex($this->guid);
        }
        catch (Exception $e)
        {

        }


        //delete physical catalog folder from uploads/files/[catalogGuid]
        $sDir = $cdn['static']['dir']['files'].DIRECTORY_SEPARATOR.$this->guid;
        //$sDir = ROOT_DIR.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR.$this->guid;
        try {
            if(is_dir($sDir))
                    rmdir($sDir);
        }
        catch (Exception $e)
        {

        }

        $sDir = $cdn['static']['dir']['images'];
        //$sDir = ROOT_DIR.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'images';
        try {
            if (file_exists($sDir."/".$this->guid.".gif")) 		{ unlink($sDir."/".$this->guid.".gif"); 	}
            if (file_exists($sDir."/tn_".$this->guid.".gif")) 	{ unlink($sDir."/tn_".$this->guid.".gif"); 	}
            if (file_exists($sDir."/".$this->guid.".jpg")) 		{ unlink($sDir."/".$this->guid.".jpg"); 	}
            if (file_exists($sDir."/tn_".$this->guid.".jpg")) 	{ unlink($sDir."/tn_".$this->guid.".jpg"); 	}
            if (file_exists($sDir."/".$this->guid.".jpeg")) 	{ unlink($sDir."/".$this->guid.".jpeg"); 	}
            if (file_exists($sDir."/tn_".$this->guid.".jpeg")) 	{ unlink($sDir."/tn_".$this->guid.".jpeg");     }
            if (file_exists($sDir."/".$this->guid.".png")) 		{ unlink($sDir."/".$this->guid.".png"); 	}
            if (file_exists($sDir."/tn_".$this->guid.".png")) 	{ unlink($sDir."/tn_".$this->guid.".png"); 	}
        }
        catch (Exception $e)
        {

        }
    }
    public function findDependentRowsetCatalogAttribute()
    {
        return $this->findDependentRowset('App_Model_Db_Table_CatalogAttribute');
    }
    public function relateTo($relatedGuid, $as='RELATED_ITEM', $valRelation = 0)
    {
        if(empty($this->guid))
            throw new Zend_Exception('Can not relate to empty GUID');
        if(empty($relatedGuid))
            throw new Zend_Exception('Can not relate to empty related GUID');

            
        $tblRelatedItem = new App_Model_Db_Table_RelatedItem();
        $rowsetRelatedItem = $tblRelatedItem->find($this->guid, $relatedGuid, $as);
        if(count($rowsetRelatedItem) > 0)
        {
            $row = $rowsetRelatedItem->current();
            $row->valueIntRelation = $valRelation;
        }
        else
        {
            $row = $tblRelatedItem->createNew();
            $row->itemGuid = $this->guid;
            $row->relatedGuid = $relatedGuid;
            $row->relateAs = $as;
            $row->valueIntRelation = $valRelation;
            $row->itemType = "history";
            
            if (in_array($as, array('REPEAL','AMEND','ISROOT'))) {
            	$tblRelatedItem = new App_Model_Db_Table_RelatedItem();
            	$rowVal = $tblRelatedItem->fetchRow("itemGuid='$relatedGuid'");
            	$row->valueStringRelation = ($rowVal->valueStringRelation)? $rowVal->valueStringRelation : $relatedGuid;
            }
        }
        $row->save();
    }
    public function copyToFolder($targetFolder)
    {
        $tblCatalogFolder = new App_Model_Db_Table_CatalogFolder();

        $rowset = $tblCatalogFolder->find($this->guid, $targetFolder);
        if(count($rowset))
        {
            //Catalog is already in the Target Folder.;
        }
        else
        {
            $row = $tblCatalogFolder->createRow();
            $row->catalogGuid = $this->guid;
            $row->folderGuid = $targetFolder;
            try
            {
                $row->save();
                return true;
            }
            catch (Exception $e)
            {
                throw new Zend_Exception($e->getMessage());
            }
        }
        return false;
    }
    public function moveToFolder($sourceFolder, $targetFolder)
    {
        $tblCatalogFolder = new App_Model_Db_Table_CatalogFolder();

        $this->copyToFolder($targetFolder);
        $tblCatalogFolder->delete("catalogGuid='$this->guid' AND folderGuid='$sourceFolder'");
    }
}
