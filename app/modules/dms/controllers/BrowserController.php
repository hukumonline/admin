<?php

class Dms_BrowserController extends Zend_Controller_Action 
{
    protected $_user;
    protected $_zl;

    function  preDispatch()
    {
        $auth = Zend_Auth::getInstance();

		$identity = Pandamp_Application::getResource('identity');
		
        $sReturn = "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
        $sReturn = base64_encode($sReturn);

        //$sso = new Pandamp_Session_Remote();
        //$user = $sso->getInfo();

        if (!$auth->hasIdentity()) {
            //$this->_forward('login','account','admin');
			$loginUrl = $identity->loginUrl;
			
			$this->_redirect($loginUrl.'?returnTo='.$sReturn);     
        }
        else
        {
            $this->_user = $auth->getIdentity();
            
            $this->_zl = Zend_Registry::get("Zend_Locale");
            
            $acl = Pandamp_Acl::manager();
            if (!$acl->checkAcl("site",'all','user', $this->_user->username, false,false))
            {
                $this->_redirect(ROOT_URL.'/'.$this->_zl->getLanguage().'/error/restricted');
            }
        }
    }
	function downloadFileAction()
	{
    	$this->_helper->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(TRUE);
    	
    	$catalogGuid = $this->_getParam('guid');
    	$parentGuid = $this->_getParam('parent');
    	
    	$tblCatalog = new App_Model_Db_Table_Catalog();
    	$rowsetCatalog = $tblCatalog->find($catalogGuid);
    	
    	if(count($rowsetCatalog))
    	{
    		$auth = Zend_Auth::getInstance();
    		if ($auth->hasIdentity())
    		{
    			$guidUser = $auth->getIdentity()->kopel;
    		}
    		
    		$tblAsetSetting = new App_Model_Db_Table_AssetSetting();
    		$rowAset = $tblAsetSetting->find($catalogGuid)->current();
    		if ($rowAset)
    		{
    			$rowAset->valueInt = $rowAset->valueInt + 1;
    		}
    		else 
    		{
    			$rowAset = $tblAsetSetting->fetchNew();
				$rowAset->guid = $catalogGuid;
				$rowAset->application = "kutu_doc";
				$rowAset->part = (isset($guidUser))? $guidUser : '';
				$rowAset->valueType = gethostbyaddr($_SERVER['REMOTE_ADDR']);
				$rowAset->valueInt = 1;
    		}
    		
    		$rowAset->save();
    		
    		$rowCatalog = $rowsetCatalog->current();
    		$rowsetCatAtt = $rowCatalog->findDependentRowsetCatalogAttribute();
    		
	    	$contentType = $rowsetCatAtt->findByAttributeGuid('docMimeType')->value;
			$filename = $systemname = $rowsetCatAtt->findByAttributeGuid('docSystemName')->value;
			$oriName = $oname = $rowsetCatAtt->findByAttributeGuid('docOriginalName')->value;
			
			$tblRelatedItem = new App_Model_Db_Table_RelatedItem();
			$rowsetRelatedItem = $tblRelatedItem->fetchAll("itemGuid='$catalogGuid' AND relateAs='RELATED_FILE'");
			
		    $registry = Zend_Registry::getInstance();
		    $config = $registry->get(Pandamp_Keys::REGISTRY_APP_OBJECT);
		    $cdn = $config->getOption('cdn');
		    
			$flagFileFound = false;
			
			foreach($rowsetRelatedItem as $rowRelatedItem)
			{
				if(!$flagFileFound)
				{
					$parentGuid = $rowRelatedItem->relatedGuid;
					$sDir1 = $cdn['static']['dir']['files']."/".$systemname;
					$sDir2 = $cdn['static']['dir']['files']."/".$parentGuid."/".$systemname;
					$sDir3 = $cdn['static']['dir']['files']."/".$oname;
					$sDir4 = $cdn['static']['dir']['files']."/".$parentGuid."/".$oname;
					
					if(file_exists($sDir1))
					{
						$flagFileFound = true;
						header("Content-type: $contentType");
						header("Content-Disposition: attachment; filename=$oriName");
						@readfile($sDir1);
						die();
					}
					else 
						if(file_exists($sDir2))
						{
							$flagFileFound = true;
							header("Content-type: $contentType");
							header("Content-Disposition: attachment; filename=$oriName");
							@readfile($sDir2);
							die();
						}
						if (file_exists($sDir3))
						{
							$flagFileFound = true;
							header("Content-type: $contentType");
							header("Content-Disposition: attachment; filename=$oriName");
							@readfile($sDir3);
							die();
						}
						if (file_exists($sDir4))
						{
							$flagFileFound = true;
							header("Content-type: $contentType");
							header("Content-Disposition: attachment; filename=$oriName");
							@readfile($sDir4);
							die();
						}
						else 
						{
							$flagFileFound = false;
							$this->_redirect(ROOT_URL.'/'.$this->_zl->getLanguage().'/dms/browser/forbidden');
						}
				}
			}
			
    	}
    	else 
    	{
    		$flagFileFound = false;
    		$this->_redirect(ROOT_URL.'/'.$this->_zl->getLanguage().'/dms/browser/forbidden');
    	}		
	}
	function forbiddenAction() 	
	{
		$this->_helper->layout->setLayout('administry');
	}
}