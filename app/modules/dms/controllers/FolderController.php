<?php

/**
 * Description of FolderController
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Dms_FolderController extends Zend_Controller_Action
{
    function preDispatch()
    {
        $this->_helper->layout->setLayout('layout-dms-folder');

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

            $zl  = Zend_Registry::get("Zend_Locale");
            
            $acl = Pandamp_Acl::manager();
            if (!$acl->checkAcl("site",'all','user', $this->_user->username, false,false))
            {                
                //$this->_redirect(ROOT_URL.'/'.$zl->getLanguage().'/error/restricted');
                $this->_forward('restricted','error','admin',array('lang'=>$zl->getLanguage()));
            }
            
			// [TODO] else: check if user has access to admin page and status website is online
			$tblSetting = new App_Model_Db_Table_Setting();
			$rowset = $tblSetting->find(1)->current();
			
			if ($rowset)
			{
				if (($rowset->status == 1 && $zl->getLanguage() == 'id') || ($rowset->status == 2 && $zl->getLanguage() == 'en') || ($rowset->status == 3))
				{
					if (($this->_user->name !== "Master") && ($this->_user->name !== "Super Admin"))
					{
						$this->_forward('temporary','error','admin'); 
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
    
    public function addAction()
    {
    	$this->_helper->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender();
    	
    	$request = $this->getRequest();
    	
    	if ($request->isPost()) {
    		$title = $request->getPost('title');
    		$guid = $request->getPost('guid');
    		$desc = $request->getPost('description');
    		$viewOrder = $request->getPost('viewOrder');
    		$cmsParams = $request->getPost('cmsParams');
    		$tof = $request->getPost('tof');
    		$asrot = $request->getPost('asroot');
    			
    		if ($asrot==1) $guid = 'root';
    	
    		$prm = '{"menu":true,"st":"'.$cmsParams.'"}';
    	
    		$zl = Zend_Registry::get('Zend_Locale');
    		$lang = $zl->getLanguage();
    			
    		$auth = Zend_Auth::getInstance();
    		$group = $auth->getIdentity()->name;
    		$group = strtolower(str_replace(" ", "", $group));
    			
    		/*$frontendOptions = array('lifetime' => 3600,'automatic_serialization' => true,'cache_id_prefix' => 'sidebar_');
    		 $backendOptions = array('cache_dir' => TEMP_DIR . '/cache/category');
    		$cache = Zend_Cache::factory('Core','File',$frontendOptions,$backendOptions);*/
    			
    		//$cache = Pandamp_Cache::getInstance();
    		//$cache->remove('dmstree_'.$lang.'_'.$group);
    		//$cache->remove("categoryCheckbox");
    	
    		$modelFolder = new App_Model_Db_Table_Folder();
    		$rowFolder = $modelFolder->createRow();
    	
    		$rowFolder->parentGuid = $guid;
    		$rowFolder->title = $title;
    		$rowFolder->description = $desc;
    		$rowFolder->viewOrder = ($viewOrder) ? $viewOrder : 0;
    		$rowFolder->cmsParams = ($cmsParams) ? $prm : '';
    		$rowFolder->type = $tof;
    	
    		$id = $rowFolder->save();
    	
    		$this->_response->setBody(Zend_Json::encode(
    				array(
    						'text' => $title,
    						'asrot' => $asrot,
    						'id' => $id
    				)
    		));
    	
    	}
    	 
    }
    
    public function editAction()
    {
    	$this->_helper->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender();
    
    	$request = $this->getRequest();
    
    	if ($request->isPost()) {
    
    		$title = $request->getPost('title');
    		$guid = $request->getPost('guid');
    		$desc = $request->getPost('description');
    		$viewOrder = $request->getPost('viewOrder');
    		$cmsParams = $request->getPost('cmsParams');
    		$tof = $request->getPost('tof');
    
    		$prm = '{"menu":true,"st":"'.$cmsParams.'"}';
    
    		$modelFolder = new App_Model_Db_Table_Folder();
    		$rowFolder = $modelFolder->find($guid)->current();
    		if ($rowFolder) {
    
    			$zl = Zend_Registry::get('Zend_Locale');
    			$lang = $zl->getLanguage();
    
    			$auth = Zend_Auth::getInstance();
    			$group = $auth->getIdentity()->name;
    			$group = strtolower(str_replace(" ", "", $group));
    				
    			//$cache = Pandamp_Cache::getInstance();
    			//$cache->remove('dmstree_'.$lang.'_'.$group);
    			//$cache->remove("categoryCheckbox");
    
    			$rowFolder->title = $title;
    			$rowFolder->description = $desc;
    			$rowFolder->viewOrder = ($viewOrder) ? $viewOrder : 0;
    			$rowFolder->cmsParams = ($cmsParams) ? $prm : '';
    			$rowFolder->type = $tof;
    
    			$id = $rowFolder->save();
    
    			$this->_response->setBody(Zend_Json::encode(
    					array(
    							'text' => $title,
    							'id' => $id
    					)
    			));
    
    		}
    	}
    
    
    }
    
    public function deleteAction()
    {
    	/**
    	 * Folder tidak bisa dihapus
    	 * jika folder mengandung sub-folder | catalog
    	 * Selain itu bisa dihapus
    	 * Untuk memilih lebih dari satu folder
    	 * tekan select+klik, alt+klik pada mac | ctrl+klik pada windows(linux)
    	 */
    
    	$this->_helper->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender();
    
    	$request = $this->getRequest();
    
    	if ($request->isPost()) {
    		$guid = $request->getPost('id');
    		$tbl = new App_Model_Db_Table_Folder();
    		$rowset = $tbl->find($guid);
    		if(count($rowset))
    		{
    			$row = $rowset->current();
    			try {
    					
    				$zl = Zend_Registry::get('Zend_Locale');
    				$lang = $zl->getLanguage();
    					
    				$auth = Zend_Auth::getInstance();
    				$group = $auth->getIdentity()->name;
    				$group = strtolower(str_replace(" ", "", $group));
    					
    				//$cache = Pandamp_Cache::getInstance();
    				//$cache->remove('dmstree_'.$lang.'_'.$group);
    				//$cache->remove("categoryCheckbox");
    					
    				$row->delete();
    					
    				$this->_response->setBody(Zend_Json::encode(
    						array(
    								'success' => 'true',
    								'text' => $row->title,
    								'id' => $guid
    						)
    				));
    
    			}
    			catch (Exception $e)
    			{
    				$this->_response->setBody(Zend_Json::encode(
    						array(
    								'success' => 'false',
    								'text' => $row->title.' ... '.$e->getMessage(),
    								'id' => $guid
    						)
    				));
    					
    
    			}
    
    		}
    
    	}
    
    }
    
    public function forcedeleteAction()
    {
    	$this->_helper->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender();
    
    	$request = $this->getRequest();
    
    	if ($request->isPost()) {
    		$folderGuid = $request->getPost('id');
    			
    		$hol = new Pandamp_Core_Hol_Folder();
    		$hol->forceDelete($folderGuid);
    	}
    }
    
    public function moveAction()
    {
    	$this->_helper->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender();
    
    	$request = $this->getRequest();
    
    	if ($request->isPost()) {
    		$targetGuid = $request->getPost("targetGuid");
    		$currentGuid = $request->getPost("folderGuid");
    			
    		$modelFolder = new App_Model_Db_Table_Folder();
    		$rowFolder = $modelFolder->find($currentGuid)->current();
    		$rowTargetFolder = $modelFolder->find($targetGuid)->current();
    			
    		if (empty($currentGuid) || $currentGuid == 'root') {
    			$response = Zend_Json::encode(array('success' => 'false','text' => 'Cant move root','id' => $currentGuid));
    		}
    		else
    		{
    			try {
    					
    				$zl = Zend_Registry::get('Zend_Locale');
    				$lang = $zl->getLanguage();
    					
    				$auth = Zend_Auth::getInstance();
    				$group = $auth->getIdentity()->name;
    				$group = strtolower(str_replace(" ", "", $group));
    					
    				//$cache = Pandamp_Cache::getInstance();
    				//$cache->remove('dmstree_'.$lang.'_'.$group);
    				//$cache->remove("categoryCheckbox");
    
    				$rowFolder->move($targetGuid);
    					
    				$response = Zend_Json::encode(array('success' => 'true','text' => $rowFolder->title.' pindah ke folder '.$rowTargetFolder->title,'id' => $rowFolder->guid));
    			}
    			catch (Exception $e)
    			{
    				$response = Zend_Json::encode(array('success' => 'false','text' => $e->getMessage(),'id' => $rowFolder->guid));
    			}
    
    		}
    			
    		$this->_response->setBody($response);
    			
    	}
    
    }
    
    public function copyAction()
    {
    	$this->_helper->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender();
    
    	$request = $this->getRequest();
    
    	if ($request->isPost()) {
    		$targetGuid = $request->getPost("targetGuid");
    		$currentGuid = $request->getPost("folderGuid");
    
    		$modelFolder = new App_Model_Db_Table_Folder();
    		$rowFolder = $modelFolder->find($currentGuid)->current();
    		$rowTargetFolder = $modelFolder->find($targetGuid)->current();
    
    
    		if (empty($currentGuid) || $currentGuid == 'root') {
    			$response = Zend_Json::encode(array('success' => 'false','text' => 'Cant move root','id' => $currentGuid));
    		}
    		else
    		{
    			try {
    					
    				$zl = Zend_Registry::get('Zend_Locale');
    				$lang = $zl->getLanguage();
    					
    				$auth = Zend_Auth::getInstance();
    				$group = $auth->getIdentity()->name;
    				$group = strtolower(str_replace(" ", "", $group));
    					
    				//$cache = Pandamp_Cache::getInstance();
    				//$cache->remove('dmstree_'.$lang.'_'.$group);
    				//$cache->remove("categoryCheckbox");
    					
    				$newRow = $modelFolder->createRow();
    				$newRow->copy($targetGuid,$currentGuid);
    
    				$response = Zend_Json::encode(array('success' => 'true','text' => $rowFolder->title.' disalin ke folder '.$rowTargetFolder->title,'id' => $rowFolder->guid));
    			}
    			catch (Exception $e)
    			{
    				$response = Zend_Json::encode(array('success' => 'false','text' => $e->getMessage(),'id' => $rowFolder->guid));
    			}
    				
    		}
    			
    		$this->_response->setBody($response);
    
    
    	}
    
    }
    
    public function checkAction()
    {
    	$this->_helper->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender();
    
    	$request = $this->getRequest();
    
    	if ($request->isPost()) {
    		$guid = $request->getParam('id');
    			
    		$tblFolder = new App_Model_Db_Table_Folder();
    		$rowFolder = $tblFolder->find($guid)->current();
    		if ($rowFolder) {
    			$prm = Zend_Json::decode($rowFolder->cmsParams);
    			$this->_response->setBody(Zend_Json::encode(
    					array(
    							'guid' => $rowFolder->guid,
    							'title' => $rowFolder->title,
    							'desc' => $rowFolder->description,
    							'parentGuid' => $rowFolder->parentGuid,
    							'path' => $rowFolder->path,
    							'type' => $rowFolder->type,
    							'viewOrder' => $rowFolder->viewOrder,
    							'cmsParams' => $prm['st']
    					)
    			));
    
    		}
    			
    	}
    }
    
    function newAction()
    {
        $r = $this->getRequest();
        $node = $r->getParam('node');
        $guid = $r->getParam('guid');

        $modDir = $this->getFrontController()->getModuleDirectory();
        require_once($modDir.'/components/Menu/FolderBreadcrumbs.php');
        $w = new Dms_Menu_FolderBreadcrumbs($node);
        $this->view->widget2 = $w;

        $tblFolder = new App_Model_Db_Table_Folder();
        $newRow = $tblFolder->createRow();

        if($node!='root')
        {
            $rowNode = $tblFolder->find($node)->current();
            $this->view->nodeTitle = $rowNode->title;
        }
        else
            $this->view->nodeTitle = 'ROOT';

        $message = '';

        if($r->isPost())
        {
            $newRow->parentGuid = $node;
            $newRow->title = $r->getParam('title');
            $newRow->description = $r->getParam('description');
            $newRow->viewOrder = $r->getParam('viewOrder')? $r->getParam('viewOrder') : 0;
            $prm = '{"menu":true,"st":"'.$r->getParam('cmsParams').'"}';
            $newRow->cmsParams = ($r->getParam('cmsParams'))? $prm : '';
            $newRow->type = $r->getParam('tof');
            $newRow->save();

            $message = 'Data was successfully saved.';

        }
        
        $this->view->row = $newRow;
        $this->view->message = $message;

        $this->view->currentNode = $node;

        $this->_helper->layout()->headerTitle = "Folder Management: Add New Folder";
    }
    function editOldAction()
    {
        $r = $this->getRequest();

        $guid = $r->getParam('guid');
        $previousNode = $r->getParam('node');

        $modDir = $this->getFrontController()->getModuleDirectory();
        require_once($modDir.'/components/Menu/FolderBreadcrumbs.php');
        $w = new Dms_Menu_FolderBreadcrumbs($previousNode);
        $this->view->widget2 = $w;

        $tblFolder = new App_Model_Db_Table_Folder();
        $rowFolder = $tblFolder->find($guid)->current();
        $message = '';

        if($r->isPost())
        {
            $rowFolder->title = $r->getParam('title');
            $rowFolder->description = $r->getParam('description');
            $rowFolder->viewOrder = $r->getParam('viewOrder');

            $prm = '{"menu":true,"st":"'.$r->getParam('cmsParams').'"}';
            
            $rowFolder->cmsParams = ($r->getParam('cmsParams'))? $prm : '';
            $rowFolder->type = $r->getParam('tof');
            $rowFolder->save();
            $message = 'Data was successfully saved.';

        }
        $this->view->row = $rowFolder;
        $this->view->previousNode = $previousNode;
        $this->view->message = $message;
        
        $this->_helper->layout()->headerTitle = "Folder Management: Edit Folder";
    }
    function deleteOldAction()
    {
        $r = $this->getRequest();

        $folderGuid = $r->getParam('guid');
        $bpm = new Pandamp_Core_Hol_Folder();

        if(is_array($folderGuid))
        {
            foreach($folderGuid as $guid)
            {
                try
                {
                    $bpm->delete($guid);
                }
                catch(Exception $e)
                {
                    $this->_forward('notify','error','error',array('type' => 'folder','num' => 101,'msg'=>$e->getMessage()));
                }
            }
        }
        else
        {
            try
            {
                $bpm->delete($folderGuid);
            }
            catch(Exception $e)
            {
            	$this->_forward('notify','error','error',array('type' => 'folder','num' => 101,'msg'=>$e->getMessage()));
            }
        }
        $this->view->message = "Folder(s) have been deleted.";

    }
    public function forcedeleteOldAction()
    {
        $r = $this->getRequest();

        $folderGuid = $r->getParam('guid');
        $bpm = new Pandamp_Core_Hol_Folder();

        if(is_array($folderGuid))
        {
            foreach($folderGuid as $guid)
            {
                try
                {
                    $bpm->forceDelete($folderGuid);
                }
                catch(Exception $e)
                {
                    throw new Zend_Exception($e->getMessage());
                }
            }
        }
        else
        {
            try
            {
                $bpm->forceDelete($folderGuid);
            }
            catch(Exception $e)
            {
                throw new Zend_Exception($e->getMessage());
            }
        }
        $this->view->message = "Folder(s) have been deleted.";
    }
    public function moveOldAction()
    {
        $urlReferer = $_SERVER['HTTP_REFERER'];

        $r = $this->getRequest();

        $tblFolder = new App_Model_Db_Table_Folder();

        $guid = $r->getParam('guid');
        $message = '';

        if(is_array($guid))
        {
            $sGuid = '';
            $sTitle = '';
            for($i=0;$i<count($guid);$i++)
            {
                $sGuid .= $guid[$i].';';

                $rowFolder = $tblFolder->find($guid[$i])->current();
                $sTitle .= $rowFolder->title.', ';
            }
            $guid = $sGuid;
        }
        else
        {
            $sTitle = '';
            if(!empty($guid))
            {
                $rowFolder = $tblFolder->find($guid)->current();
                $sTitle .= $rowFolder->title;
            }
        }
        if($r->isPost())
        {
            $sessHistory = new Zend_Session_Namespace('BROWSER_HISTORY');
            $urlReferer = $sessHistory->urlReferer;

            $guid = $r->getParam('guid');
            $targetNode = $r->getParam('targetNode');
            if(is_array($guid))
            {
                foreach($guid as $folderId)
                {
                    $row = $tblFolder->find($folderId)->current();
                    $row->move($targetNode);
                }
            }
            else
            {
                $guid = $r->getParam('guid');
                $targetNode = $r->getParam('targetNode');
                $row = $tblFolder->find($guid)->current();
                $row->move($targetNode);
            }
            $message = "Data was successfully saved.";
        }

        $this->view->guid = $guid;
        $this->view->folderTitle = $sTitle;

        $backToNode = $r->getParam('backToNode');
        $this->view->backToNode = $backToNode;


        $rowFolder = $tblFolder->find($guid)->current();


        $this->view->row = $rowFolder;
        $this->view->message = $message;


        $sessHistory = new Zend_Session_Namespace('BROWSER_HISTORY');
        $sessHistory->urlReferer = $urlReferer;
        $this->view->urlReferer = $sessHistory->urlReferer;

        $this->_helper->layout()->headerTitle = "Folder Management: Move Folder";
    }
    public function copyOldAction()
    {
        $urlReferer = $_SERVER['HTTP_REFERER'];

        $r = $this->getRequest();

        $tblFolder = new App_Model_Db_Table_Folder();

        $guid = $r->getParam('guid');
        $message = '';

        if(is_array($guid))
        {
            $sGuid = '';
            $sTitle = '';
            for($i=0;$i<count($guid);$i++)
            {
                $sGuid .= $guid[$i].';';

                $rowFolder = $tblFolder->find($guid[$i])->current();
                $sTitle .= $rowFolder->title.', ';
            }
            $guid = $sGuid;
        }
        else
        {
            $sTitle = '';
            if(!empty($guid))
            {
                $rowFolder = $tblFolder->find($guid)->current();
                $sTitle .= $rowFolder->title;
            }
        }
        if($r->isPost())
        {
            $sessHistory = new Zend_Session_Namespace('BROWSER_HISTORY');
            $urlReferer = $sessHistory->urlReferer;
            
            $tblFolder = new App_Model_Db_Table_Folder();

            $guid = $r->getParam('guid');
            $targetNode = $r->getParam('targetNode');
            if(is_array($guid))
            {
                foreach($guid as $folderId)
                {
                    $row = $tblFolder->createRow();
                    $row->copy($targetNode,$folderId);
                }
            }
            else
            {
                $guid = $r->getParam('guid');
                $targetNode = $r->getParam('targetNode');
                $row = $tblFolder->createRow();
                $row->copy($targetNode,$guid);
            }
            $message = "Data was successfully saved.";
        }

        $this->view->guid = $guid;
        $this->view->folderTitle = $sTitle;

        $backToNode = $r->getParam('backToNode');
        $this->view->backToNode = $backToNode;


        $rowFolder = $tblFolder->find($guid)->current();


        $this->view->row = $rowFolder;
        $this->view->message = $message;


        $sessHistory = new Zend_Session_Namespace('BROWSER_HISTORY');
        $sessHistory->urlReferer = $urlReferer;
        $this->view->urlReferer = $sessHistory->urlReferer;

        $this->_helper->layout()->headerTitle = "Folder Management: Copy Folder";
    }
}
