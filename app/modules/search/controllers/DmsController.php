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
        Pandamp_Debug::manager($r->getParams());
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
}
