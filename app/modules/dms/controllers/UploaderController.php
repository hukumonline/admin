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

        $loginUrl = $identity->loginUrl;
        
		$multidb = Pandamp_Application::getResource('multidb');
		$multidb->init();
		
		$db = $multidb->getDb('db2');
		
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
