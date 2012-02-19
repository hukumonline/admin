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
		
        $sReturn = "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
        $sReturn = base64_encode($sReturn);

        //$sso = new Pandamp_Session_Remote();
        //$user = $sso->getInfo();

        if (!$auth->hasIdentity()) {
            //$this->_forward('login','account','admin');
			$loginUrl = $identity->loginUrl;
			
			$this->_redirect($loginUrl.'?returnUrl='.$sReturn);   
        }
        else
        {
            $this->_user = $auth->getIdentity();

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
