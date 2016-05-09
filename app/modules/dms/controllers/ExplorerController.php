<?php
class Dms_ExplorerController extends Zend_Controller_Action
{
	protected $_user;
	
	function preDispatch()
	{
		$this->_helper->layout->setLayout('new/layout-pusatdata');
		
        $identity = Pandamp_Application::getResource('identity');
		$loginUrl = $identity->loginUrl;
		
        $sReturn = "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
        $sReturn = base64_encode($sReturn);

		$auth = Zend_Auth::getInstance();
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
				$this->_forward('restricted','error','admin',array('lang'=>$zl->getLanguage()));
			}

			$tblSetting = new App_Model_Db_Table_Setting();
			$rowset = $tblSetting->find(1)->current();
				
			if ($rowset)
			{
				if (($rowset->status == 1 && $zl->getLanguage() == 'id') || ($rowset->status == 2 && $zl->getLanguage() == 'en') || ($rowset->status == 3))
				{
					$aReturn = App_Model_Show_AroGroup::show()->getUserGroup($this->_user->packageId);
					if (isset($aReturn['name']))
					{
						if (($aReturn['name'] !== "Master") && ($aReturn['name'] !== "Super Admin"))
						{
							$this->_forward('temporary','error','admin');
						}
					}
						
				}
			}
			
		}
	}
	
	function browseAction()
	{
		$request = $this->getRequest();
		
		$pageIndex = $request->getParam('page',1);
		$node = $request->getParam('node','root');
		$limit = $request->getParam('showperpage',25);
		$status = $request->getParam('status');
		$sortby = $request->getParam('sortby','createdDate desc');
		
		$offset = ($pageIndex > 0) ? ($pageIndex - 1) * $limit : 0;
		
		if ($node == "lt4b11ece54d870") { // Approved
			$status = "1";
		}
		elseif ($node == "lt4b11e8fde1e42") { // Draft
			$status = "0";
		}
		elseif ($node == "lt4b11ecf5408d2") { // NA (Not Available)
			$status = "2";
		}
		elseif ($node == "lt4b11e8c86c8a4") { // Published
			$status = "99";
			$sortby = "publishedDate desc";
		}
		
		$catalogDb = new App_Model_Db_Table_Catalog();
		$rowset = $catalogDb->fetchCatalogInFolder($node,$offset,$limit,$sortby,['status'=>$status]);
		$numOfRows = $catalogDb->getCountCatalogInFolder($node,['status'=>$status]);
		
		$paginator = new Zend_Paginator(new Pandamp_Utility_PaginatorAdapter($rowset, $numOfRows));
		/*$cache = Pandamp_Cache::getInstance();
		if ($cache) {
			Zend_Paginator::setCache($cache);
		}
		$paginator->setCacheEnabled(true);*/
		$paginator->setCurrentPageNumber($pageIndex);
		$paginator->setItemCountPerPage($limit);
		$paginator = get_object_vars($paginator->getPages('Sliding'));
		
		$modDir = $this->getFrontController()->getModuleDirectory();
		require_once($modDir.'/components/Menu/FolderBreadcrumbs2.php');
		$w = new Dms_Menu_FolderBreadcrumbs2($node);
		$this->view->assign('breadcrumbs', $w);
		
		$this->view->assign('currentNode', $node);
		$this->view->assign('limit', $limit);
		$this->view->assign('totalItems',$numOfRows);
		$this->view->assign('rowset',$rowset);
		$this->view->assign('paginator',$paginator);
		$this->view->assign('sortby',$sortby);
		
		$this->_helper->layout()->showperpage = $limit;
		$this->_helper->layout()->status = $status;
		
		$sortby = str_replace(array("desc","asc"), "", $sortby);
		$this->_helper->layout()->sort = trim($sortby);
	}
}