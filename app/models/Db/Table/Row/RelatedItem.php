<?php

/**
 * Description of RelatedItem
 *
 * @author nihki <nihki@madaniyah.com>
 */
class App_Model_Db_Table_Row_RelatedItem extends Zend_Db_Table_Row_Abstract
{
    protected function  _postInsert() {
        parent::_postInsert();
    }
    protected function  _postUpdate() {
        parent::_postUpdate();
    }
    protected function _postDelete()
    {
	    $registry = Zend_Registry::getInstance();
	    $config = $registry->get(Pandamp_Keys::REGISTRY_APP_OBJECT);
	    $cdn = $config->getOption('cdn');
	    
        //find related docs and delete them
        $tblRelatedItem = new App_Model_Db_Table_RelatedItem();
        $rowsetRelatedDocs = $tblRelatedItem->fetchAll("relatedGuid='$this->relatedGuid' AND relateAs IN ('RELATED_FILE','RELATED_IMAGE','RELATED_VIDEO')");
        if(count($rowsetRelatedDocs))
        {
            foreach ($rowsetRelatedDocs as $rowRelatedDoc)
            {
                $systemname = App_Model_Show_CatalogAttribute::show()->getCatalogAttributeValue($rowRelatedDoc->itemGuid,'docSystemName');
                $parentGuid = $rowRelatedDoc->relatedGuid;

                $sDir1 = $cdn['static']['dir']['images'].DIRECTORY_SEPARATOR.$systemname;
                $sDir2 = $cdn['static']['dir']['images'].DIRECTORY_SEPARATOR.$parentGuid.DIRECTORY_SEPARATOR.$systemname;
                
                //$sDir1 = ROOT_DIR.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.$systemname;
                //$sDir2 = ROOT_DIR.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.$parentGuid.DIRECTORY_SEPARATOR.$systemname;

                if(file_exists($sDir1))
                {
                    unlink($sDir1);
                }
                else
                    if(file_exists($sDir2))
                    {
                        unlink($sDir2);
                    }


                $systemname = $rowRelatedDoc->itemGuid;
                $parentGuid = $rowRelatedDoc->relatedGuid;

                $sDir = $cdn['static']['dir']['images'];
                $sDir2 = $cdn['static']['dir']['images'].DIRECTORY_SEPARATOR.$parentGuid;
                
                //$sDir = ROOT_DIR.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'images';
                //$sDir2 = ROOT_DIR.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.$parentGuid;

                if (file_exists($sDir."/".$systemname.".gif"))      { unlink($sDir."/".$systemname.".gif"); 	}
                if (file_exists($sDir."/tn_".$systemname.".gif"))   { unlink($sDir."/tn_".$systemname.".gif"); 	}
                if (file_exists($sDir."/".$systemname.".jpg"))      { unlink($sDir."/".$systemname.".jpg"); 	}
                if (file_exists($sDir."/tn_".$systemname.".jpg"))   { unlink($sDir."/tn_".$systemname.".jpg"); 	}
                if (file_exists($sDir."/".$systemname.".jpeg"))     { unlink($sDir."/".$systemname.".jpeg"); 	}
                if (file_exists($sDir."/tn_".$systemname.".jpeg"))  { unlink($sDir."/tn_".$systemname.".jpeg"); }
                if (file_exists($sDir."/".$systemname.".png"))      { unlink($sDir."/".$systemname.".png"); 	}
                if (file_exists($sDir."/tn_".$systemname.".png"))   { unlink($sDir."/tn_".$systemname.".png"); 	}

                if (file_exists($sDir2."/".$systemname.".gif"))      { unlink($sDir2."/".$systemname.".gif"); 	}
                if (file_exists($sDir2."/tn_".$systemname.".gif"))   { unlink($sDir2."/tn_".$systemname.".gif"); 	}
                if (file_exists($sDir2."/".$systemname.".jpg"))      { unlink($sDir2."/".$systemname.".jpg"); 	}
                if (file_exists($sDir2."/tn_".$systemname.".jpg"))   { unlink($sDir2."/tn_".$systemname.".jpg"); 	}
                if (file_exists($sDir2."/".$systemname.".jpeg"))     { unlink($sDir2."/".$systemname.".jpeg"); 	}
                if (file_exists($sDir2."/tn_".$systemname.".jpeg"))  { unlink($sDir2."/tn_".$systemname.".jpeg"); }
                if (file_exists($sDir2."/".$systemname.".png"))      { unlink($sDir2."/".$systemname.".png"); 	}
                if (file_exists($sDir2."/tn_".$systemname.".png"))   { unlink($sDir2."/tn_".$systemname.".png"); 	}


                $tblCatalog = new App_Model_Db_Table_Catalog();
                $rowCatalog = $tblCatalog->find($rowRelatedDoc->itemGuid)->current();
                $rowCatalog->delete();
            }
        }
        
    }
}
