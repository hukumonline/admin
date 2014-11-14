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
        $req = $this->getRequest();
        $itemGuid = ($req->getParam('itemGuid'))? $req->getParam('itemGuid') : 'XXX';
        $relatedGuid = ($req->getParam('relatedGuid')) ? $req->getParam('relatedGuid') : 'XXX';
        $relateAs = ($req->getParam('relateAs')) ? $req->getParam('relateAs') : 'XXX';

        $hol = new Pandamp_Core_Hol_Relation();
        $hol->delete($itemGuid,$relatedGuid,$relateAs);

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
}
