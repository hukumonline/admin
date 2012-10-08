<?php

/**
 * Description of Explorer
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Dms_ExplorerController extends Zend_Controller_Action
{
    protected $_user;

    function  preDispatch()
    {
        $this->_helper->layout->setLayout('layout-pusatdata');

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

//        $rowset = App_Model_Show_CatalogFolder::show()->getCatalogGuidByFolderGuid($node);
        $rowset = App_Model_Show_Catalog::show()->fetchCatalogInFolder($node,$offset,$limit,$sort,$sortBy);
        
//        $solrAdapter = Pandamp_Search::manager();

		$numOfRows = App_Model_Show_Catalog::show()->getCountCatalogsInFolder($node);
//        $numOfRows = App_Model_Show_CatalogFolder::show()->getCountCatalogGuidByFolderGuid($node);
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

        $this->_helper->layout()->headerTitle = "Document Management";
    }
}
