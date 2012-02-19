<?php

/**
 * Description of UploaderController
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Dms_UploaderController extends Zend_Controller_Action
{
    protected $_user;

    function  preDispatch()
    {
        $this->_helper->layout->setLayout('layout-dms-uploader');

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
    function newAction()
    {
        $r = $this->getRequest();
        $relatedGuid = $r->getParam('relatedGuid');
        $this->view->relatedGuid = $relatedGuid;
        if(empty($relatedGuid))
                throw new Zend_Exception("relatedGuid can not be empty!");

        $message = '';

        $urlReferer = $_SERVER['HTTP_REFERER'];

        $node = ($r->getParam('node')?$r->getParam('node'):'root');
        $this->view->currentNode = $node;

        $title = App_Model_Show_CatalogAttribute::show()->getCatalogAttributeValue($relatedGuid,'fixedTitle');
        $this->view->catalogTitle = $title;
        $subTitle = App_Model_Show_CatalogAttribute::show()->getCatalogAttributeValue($relatedGuid,'fixedSubTitle');
        $this->view->catalogSubTitle = $subTitle;

        $modDir = $this->getFrontController()->getModuleDirectory();
        require_once($modDir.'/components/Menu/FolderBreadcrumbs.php');
        $w = new Dms_Menu_FolderBreadcrumbs($node);
        $this->view->widget2 = $w;

        if($r->isPost())
        {
            $sessHistory = new Zend_Session_Namespace('BROWSER_HISTORY');
            $urlReferer = $sessHistory->urlReferer;

            $this->_save();
            $message = "File was successfully uploaded.";
        }
        $this->view->message = $message;

        $sessHistory = new Zend_Session_Namespace('BROWSER_HISTORY');
        $sessHistory->urlReferer = $urlReferer;
        $this->view->urlReferer = $sessHistory->urlReferer;
        
        $this->_helper->layout()->headerTitle = "Document Upload Management: Upload New File";
    }
    private function _save()
    {
        $hol = new Pandamp_Core_Hol_Catalog();
        $r = $this->getRequest();
        $aData = $r->getParams();

        $hol->uploadFile($aData, $aData['relatedGuid']);
    }
}
