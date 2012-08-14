<?php

/**
 * Description of RelationController
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Dms_RelationController extends Zend_Controller_Action
{
    protected $_user;

    function  preDispatch()
    {
        $this->_helper->layout->setLayout('layout-dms-relation-catalog');

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

            $zl  = Zend_Registry::get("Zend_Locale");
            
            $acl = Pandamp_Acl::manager();
            if (!$acl->checkAcl("site",'all','user', $this->_user->username, false,false))
            {
                $this->_redirect(ROOT_URL.'/'.$zl->getLanguage().'/error/restricted');
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
				else 
				{
					return;
				}
			}
        }
    }
    function searchAction()
    {
        $sessHistory = new Zend_Session_Namespace('BROWSER_HISTORY');
        $sessHistory->relatedGuid = ($this->_getParam('relatedGuid'))? $this->_getParam('relatedGuid') : $sessHistory->relatedGuid;
        $this->view->relatedGuid = $sessHistory->relatedGuid;

        $r = $this->getRequest();

        $sQuery = ($r->getParam('sQuery'))?$r->getParam('sQuery'):'';
        $this->view->sQuery = $sQuery;
        $nOffset = $r->getParam('nOffset');
        $this->view->nOffset = $nOffset;
        $nLimit = $r->getParam('nLimit');
        $this->view->nLimit = $nLimit;
        $withSelected = ($r->getParam('relate'))?$r->getParam('relate'):"relateas";
        $this->view->withSelected = $withSelected;


        $node = ($r->getParam('node')?$r->getParam('node'):'root');
        $this->view->currentNode = $node;

        $modDir = $this->getFrontController()->getModuleDirectory();
        require_once($modDir.'/components/Menu/FolderBreadcrumbs.php');
        $w = new Dms_Menu_FolderBreadcrumbs($node);
        $this->view->widget2 = $w;

        $title = App_Model_Show_CatalogAttribute::show()->getCatalogAttributeValue($sessHistory->relatedGuid,'fixedTitle');
        $this->view->catalogTitle = $title;
        $subTitle = App_Model_Show_CatalogAttribute::show()->getCatalogAttributeValue($sessHistory->relatedGuid,'fixedSubTitle');
        $this->view->catalogSubTitle = $subTitle;

        $this->_helper->layout()->headerTitle = "Catalog Management: Relation";

        $indexingEngine = Pandamp_Search::manager();

        if(empty($sQuery))
            $hits = $indexingEngine->find("fjkslfjdkfjls",$nOffset, $nLimit);
        else
            $hits = $indexingEngine->find($sQuery." -profile:kutu_doc",$nOffset, $nLimit);

        $this->view->hits = $hits;
    }
    function newAction()
    {
        $this->_helper->layout->disableLayout();
        $aResult = array();

        $req = $this->getRequest();
        $item = $req->getParam('guid');
        $relatedItem = $req->getParam('relatedGuid');
        $as = $req->getParam('relateAs');

        $tblCatalog = new App_Model_Db_Table_Catalog();

        if(empty($relatedItem))
        {
            $aResult['isError'] = true;
            $aResult['msg'] = 'No relatedGuid specified!';
        }

        if(is_array($item))
        {
            foreach($item as $guid)
            {
                $rowCatalog = $tblCatalog->find($guid)->current();
                $rowCatalog->relateTo($relatedItem, $as);

                $aResult['isError'] = false;
                $aResult['msg'] = 'Adding Multi Relation Success';
            }
        }
        else
        {
            $rowCatalog = $tblCatalog->find($item)->current();
            $rowCatalog->relateTo($relatedItem, $as);

            $aResult['isError'] = false;
            $aResult['msg'] = 'Adding Relation Success';
        }

        echo Zend_Json::encode($aResult);
    }
}
