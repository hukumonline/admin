<?php

/**
 * Description of DmsController
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Search_DmsController extends Zend_Controller_Action
{
    protected $_user;

    function  preDispatch()
    {
        $this->_helper->layout->setLayout('layout-paging-search');

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
        	if ($this->getRequest()->isXmlHttpRequest()) {
        		//$this->getResponse()->setHttpResponseCode(401);
        		$this->getResponse()->setRawHeader('HTTP/1.1 401 Not Found');
        		$return = array('message' => 'You are not allowed to access this page because you do not have permission to view it.<br />Please contact the administrator.');
        		$this->getResponse()->setBody(Zend_Json::encode($return));
        		$this->getResponse()->setBody(
       				"<script>alert('f');</script>"        				
        		);die;
        	}
			$this->_redirect($loginUrl.'?returnUrl='.$sReturn);     
        }
        else
        {
            $this->_user = $auth->getIdentity();

            $zl = Zend_Registry::get("Zend_Locale");
            
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
    function browseAction()
    {
        $r = $this->getRequest();
        $sOffset = ($r->getParam('sOffset'))? $r->getParam('sOffset') : 0;
        $this->view->sOffset = $sOffset;
        $sLimit = ($r->getParam('sLimit'))? $r->getParam('sLimit') : 0;
        $this->view->sLimit = $sLimit;
        
        $category = ($r->getParam('category'))? $r->getParam('category') : '';
        
		if ($category=="all")
		$category="";
		

        $query = ($r->getParam('q'))? $r->getParam('q') : '';

        $indexingEngine = Pandamp_Search::manager();
        if(empty($query)) {
            //$hits = $indexingEngine->find("*:*;publishedDate desc",$sOffset, $sLimit);
            $hits = $indexingEngine->find("*:*",$sOffset, $sLimit);
        } else {
        	
        	if ($category)
        	{
        		$querySolr = $query . ' profile:'.$category;
        	}
        	else 
        	{
	            //$hits = $indexingEngine->find($query." -profile:kutu_doc;publishedDate desc",$sOffset, $sLimit);
	            $querySolr = $query." -profile:kutu_doc";
        	}
        	
        	$hits = $indexingEngine->find($querySolr, $sOffset, $sLimit);
        }
        
        $solrNumFound = count($hits->response->docs);

        $content = 0;
        $data = array();

        for($ii=0;$ii<$solrNumFound;$ii++) {
                $row = $hits->response->docs[$ii];
                $data[$content][0] = $row->id;
                $data[$content][1] = (isset($row->title))? $row->title : '';

                $tblCatalogFolder = new App_Model_Db_Table_CatalogFolder();
                $rowsetCatalogFolder = $tblCatalogFolder->fetchRow("catalogGuid='$row->id'");
                if ($rowsetCatalogFolder)
                    $parentGuid= $rowsetCatalogFolder->folderGuid;
                else
                    $parentGuid='';

                $data[$content][2] = $parentGuid;
                $profileSolr = str_replace("kutu_", "", $row->profile);
                $data[$content][3] = $profileSolr;
                $data[$content][4] = $row->createdDate;
                $data[$content][5] = $row->createdBy;
                $data[$content][6] = $row->modifiedDate;
                $data[$content][7] = $row->modifiedBy;
                $data[$content][8] = $row->publishedDate;
                $data[$content][9] = $row->status;

                $content++;
        }

        switch ($category)
        {
			case "kutu_peraturan":
            case "kutu_rancangan_peraturan":
            case "kutu_peraturan_kolonial":
				$ct = "(kutu_peraturan_kolonial OR kutu_rancangan_peraturan OR kutu_peraturan)";  
				break;
			case "":
                $ct = "all";
                break;	
            default :
                $ct = $category;
                break;
        }

		$this->_helper->layout()->categorySearchQuery = $ct;

        $num_rows = $solrNumFound;

        $this->view->query = $query;
        
        $this->view->totalItems = $hits->response->numFound;
        $this->view->numberOfRows = $num_rows;
        $this->view->data = $data;
    }
    
    /**
     * Find images
     */
    public function findimageAction()
    {
    	$request   = $this->getRequest();
    	$pageIndex = $request->getParam('pageIndex', 1);
    	$perPage   = $request->getParam('perpage');
    	$perPage   = ($perPage)? $perPage : 20;
    	$offset    = ($pageIndex - 1) * $perPage;
    
    	$params = null;
    	$exp 	= array();
    
    	$params = $request->getParam('q');
    	if (null != $params) {
    		$exp = rawurldecode(base64_decode($params));
    		$exp = Zend_Json::decode($exp);
    	}
    	else
    	{
    		$params = rawurlencode(base64_encode(Zend_Json::encode($exp)));
    	}
    
    	$indexingEngine = Pandamp_Search::manager();
    
    	if ($exp['keyword'] == '*' || $exp['keyword'] == '') {
    		$exp['keyword'] = '*:*';
    	}
    
    	$hits = $indexingEngine->find($exp['keyword'].' mimeType:image* profile:kutu_doc', $offset, $perPage,'createdDate desc');
    	$solrNumFound = count($hits->response->docs);
    
    	$num_rows = $hits->response->numFound;
    
    	$paginator = Zend_Paginator::factory($num_rows);
    	$cache = Pandamp_Cache::getInstance();
    	if ($cache) {
    		Zend_Paginator::setCache($cache);
    	}
    	$paginator->setCacheEnabled(true);
    
    	$paginator->setCurrentPageNumber($pageIndex);
    	$paginator->setItemCountPerPage($perPage);
    
    	$paginator = get_object_vars($paginator->getPages('Sliding'));
    
    	$paginatorOptions = array(
   			'path' 	   => $this->view->url(array('lang'=>$this->view->getLanguage()), 'search_catalog_findimage'),
   			'itemLink' => (null == $params) ? 'page-%d' : 'page-%d?perpage='.$perPage.'&q=' . $params,
    	);
    
    	/**
    	 * Support searching from other page
    	 * For example, search files at adding set page
    	*/
    	if (isset($exp['format']) && $exp['format'] == 'JSON') {
    		$this->_helper->getHelper('viewRenderer')->setNoRender();
    		$this->_helper->getHelper('layout')->disableLayout();
    			
    		$config = Pandamp_Application::getOption('cdn');
    			
    		if($solrNumFound>$perPage)
    			$numRowset = $perPage ;
    		else
    			$numRowset = $solrNumFound;
    			
    		$res = array(
   				'files' 	=> array(),
   				'paginator' => $this->view->paginator()->slide($paginator, $paginatorOptions),
    		);
    		for ($i=0;$i<$numRowset;$i++) {
    			$row = $hits->response->docs[$i];
    			$fs = 'thumbnail_';
    			//$filename = $row->systemName; <-- metode ini kadang suka kosong
    			$filename = $this->view->getCatalogAttribute($row->id,'docSystemName');
				$fn = pathinfo($filename,PATHINFO_FILENAME);
   				$ext = pathinfo($filename,PATHINFO_EXTENSION);
   				//$ext = strtolower($ext);
   				if (substr($fn,0,2) !== 'lt') {
   					$fn = $row->id;
   					$fs = 'tn_';
   					$filename = $fn.'.'.strtolower($ext);
   				}
   				$title = $this->view->getCatalogAttribute($fn,'fixedTitle');
   				$relDb = new App_Model_Db_Table_RelatedItem();
   				$rel = $relDb->fetchRow("itemGuid='".$fn."' AND relateAs='RELATED_IMAGE'");
   				$relGuid = (isset($rel->relatedGuid))?$rel->relatedGuid:'';
   				if (is_array(@getimagesize($config['static']['url']['images'].'/'.$relGuid.'/'.$fs.$filename)))
   					$url = $config['static']['url']['images'].'/'.$relGuid.'/'.$fs.$filename;
   				elseif (is_array(@getimagesize($config['static']['url']['images'].'/'.$fs.$filename)))
   					$url = $config['static']['url']['images'].'/'.$fs.$filename;
   				else 
   					$url = 'http://static.hukumonline.com/frontend/default/images/kaze/karticle-img.jpg';
   				   				
   				//$url = $config['static']['url']['images'].'/'.$rel->relatedGuid.'/'.$rel->itemGuid.'.'.strtolower($ext);
   				//$url = $config['static']['url']['images'].'/upload/'.$pd1.'/'.$pd2.'/'.$pd3.'/'.$pd4.'/'.$fn.'_square'.'.'.$ext;
   				$res['files'][] = array(
					'id' 			=> (isset($fn))?$fn:'',
					'relatedGuid' 	=> (isset($relGuid))?$relGuid:'',
					'title' 		=> (isset($title))?$title:'',
					'url'   		=> (isset($url))?$url:''
 				);
    
    				
    
    		}
    			
    		$this->getResponse()->setBody(Zend_Json::encode($res));
    	}
    
    }
}
