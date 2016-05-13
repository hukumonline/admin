<?php

/**
 * Description of RelationController
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Api_RelationController extends Zend_Controller_Action
{
    function deleteAction()
    {
    	$this->_helper->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(TRUE);
    	
        $req = $this->getRequest();
        $itemGuid = ($req->getParam('itemGuid'))? $req->getParam('itemGuid') : 'XXX';
        $relatedGuid = ($req->getParam('relatedGuid')) ? $req->getParam('relatedGuid') : 'XXX';
        $relateAs = ($req->getParam('relateAs')) ? $req->getParam('relateAs') : 'XXX';

        //$hol = new Pandamp_Core_Hol_Relation();
        //$hol->delete($itemGuid,$relatedGuid,$relateAs);
        
        $tblRelatedItem = new App_Model_Db_Table_RelatedItem();
        $tblRelatedItem->delete("itemGuid='$itemGuid' AND relatedGuid='$relatedGuid' AND relateAs='$relateAs'");

        $this->getResponse()->setBody('RESULT_OK');
    }
    function deleteparenthistoryAction()
    {
    	$request = $this->getRequest();
    	
    	$catalogGuid = $request->getParam('guid');
    	
    	$tblRelatedItem = new App_Model_Db_Table_RelatedItem();
    	$tblRelatedItem->delete("valueStringRelation='$catalogGuid'");
    	
    	exit();
    }
    function deletehistoryAction()
    {
    	$req = $this->getRequest();
    	$itemGuid = ($req->getParam('itemGuid'))? $req->getParam('itemGuid') : 'XXX';
    	$relatedGuid = ($req->getParam('relatedGuid')) ? $req->getParam('relatedGuid') : 'XXX';
    	$relateAs = ($req->getParam('relateAs')) ? $req->getParam('relateAs') : 'XXX';
    	
    	$tblRelatedItem = new App_Model_Db_Table_RelatedItem();
    	$tblRelatedItem->delete("itemGuid='$itemGuid' AND relatedGuid='$relatedGuid' AND relateAs='$relateAs'");
    	
    	exit();
    }
    function catalogorderAction()
    {
    	$this->_helper->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(TRUE);
    	
    	$request = $this->getRequest();
    	$result  = 'RESULT_ERROR';
    	if ($request->isPost()) {
    		$viewOrder 	= $request->getPost('order');
    		$id     	= $request->getPost('id');
    			
    		$tblCatalogAttribute = new App_Model_Db_Table_CatalogAttribute();
    		$where2 = "catalogGuid='$id' AND attributeGuid='docViewOrder'";
    		$rowCatalogAttribute = $tblCatalogAttribute->fetchRow($where2);
    			
    		if ($rowCatalogAttribute)
    		{
    			$rowCatalogAttribute->value = $viewOrder;
    			$rowCatalogAttribute->save();
    				
    		}
    	
    		$result = 'RESULT_OK';
    	}
    	$this->getResponse()->setBody($result);
    }
}
