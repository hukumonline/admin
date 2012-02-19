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
        $sOffset = $r->getParam('sOffset');
        $this->view->sOffset = $sOffset;
        $sLimit = $r->getParam('sLimit');
        $this->view->sLimit = $sLimit;

        $query = ($r->getParam('q'))? $r->getParam('q') : '';

        $indexingEngine = Pandamp_Search::manager();
        if(empty($query)) {
            //$hits = $indexingEngine->find("*:*;publishedDate desc",$sOffset, $sLimit);
            $hits = $indexingEngine->find("*:*",$sOffset, $sLimit);
        } else {
            //$hits = $indexingEngine->find($query." -profile:kutu_doc;publishedDate desc",$sOffset, $sLimit);
            $hits = $indexingEngine->find($query." -profile:kutu_doc",$sOffset, $sLimit);
        }
        
        $solrNumFound = count($hits->response->docs);

        $content = 0;
        $data = array();

        for($ii=0;$ii<$solrNumFound;$ii++) {
                $row = $hits->response->docs[$ii];
                $data[$content][0] = $row->id;
                $data[$content][1] = $row->title;

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

        $num_rows = $solrNumFound;

        $this->view->query = $query;
        
        $this->view->totalItems = $hits->response->numFound;
        $this->view->numberOfRows = $num_rows;
        $this->view->data = $data;
    }
}
