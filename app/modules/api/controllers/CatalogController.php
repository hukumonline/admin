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
	
}