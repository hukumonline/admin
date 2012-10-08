<?php

class Dms_BrowserController extends Zend_Controller_Action 
{
    protected $_user;
    protected $_zl;

    function  preDispatch()
    {
        $auth = Zend_Auth::getInstance();

		$identity = Pandamp_Application::getResource('identity');

		$loginUrl = $identity->loginUrl;
		
		/*
		$multidb = Pandamp_Application::getResource('multidb');
		$multidb->init();
		
		$db = $multidb->getDb('db2');
		*/
		
        $sReturn = "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
        $sReturn = base64_encode($sReturn);

        //$sso = new Pandamp_Session_Remote();
        //$user = $sso->getInfo();

        if (!$auth->hasIdentity()) {
            //$this->_forward('login','account','admin');
			
			$this->_redirect($loginUrl.'?returnUrl='.$sReturn);     
        }
        else
        {
            $this->_user = $auth->getIdentity();
            
            $this->_zl = Zend_Registry::get("Zend_Locale");
            
            $acl = Pandamp_Acl::manager();
            if (!$acl->checkAcl("site",'all','user', $this->_user->username, false,false))
            {
                //$this->_redirect(ROOT_URL.'/'.$this->_zl->getLanguage().'/error/restricted');
                $this->_forward('restricted','error','admin',array('lang'=>$this->_zl->getLanguage()));
            }
            
			// [TODO] else: check if user has access to admin page and status website is online
			$tblSetting = new App_Model_Db_Table_Setting();
			$rowset = $tblSetting->find(1)->current();
			
			if ($rowset)
			{
				if (($rowset->status == 1 && $this->_zl->getLanguage() == 'id') || ($rowset->status == 2 && $this->_zl->getLanguage() == 'en') || ($rowset->status == 3))
				{
					// it means that user offline other than admin
					$aReturn = App_Model_Show_AroGroup::show()->getUserGroup($this->_user->packageId);
					
					if (isset($aReturn['name']))
					{
						//if (($aReturn[1] !== "admin"))
						if (($aReturn['name'] !== "Master") && ($aReturn['name'] !== "Super Admin"))
						{
							$this->_forward('temporary','error','admin'); 
						}
					}
				}
			}
			
			// check session expire
			/*
			$timeLeftTillSessionExpires = $_SESSION['__ZF']['Zend_Auth']['ENT'] - time();

			if (Pandamp_Lib_Formater::diff('now', $this->_user->dtime) > $timeLeftTillSessionExpires) {
				$db->update('KutuUser',array('ses'=>'*'),"ses='".Zend_Session::getId()."'");
				$flashMessenger = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
		        $flashMessenger->addMessage('Session Expired');
		        $auth->clearIdentity();
		        
		        $this->_redirect($loginUrl.'?returnUrl='.$sReturn);     
			}
			
			$dat = Pandamp_Lib_Formater::now();
			$db->update('KutuUser',array('dtime'=>$dat),"ses='".Zend_Session::getId()."'");
			*/
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
    			$identity = $auth->getIdentity();
    			$guidUser = $identity->kopel;
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