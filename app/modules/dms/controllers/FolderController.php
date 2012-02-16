<?php

/**
 * Description of FolderController
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Dms_FolderController extends Zend_Controller_Action
{
    function preDispatch()
    {
        $this->_helper->layout->setLayout('layout-dms-folder');

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
    function newAction()
    {
        $r = $this->getRequest();
        $node = $r->getParam('node');
        $guid = $r->getParam('guid');

        $modDir = $this->getFrontController()->getModuleDirectory();
        require_once($modDir.'/components/Menu/FolderBreadcrumbs.php');
        $w = new Dms_Menu_FolderBreadcrumbs($node);
        $this->view->widget2 = $w;

        $tblFolder = new App_Model_Db_Table_Folder();
        $newRow = $tblFolder->createRow();

        if($node!='root')
        {
            $rowNode = $tblFolder->find($node)->current();
            $this->view->nodeTitle = $rowNode->title;
        }
        else
            $this->view->nodeTitle = 'ROOT';

        $message = '';

        if($r->isPost())
        {
            $newRow->parentGuid = $node;
            $newRow->title = $r->getParam('title');
            $newRow->description = $r->getParam('description');
            $newRow->viewOrder = $r->getParam('viewOrder')? $r->getParam('viewOrder') : 0;
            $prm = '{"menu":true,"st":"'.$r->getParam('cmsParams').'"}';
            $newRow->cmsParams = ($r->getParam('cmsParams'))? $prm : '';
            $newRow->type = $r->getParam('tof');
            $newRow->save();

            $message = 'Data was successfully saved.';

        }
        
        $this->view->row = $newRow;
        $this->view->message = $message;

        $this->view->currentNode = $node;

        $this->_helper->layout()->headerTitle = "Folder Management: Add New Folder";
    }
    function editAction()
    {
        $r = $this->getRequest();

        $guid = $r->getParam('guid');
        $previousNode = $r->getParam('node');

        $modDir = $this->getFrontController()->getModuleDirectory();
        require_once($modDir.'/components/Menu/FolderBreadcrumbs.php');
        $w = new Dms_Menu_FolderBreadcrumbs($previousNode);
        $this->view->widget2 = $w;

        $tblFolder = new App_Model_Db_Table_Folder();
        $rowFolder = $tblFolder->find($guid)->current();
        $message = '';

        if($r->isPost())
        {
            $rowFolder->title = $r->getParam('title');
            $rowFolder->description = $r->getParam('description');
            $rowFolder->viewOrder = $r->getParam('viewOrder');

            $prm = '{"menu":true,"st":"'.$r->getParam('cmsParams').'"}';
            
            $rowFolder->cmsParams = ($r->getParam('cmsParams'))? $prm : '';
            $rowFolder->type = $r->getParam('tof');
            $rowFolder->save();
            $message = 'Data was successfully saved.';

        }
        $this->view->row = $rowFolder;
        $this->view->previousNode = $previousNode;
        $this->view->message = $message;
        
        $this->_helper->layout()->headerTitle = "Folder Management: Edit Folder";
    }
    function deleteAction()
    {
        $r = $this->getRequest();

        $folderGuid = $r->getParam('guid');
        $bpm = new Pandamp_Core_Hol_Folder();

        if(is_array($folderGuid))
        {
            foreach($folderGuid as $guid)
            {
                try
                {
                    $bpm->delete($guid);
                }
                catch(Exception $e)
                {
                    $this->_forward('notify','error','error',array('type' => 'folder','num' => 101,'msg'=>$e->getMessage()));
                }
            }
        }
        else
        {
            try
            {
                $bpm->delete($folderGuid);
            }
            catch(Exception $e)
            {
            	$this->_forward('notify','error','error',array('type' => 'folder','num' => 101,'msg'=>$e->getMessage()));
            }
        }
        $this->view->message = "Folder(s) have been deleted.";

    }
    public function forcedeleteAction()
    {
        $r = $this->getRequest();

        $folderGuid = $r->getParam('guid');
        $bpm = new Pandamp_Core_Hol_Folder();

        if(is_array($folderGuid))
        {
            foreach($folderGuid as $guid)
            {
                try
                {
                    $bpm->forceDelete($folderGuid);
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
                $bpm->forceDelete($folderGuid);
            }
            catch(Exception $e)
            {
                throw new Zend_Exception($e->getMessage());
            }
        }
        $this->view->message = "Folder(s) have been deleted.";
    }
    public function moveAction()
    {
        $urlReferer = $_SERVER['HTTP_REFERER'];

        $r = $this->getRequest();

        $tblFolder = new App_Model_Db_Table_Folder();

        $guid = $r->getParam('guid');
        $message = '';

        if(is_array($guid))
        {
            $sGuid = '';
            $sTitle = '';
            for($i=0;$i<count($guid);$i++)
            {
                $sGuid .= $guid[$i].';';

                $rowFolder = $tblFolder->find($guid[$i])->current();
                $sTitle .= $rowFolder->title.', ';
            }
            $guid = $sGuid;
        }
        else
        {
            $sTitle = '';
            if(!empty($guid))
            {
                $rowFolder = $tblFolder->find($guid)->current();
                $sTitle .= $rowFolder->title;
            }
        }
        if($r->isPost())
        {
            $sessHistory = new Zend_Session_Namespace('BROWSER_HISTORY');
            $urlReferer = $sessHistory->urlReferer;

            $guid = $r->getParam('guid');
            $targetNode = $r->getParam('targetNode');
            if(is_array($guid))
            {
                foreach($guid as $folderId)
                {
                    $row = $tblFolder->find($folderId)->current();
                    $row->move($targetNode);
                }
            }
            else
            {
                $guid = $r->getParam('guid');
                $targetNode = $r->getParam('targetNode');
                $row = $tblFolder->find($guid)->current();
                $row->move($targetNode);
            }
            $message = "Data was successfully saved.";
        }

        $this->view->guid = $guid;
        $this->view->folderTitle = $sTitle;

        $backToNode = $r->getParam('backToNode');
        $this->view->backToNode = $backToNode;


        $rowFolder = $tblFolder->find($guid)->current();


        $this->view->row = $rowFolder;
        $this->view->message = $message;


        $sessHistory = new Zend_Session_Namespace('BROWSER_HISTORY');
        $sessHistory->urlReferer = $urlReferer;
        $this->view->urlReferer = $sessHistory->urlReferer;

        $this->_helper->layout()->headerTitle = "Folder Management: Move Folder";
    }
    public function copyAction()
    {
        $urlReferer = $_SERVER['HTTP_REFERER'];

        $r = $this->getRequest();

        $tblFolder = new App_Model_Db_Table_Folder();

        $guid = $r->getParam('guid');
        $message = '';

        if(is_array($guid))
        {
            $sGuid = '';
            $sTitle = '';
            for($i=0;$i<count($guid);$i++)
            {
                $sGuid .= $guid[$i].';';

                $rowFolder = $tblFolder->find($guid[$i])->current();
                $sTitle .= $rowFolder->title.', ';
            }
            $guid = $sGuid;
        }
        else
        {
            $sTitle = '';
            if(!empty($guid))
            {
                $rowFolder = $tblFolder->find($guid)->current();
                $sTitle .= $rowFolder->title;
            }
        }
        if($r->isPost())
        {
            $sessHistory = new Zend_Session_Namespace('BROWSER_HISTORY');
            $urlReferer = $sessHistory->urlReferer;
            
            $tblFolder = new App_Model_Db_Table_Folder();

            $guid = $r->getParam('guid');
            $targetNode = $r->getParam('targetNode');
            if(is_array($guid))
            {
                foreach($guid as $folderId)
                {
                    $row = $tblFolder->createRow();
                    $row->copy($targetNode,$folderId);
                }
            }
            else
            {
                $guid = $r->getParam('guid');
                $targetNode = $r->getParam('targetNode');
                $row = $tblFolder->createRow();
                $row->copy($targetNode,$guid);
            }
            $message = "Data was successfully saved.";
        }

        $this->view->guid = $guid;
        $this->view->folderTitle = $sTitle;

        $backToNode = $r->getParam('backToNode');
        $this->view->backToNode = $backToNode;


        $rowFolder = $tblFolder->find($guid)->current();


        $this->view->row = $rowFolder;
        $this->view->message = $message;


        $sessHistory = new Zend_Session_Namespace('BROWSER_HISTORY');
        $sessHistory->urlReferer = $urlReferer;
        $this->view->urlReferer = $sessHistory->urlReferer;

        $this->_helper->layout()->headerTitle = "Folder Management: Copy Folder";
    }
}
