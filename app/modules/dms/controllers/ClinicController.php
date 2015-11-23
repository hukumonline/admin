<?php

/**
 * Description of ClinicController
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Dms_ClinicController extends Zend_Controller_Action
{
    protected $_user;
    protected $_lang;

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

			$zl = Zend_Registry::get("Zend_Locale");
			$this->_lang = $zl;
			
            $acl = Pandamp_Acl::manager();
            if (!$acl->checkAcl("site",'all','user', $this->_user->username, false,false))
            {
                //$this->_redirect(ROOT_URL.'/'.$this->_lang->getLanguage().'/error/restricted');
                $this->_forward('restricted','error','admin',array('lang'=>$this->_lang->getLanguage()));
            }
            
			// [TODO] else: check if user has access to admin page and status website is online
			$tblSetting = new App_Model_Db_Table_Setting();
			$rowset = $tblSetting->find(1)->current();
			
			if ($rowset)
			{
				if (($rowset->status == 1 && $this->_lang->getLanguage() == 'id') || ($rowset->status == 2 && $this->_lang->getLanguage() == 'en') || ($rowset->status == 3))
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
        $this->_helper->layout->setLayout('layout-clinic');
        $r = $this->getRequest();

        $node = ($r->getParam('node')?$r->getParam('node'):'root');

        $modDir = $this->getFrontController()->getModuleDirectory();
        require_once($modDir.'/components/Menu/ViewFolder.php');
        $w = new Dms_Menu_ViewFolder($node);
        $this->view->widget1 = $w;

        $modDir = $this->getFrontController()->getModuleDirectory();
        require_once($modDir.'/components/Menu/FolderBreadcrumbs.php');
        $w = new Dms_Menu_FolderBreadcrumbs($node);
        $this->view->widget2 = $w;

        $modDir = $this->getFrontController()->getModuleDirectory();
        require_once($modDir.'/components/Menu/TreeSlideMenu.php');
        $w3 = new Dms_Menu_TreeSlideMenu();
        $this->view->slideMenu = $w3;

        $this->view->currentNode = $node;

        $limit = ($r->getParam('limit'))?$r->getParam('limit'):25;
        $this->view->limit =$limit;
        $itemsPerPage = $limit;
        $this->view->itemsPerPage = $itemsPerPage;
        $offset = ($r->getParam('offset'))?$r->getParam('offset'):0;
        $this->view->offset = $offset;

        $sort = ($r->getParam('sort'))?$r->getParam('sort'):"publishedDate";
        $this->view->sort = $sort;
        $sortBy = ($r->getParam('by'))?$r->getParam('by'):"desc";
        $this->view->sortBy = $sortBy;
        $withSelected = ($r->getParam('ws'))?$r->getParam('ws'):"withselected";
        $this->view->withSelected = $withSelected;

        $status = ($r->getParam('status')?$r->getParam('status'):0);
        $this->view->statusClinic = $status;

//        $rowset = App_Model_Show_Catalog::show()->fetchCatalogByStatus($status);
		$rowset = App_Model_Show_Catalog::show()->fetchFromFolderAdminClinic($status,$offset,$limit,$sort,$sortBy);

//        $solrAdapter = Pandamp_Search::manager();

        //$numOfRows = App_Model_Show_CatalogFolder::show()->getCountCatalogGuidByFolderGuid($node);
        $numOfRows = App_Model_Show_Catalog::show()->countCatalogsInFolderClinic($status);
//        $numi = count($rowset);
//        $sSolr = "id:(";
//        for($i=0;$i<$numi;$i++)
//        {
//            $row = $rowset[$i];
//            $sSolr .= $row['guid'] .' ';
//        }
//        $sSolr .= ')';
//
//        if(!$numi) $sSolr="id:(hfgjhfdfka)";
//
//        $solrResult = $solrAdapter->findAndSort($sSolr,$offset,$limit, array($sort.' '.$sortBy));
//        $solrNumFound = count($solrResult->response->docs);
//        $this->view->totalItems = $solrResult->response->numFound;
        $this->view->totalItems = $numOfRows;
//        $this->view->hits = $solrResult;
		$this->view->hits = $rowset;
		
        $this->_helper->layout()->headerTitle = "Clinic";
    }
    function answerClinicAction()
    {
        $urlReferer = (isset($_SERVER['HTTP_REFERER']))? $_SERVER['HTTP_REFERER'] : '';

    	$request 		= $this->getRequest();
    	
        $catalogGuid 	= $request->getParam('guid');
        $node 			= $request->getParam('node');
        
        $gen = new Pandamp_Form_Helper_ClinicInputGenerator();
        $aRender = $gen->generateFormAnswer($catalogGuid);
        $this->view->aRenderedAttributes = $aRender;

        if($request->isPost())
        {
            $sessHistory = new Zend_Session_Namespace('BROWSER_HISTORY');
            $urlReferer = $sessHistory->urlReferer;

	        $aData = $request->getPost();
	
	        $aData['username'] = $this->_user->username;
	
	        $Bpm = new Pandamp_Core_Hol_Catalog();
	        $id	 = $Bpm->save($aData);
	        
            if ($id) {
	            //$message = "Data was successfully saved.";
				//$this->_helper->getHelper('FlashMessenger')->addMessage($message);
				//$this->_redirect(ROOT_URL.'/'.$this->_lang->getLanguage().'/dms/clinic/browse/status/'.$aData['status'].'/node/'.$node);
            	$queue = Zend_Registry::get(Bootstrap::NAME_ORDERQUEUE);
            	$queue->addJob('Pandamp_Job_Catalog',
            			['guid' => $id,'folderGuid' => $node, 'ip' => Pandamp_Lib_Formater::getHttpRealIp(), 'kopel' => $this->_user->kopel, 'lang' => $this->view->getLanguage()],
            			false);
            	 
            	$this->_helper->json([
            			'response' => true,
            			'message' => 'Artikel berhasil disimpan. <a href="'.ROOT_URL.'/'.$this->_lang->getLanguage().'/dms/clinic/browse/status/'.$aData['status'].'/node/'.$node.'">Lihat artikel</a>.'
            		]);
            }
        }

        $sessHistory = new Zend_Session_Namespace('BROWSER_HISTORY');
        $sessHistory->urlReferer = $urlReferer;
        $this->view->urlReferer = $sessHistory->urlReferer;
    }
}
