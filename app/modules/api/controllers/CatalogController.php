<?php

class Api_CatalogController extends Zend_Controller_Action 
{
	function removeFromFolderAction()
	{
		$req = $this->getRequest();
		$catalogGuid = $req->getParam('guid');
		$folderGuid = $req->getParam('folderGuid');
		
		$hol = new Pandamp_Core_Hol_Catalog();
		$hol->removeFromFolder($catalogGuid, $folderGuid);
		
		exit();
	}

	public function updatecatalogattributeAction()
	{
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
	
		$request = $this->getRequest();
	
		$catalogGuid = $request->getParam('guid');
		$title = $request->getParam('title');
		$attributeGuid = $request->getParam('attributeGuid');
	
		$catalogAttributeDb = new App_Model_Db_Table_CatalogAttribute();
		$catalogAttributeDb->update(array(
				'value'=>$title
		), array(
				'catalogGuid = ?' => $catalogGuid,
				'attributeGuid = ?'	=> $attributeGuid
		));
	
	
		//$indexingEngine = Pandamp_Search::manager();
		//$indexingEngine->indexCatalog($catalogGuid);
		
		try {
			$this->view->addHitsBySolr(json_encode([[
					"id" => $catalogGuid,
					"title" => ["set" => $title]
					]]));
		}
		catch (Zend_Exception $e)
		{}
	
	
		$this->getResponse()->setBody('RESULT_OK');
	}	
	
	public function delrelAction()
	{
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$request = $this->getRequest();
		
		$itemGuid = $request->getParam('itemGuid');
		$relatedGuid = $request->getParam('relatedGuid');
		$relateAs = $request->getParam('relateAs');
		
		$result  = 'RESULT_ERROR';
		if ($request->isPost()) {
			$catalogDb = new App_Model_Db_Table_Catalog();
			$catalogDb->update([
					'deletedDate' => new Zend_Db_Expr('NOW()'),
					'deletedBy' => Zend_Auth::getInstance()->getIdentity()->username,
					'status' => -1
				], "guid='".$itemGuid."'");
				
			$result = 'RESULT_OK';
		}
		
		try {
			$this->view->addHitsBySolr(json_encode([[
					"id" => $itemGuid,
					"deletedDate" => ["set" => date("Y-m-d\\TH:i:s\\Z")],
					"deletedBy" => ["set" => Zend_Auth::getInstance()->getIdentity()->username],
					"status" => ["set" => -1]
				]]));
		}
		catch (Zend_Exception $e)
		{
			
		}
		
		$this->getResponse()->setBody($result);
	}
}