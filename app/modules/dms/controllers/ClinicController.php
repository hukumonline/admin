<?php

/**
 * Description of ClinicController
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Dms_ClinicController extends Zend_Controller_Action
{
    protected $_user;

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
			
			//$this->_redirect($loginUrl.'?returnTo='.$sReturn);     
			$this->_redirect($loginUrl.'/returnUrl/'.$sReturn);
        }
        else
        {
            //$this->_user = $auth->getIdentity();
            $idt = $auth->getIdentity();
			//$this->_user = $identity['properties'];
			$this->_user = new stdClass();
			$this->_user->kopel 	= $idt['properties']['kopel'];
			$this->_user->username 	= $idt['properties']['username'];
			$this->_user->packageId = $idt['properties']['packageId'];

            $acl = Pandamp_Acl::manager();
            if (!$acl->checkAcl("site",'all','user', $this->_user->username, false,false))
            {
                $zl = Zend_Registry::get("Zend_Locale");
                $this->_redirect(ROOT_URL.'/'.$zl->getLanguage().'/error/restricted');
            }
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
    	$r = $this->getRequest();
    	
        $urlReferer = $_SERVER['HTTP_REFERER'];

        $message = "";

        $catalogGuid = ($this->_getParam('catalogGuid'))? $this->_getParam('catalogGuid') : '';
        $gen = new Pandamp_Form_Helper_ClinicInputGenerator();
        $aRender = $gen->generateFormAnswer($catalogGuid);
        $this->view->aRenderedAttributes = $aRender;

        if($r->isPost())
        {
            $sessHistory = new Zend_Session_Namespace('BROWSER_HISTORY');
            $urlReferer = $sessHistory->urlReferer;

            $this->save();
            $message = "Data was successfully saved.";
        }

        $this->view->message = $message;

        $sessHistory = new Zend_Session_Namespace('BROWSER_HISTORY');
        $sessHistory->urlReferer = $urlReferer;
        $this->view->urlReferer = $sessHistory->urlReferer;
    }
    private function save()
    {
        $Bpm = new Pandamp_Core_Hol_Catalog();
        $request = $this->getRequest();
        $aData = $request->getParams();

        $aData['username'] = $this->_user->username;

        $Bpm->save($aData);
    }
}
