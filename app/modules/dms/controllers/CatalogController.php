<?php

/**
 * Description of CatalogController
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Dms_CatalogController extends Zend_Controller_Action
{
    protected $_user;
    protected $_lang;

    function  preDispatch()
    {
        $this->_helper->layout->setLayout('layout-dms-catalog');

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

			$zl = Zend_Registry::get("Zend_Locale");
			$this->_lang = $zl;
			
            $acl = Pandamp_Acl::manager();
            if (!$acl->checkAcl("site",'all','user', $this->_user->username, false,false))
            {
                $this->_redirect(ROOT_URL.'/'.$this->_lang->getLanguage().'/error/restricted');
            }
        }
    }
    function rightdownAction()
    {
        $r = $this->getRequest();
        $catalogGuid = $r->getParam('guid');
        $this->view->catalogGuid = $catalogGuid;

        $node = ($r->getParam('node')?$r->getParam('node'):'root');

        $this->view->currentNode = $node;
    }
    function headerAction()
    {
        $r = $this->getRequest();
        $sOffset = $r->getParam('sOffset');
        $this->view->sOffset = $sOffset;
        $sLimit = $r->getParam('sLimit');
        $this->view->sLimit = $sLimit;

        $query = ($r->getParam('q'))? $r->getParam('q') : '';
        $this->_helper->layout()->searchQuery = $query;
        $this->view->user = $this->_user;
    }
    function detailAction()
    {
        $r = $this->getRequest();
        $catalogGuid = $r->getParam('guid');
        $this->view->catalogGuid = $catalogGuid;

        $node = ($r->getParam('node')?$r->getParam('node'):'root');
        $this->view->currentNode = $node;
        
        $modDir = $this->getFrontController()->getModuleDirectory("dms");
        require_once($modDir.'/components/Menu/FolderBreadcrumbs.php');
        $w = new Dms_Menu_FolderBreadcrumbs($node);
        $this->view->widget2 = $w;

        $title = App_Model_Show_CatalogAttribute::show()->getCatalogAttributeValue($catalogGuid,'fixedTitle');
        $this->view->catalogTitle = $title;
        $subTitle = App_Model_Show_CatalogAttribute::show()->getCatalogAttributeValue($catalogGuid,'fixedSubTitle');
        $this->view->catalogSubTitle = $subTitle;

        $modDir = $this->getFrontController()->getModuleDirectory("dms");
        require_once($modDir.'/components/Catalog/Detail.php');
        $w = new Dms_Catalog_Detail($catalogGuid, $node);
        $this->view->widget1 = $w;

        require_once($modDir.'/components/Relation/OtherViewer.php');
        $w3 = new Dms_Relation_OtherViewer($catalogGuid, $node);
        $this->view->widget3 = $w3;

        require_once($modDir.'/components/Relation/FolderViewer.php');
        $w4 = new Dms_Relation_FolderViewer($catalogGuid, $node);
        $this->view->widget4 = $w4;

        require_once($modDir.'/components/Relation/History.php');
        $w5 = new Dms_Relation_History($catalogGuid, $node);
        $this->view->widgetHistory = $w5;

        require_once($modDir.'/components/Relation/LegalBasis.php');
        $w6 = new Dms_Relation_LegalBasis($catalogGuid, $node);
        $this->view->widgetLegalBasis = $w6;

        require_once($modDir.'/components/Relation/Regulation.php');
        $w7 = new Dms_Relation_Regulation($catalogGuid, $node);
        $this->view->widgetImplementingRegulation = $w7;

        require_once($modDir.'/components/Relation/Document.php');
        $w8 = new Dms_Relation_Document($catalogGuid, $node);
        $this->view->widgetFiles = $w8;

//        require_once($modDir.'/components/Relation/Dci.php');
//        $w9 = new Dms_Relation_Dci($catalogGuid, $node);
//        $this->view->widgetImageFiles = $w9;

        $modelCatalog = App_Model_Show_Catalog::show()->getCatalogByGuid($catalogGuid);
        $this->view->rowCatalog = $modelCatalog;

        $this->_helper->layout()->headerTitle = "Catalog Management: Details";
    }
    function renderFolderAction()
    {
    	$this->_helper->layout->disableLayout();
    	
        $r = $this->getRequest();
        
        $catalogGuid = $r->getParam('guid');
        $node = ($r->getParam('node')?$r->getParam('node'):'root');
        
        $modDir = $this->getFrontController()->getModuleDirectory("dms");
        require_once($modDir.'/components/Relation/FolderViewer.php');
        $w4 = new Dms_Relation_FolderViewer($catalogGuid, $node);
        $this->view->widget4 = $w4;
    }
    function galleryAction()
    {
        $this->_helper->layout->disableLayout();

        $r = $this->getRequest();

        $catalogGuid = $r->getParam("guid");
        $node = $r->getParam("node");
        $page = ($r->getParam("page"))? $r->getParam("page") : 1;

        $limit = 15;

        $offset = $page;

        $modDir = $this->getFrontController()->getModuleDirectory("dms");
        require_once($modDir.'/components/Relation/Dci.php');
        $w9 = new Dms_Relation_Dci($catalogGuid, $node, $limit, $offset);
        $this->view->widgetImageFiles = $w9;
    }
    function editdocumentAction()
    {
    	$this->_helper->layout->setLayout('layout-dms-catalog-document');
    	
    	$r = $this->getRequest();
    	
    	$catalogGuid = explode(',',$r->getParam('guid'));
    	$this->view->catalogGuid = $catalogGuid;
    	
    	$relatedGuid = $r->getParam('relatedGuid');
    	$this->view->relatedGuid = $relatedGuid;
    	
    	$num_rows = count($catalogGuid);
    	$this->view->numberOfRows = $num_rows;
    }
    function recycleAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);
        
        $r = $this->getRequest();
    	
        $catalogGuid = explode(',',$r->getParam('guid'));
        
        $hol = new Pandamp_Core_Hol_Catalog();

        if (is_array($catalogGuid))
        {
            foreach($catalogGuid as $guid)
            {
                try
                {
                    $hol->recycle($guid);
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
                $hol->recycle($catalogGuid);
            }
            catch(Exception $e)
            {
                throw new Zend_Exception($e->getMessage());
            }
        }
    }
    function recyclebinAction()
    {
    	$tblCatalog = new App_Model_Db_Table_Catalog();
    	$rowset = $tblCatalog->fetchAll("status=-2");
    	
    	$this->view->rowset = $rowset;
    }
    function restoreAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);
        
        $r = $this->getRequest();
    	
        $catalogGuid = explode(',',$r->getParam('guid'));
        $status = $r->getParam('status');
        
        $hol = new Pandamp_Core_Hol_Catalog();

        if (is_array($catalogGuid))
        {
            foreach($catalogGuid as $guid)
            {
                try
                {
                    $hol->restore($guid, $status);
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
                $hol->restore($catalogGuid, $status);
            }
            catch(Exception $e)
            {
                throw new Zend_Exception($e->getMessage());
            }
        }
    }
    function deleteAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);
        
        $r = $this->getRequest();

        //$catalogGuid = $r->getParam('guid');
        $catalogGuid = explode(',',$r->getParam('guid'));
        
        $hol = new Pandamp_Core_Hol_Catalog();

        if (is_array($catalogGuid))
        {
            foreach($catalogGuid as $guid)
            {
                try
                {
                    $hol->delete($guid);
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
                $hol->delete($catalogGuid);
            }
            catch(Exception $e)
            {
                throw new Zend_Exception($e->getMessage());
            }
        }
    }
    function deleteConfirmAction()
    {
        $r = $this->getRequest();

        $catalogGuid = explode(',',$r->getParam('guid'));
        $this->view->catalogGuid = $catalogGuid;

        $this->_helper->layout()->headerTitle = "Catalog Management: Delete";
    }
    function moveFolderAction()
    {
        $urlReferer = $_SERVER['HTTP_REFERER'];
        $r = $this->getRequest();

        $guid = explode(',',$r->getParam('guid'));

        if(is_array($guid))
        {
            $sGuid = '';
            $sTitle = '';
            for($i=0;$i<count($guid);$i++)
            {
                $sGuid .= $guid[$i].';';
                $modelCatalog = App_Model_Show_Catalog::show()->getCatalogByGuid($guid[$i]);
                if ($modelCatalog['profileGuid'] == "klinik")
                    $sTitle .= App_Model_Show_CatalogAttribute::show()->getCatalogAttributeValue( $guid[$i], "fixedCommentTitle").', ';
                else
                    $sTitle .= App_Model_Show_CatalogAttribute::show()->getCatalogAttributeValue( $guid[$i], "fixedTitle").', ';
            }
            $guid = $sGuid;
        }
        else
        {
            $sTitle = '';
            if(!empty($guid))
            {
                $modelCatalog = App_Model_Show_Catalog::show()->getCatalogByGuid($guid);
                if ($modelCatalog['profileGuid'] == "klinik")
                    $sTitle .= App_Model_Show_CatalogAttribute::show()->getCatalogAttributeValue( $guid, "fixedCommentTitle").', ';
                else
                    $sTitle .= App_Model_Show_CatalogAttribute::show()->getCatalogAttributeValue( $guid, "fixedTitle");
            }
        }

        $this->view->catalogTitle = $sTitle;
        $this->view->guid = $guid;

        $sourceNode = $r->getParam('sourceNode');
        $this->view->sourceNode = $sourceNode;

        $this->_helper->layout()->headerTitle = "Catalog Management: Move to Folder";

        if($r->isPost())
        {
            $sessHistory = new Zend_Session_Namespace('BROWSER_HISTORY');
            $urlReferer = $sessHistory->urlReferer;
            
            $guid = explode(',',$r->getParam('guid'));

            $req = $this->getRequest();
            $targetNode = $req->getParam('targetNode');
            $tblCatalog = new App_Model_Db_Table_Catalog();

            if(is_array($guid))
            {
                foreach($guid as $tmpGuid)
                {
                    $rowset = $tblCatalog->find($tmpGuid);
                    if(count($rowset))
                    {
                        $row = $rowset->current();
                        $row->moveToFolder($sourceNode, $targetNode);
                    }
                }
            }
            else
            {
                $rowset = $tblCatalog->find($r->getParam('guid'));
                if(count($rowset))
                {
                    $row = $rowset->current();
                    $row->moveToFolder($sourceNode, $targetNode);
                }
            }

        }

        $sessHistory = new Zend_Session_Namespace('BROWSER_HISTORY');
        $sessHistory->urlReferer = $urlReferer;
        $this->view->urlReferer = $sessHistory->urlReferer;
    }
    function copyFolderAction()
    {
        $urlReferer = $_SERVER['HTTP_REFERER'];
        $r = $this->getRequest();

        $guid = explode(',',$r->getParam('guid'));

        if(is_array($guid))
        {
            $sGuid = '';
            $sTitle = '';
            for($i=0;$i<count($guid);$i++)
            {
                $sGuid .= $guid[$i].';';
                $modelCatalog = App_Model_Show_Catalog::show()->getCatalogByGuid($guid[$i]);
                if ($modelCatalog['profileGuid'] == "klinik")
                    $sTitle .= App_Model_Show_CatalogAttribute::show()->getCatalogAttributeValue( $guid[$i], "fixedCommentTitle").', ';
                else
                    $sTitle .= App_Model_Show_CatalogAttribute::show()->getCatalogAttributeValue( $guid[$i], "fixedTitle").', ';
            }
            $guid = $sGuid;
        }
        else
        {
            $sTitle = '';
            if(!empty($guid))
            {
                $modelCatalog = App_Model_Show_Catalog::show()->getCatalogByGuid($guid);
                if ($modelCatalog['profileGuid'] == "klinik")
                    $sTitle .= App_Model_Show_CatalogAttribute::show()->getCatalogAttributeValue( $guid, "fixedCommentTitle").', ';
                else
                    $sTitle .= App_Model_Show_CatalogAttribute::show()->getCatalogAttributeValue( $guid, "fixedTitle");
            }
        }

        $this->view->catalogTitle = $sTitle;
        $this->view->guid = $guid;

        $this->_helper->layout()->headerTitle = "Catalog Management: Copy to Folder";

        if($r->isPost())
        {
            $sessHistory = new Zend_Session_Namespace('BROWSER_HISTORY');
            $urlReferer = $sessHistory->urlReferer;

            $req = $this->getRequest();
            $targetNode = $req->getParam('targetNode');

            $tblCatalog = new App_Model_Db_Table_Catalog();

            if(is_array($r->getParam('guid')))
            {
                foreach($r->getParam('guid') as $tmpGuid)
                {
                    $rowset = $tblCatalog->find($tmpGuid);
                    if(count($rowset))
                    {
                        $row = $rowset->current();
                        $row->copyToFolder($targetNode);
                    }
                }
            }
            else
            {
                $rowset = $tblCatalog->find($r->getParam('guid'));
                if(count($rowset))
                {
                    $row = $rowset->current();
                    $row->copyToFolder($targetNode);
                }
            }
            $this->view->message = "Data was successfully saved.";
        }

        $sessHistory = new Zend_Session_Namespace('BROWSER_HISTORY');
        $sessHistory->urlReferer = $urlReferer;
        $this->view->urlReferer = $sessHistory->urlReferer;

    }
    function newAction()
    {
        $r = $this->getRequest();
        $folderGuid = $r->getParam('node');
        $profileGuid = $r->getParam('profile');

        $modDir = $this->getFrontController()->getModuleDirectory();
        require_once($modDir.'/components/Menu/FolderBreadcrumbs.php');
        $w = new Dms_Menu_FolderBreadcrumbs($folderGuid);
        $this->view->widget2 = $w;

        $generatorForm = new Pandamp_Form_Helper_CatalogInputGenerator();
        $aRender = $generatorForm->generateFormAdd(strtolower($profileGuid), $folderGuid);

        $this->view->aRenderedAttributes = $aRender;

        $this->view->currentNode = $folderGuid;
        $this->_helper->layout()->headerTitle = "Catalog Management: Add New Catalog";

        $message = "";
        if($r->isPost())
        {
	        $aData = $r->getPost();
	
	        $aData['username'] = $this->_user->username;
	
	        $Bpm = new Pandamp_Core_Hol_Catalog();
	        $id	 = $Bpm->save($aData);
	        
            if ($id) {
	            $message = "Data was successfully saved.";
				$this->_helper->getHelper('FlashMessenger')
					->addMessage($message);
				$this->_redirect(ROOT_URL.'/'.$this->_lang->getLanguage().'/dms/explorer/browse/node/'.$folderGuid);
            }
        }
    }
    function editAction()
    {
        $r = $this->getRequest();
        $catalogGuid = ($this->_getParam('guid'))? $this->_getParam('guid') : '';

        $sessHistory = new Zend_Session_Namespace('BROWSER_HISTORY');
        $sessHistory->currentNode = ($this->_getParam('node'))? $this->_getParam('node') : $sessHistory->currentNode;
        $this->view->currentNode = $sessHistory->currentNode;

        $urlReferer = (isset($_SERVER['HTTP_REFERER']))? $_SERVER['HTTP_REFERER'] : '';

        $message = "";

        $modDir = $this->getFrontController()->getModuleDirectory();
        require_once($modDir.'/components/Menu/FolderBreadcrumbs.php');
        $w = new Dms_Menu_FolderBreadcrumbs($sessHistory->currentNode);
        $this->view->widget2 = $w;

        $modelCatalog = App_Model_Show_Catalog::show()->getCatalogByGuid($catalogGuid);
        if ($modelCatalog['profileGuid'] == "klinik") {
            $this->_forward('answer.clinic','clinic','dms',array('guid'=>$catalogGuid));
        }
        else
        {
            $gen = new Pandamp_Form_Helper_CatalogInputGenerator();
            $aRender = $gen->generateFormEdit($catalogGuid);
            $this->view->aRenderedAttributes = $aRender;
        }

        if($r->isPost())
        {
            $sessHistory = new Zend_Session_Namespace('BROWSER_HISTORY');
            $urlReferer = $sessHistory->urlReferer;

	        $aData = $r->getPost();
	
	        $aData['username'] = $this->_user->username;
	
	        $Bpm = new Pandamp_Core_Hol_Catalog();
	        $id	 = $Bpm->save($aData);
	        
            if ($id) {
            	$modelCatalog = App_Model_Show_Catalog::show()->getCatalogByGuid($id);
	            $message = "Data was successfully saved.";
				$this->_helper->getHelper('FlashMessenger')
					->addMessage($message);
					
				if ($modelCatalog['profileGuid'] == "klinik") {
					$this->_redirect(ROOT_URL.'/'.$this->_lang->getLanguage().'/dms/clinic/browse/status/'.$modelCatalog['status'].'/node/'.$sessHistory->currentNode);
				}
				else 
				{
					$this->_redirect(ROOT_URL.'/'.$this->_lang->getLanguage().'/dms/explorer/browse/node/'.$sessHistory->currentNode);
				}
            }
        }

        $this->_helper->layout()->headerTitle = "Catalog Management: Edit Catalog";

        $sessHistory = new Zend_Session_Namespace('BROWSER_HISTORY');
        $sessHistory->urlReferer = $urlReferer;
        
        $this->view->urlReferer = $sessHistory->urlReferer;
    }
}
