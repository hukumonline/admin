<?php
/**
 * @author	2012-2013 Nihki Prihadi <nihki@madaniyah.com>
 * @version $Id: HolController.php 1 2012-09-25 16:12Z $
 */

class Report_HolController extends Zend_Controller_Action  
{
    protected $_user;

    function  preDispatch()
    {
        $this->_helper->layout->setLayout('layout-report');

        $auth = Zend_Auth::getInstance();
        
        $identity = Pandamp_Application::getResource('identity');

        $loginUrl = $identity->loginUrl;
        
		$multidb = Pandamp_Application::getResource('multidb');
		$multidb->init();
		
		$db = $multidb->getDb('db2');
		
        $sReturn = "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
        $sReturn = base64_encode($sReturn);

        if (!$auth->hasIdentity()) {
			
			$this->_redirect($loginUrl.'?returnUrl='.$sReturn);     
        }
        else
        {
            $this->_user = $auth->getIdentity();

            $zl = Zend_Registry::get("Zend_Locale");
            
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
        
        parent::preDispatch();
    }
    
	public function dcAction()
	{
		$request 	= $this->getRequest();
		
		$y			= $request->getParam('y');
		
		$pageIndex 	= $request->getParam('page', 1);
		$perPage 	= 20;
		$offset	 	= ($pageIndex - 1) * $perPage;
		$pageRange 	= 10;
		
		$querySolr = "profile:(kutu_peraturan OR kutu_peraturan_kolonial OR kutu_rancangan_peraturan) createdDate:$y*;date desc";
		
		/*
        $db = Zend_Db_Table::getDefaultAdapter()->query
        ("SELECT guid from KutuCatalog where profileGuid IN ('kutu_peraturan','kutu_peraturan_kolonial','kutu_rancangan_peraturan') AND YEAR(createdDate)='". $y ."'");

        $rowset = $db->fetchAll(Zend_Db::FETCH_OBJ);

        $numi = count($rowset);
        */
        
        $solrAdapter = Pandamp_Search::manager();
        
        /*
        $sSolr = "id:(";
        for($i=0;$i<$numi;$i++)
        {
            $row = $rowset[$i];
            $sSolr .= $row->guid .' ';
        }
        $sSolr .= ')';

        if(!$numi)
			$sSolr="id:(hfgjhfdfka)";
		*/
		
		$solrResult = $solrAdapter->find($querySolr, $offset, $perPage);			
		$solrNumFound = count($solrResult->response->docs);
		
		/*$solrResult = $solrAdapter->findAndSort($sSolr,$offset,$perPage, 'date desc');
        $solrNumFound = $solrResult->response->numFound;*/
     
		$content = 0;
		$data = array();
		
        if($solrNumFound==0)
        {}
        else
        {
            for($ii=0;$ii<$solrNumFound;$ii++)
            {
            	if(isset($solrResult->response->docs[$ii]))
            	{
            		$row = $solrResult->response->docs[$ii];
            		if(!empty($row))
            		{
            			$data[$ii][0] = $row->id;
            			$data[$ii][1] = $row->title;
			            $data[$ii][2] = $row->createdDate;
			            $data[$ii][3] = $row->modifiedDate;
			            $data[$ii][4] = $row->createdBy;
			            $data[$ii][5] = $row->modifiedBy;
            		}
            	}            	
            }
            
			/**
			 * Paginator
			 */
			$paginator = Zend_Paginator::factory($solrResult->response->numFound);
			$paginator->setCurrentPagenumber($pageIndex);
			$paginator->setItemCountPerPage($perPage);
			$paginator->setPageRange($pageRange);
			$scrollType = 'Sliding'; //change this to 'All', 'Elastic', 'Sliding' or 'Jumping' to test all scrolling types
			$paginator = get_object_vars($paginator->getPages($scrollType));
			
			
			$this->view->assign('pageIndex', $pageIndex);
			$this->view->assign('paginator', $paginator);
			
			$this->view->assign('data', $data);
			$this->view->assign('numberOfRows', $solrNumFound);
        }
        
        
        $this->view->assign('y', $y);
        
        //$this->view->assign('totalOfRows', $numi);
        $this->view->assign('totalOfRows', $solrResult->response->numFound);
	}
}