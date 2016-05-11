<?php
/**
 * @author	2011-2018 Nihki Prihadi <nihki@madaniyah.com>
 * @version $Id: GetNode.php 1 2013-12-04 11:44Z $
 */

class Pandamp_Controller_Action_Helper_GetNode
{
	public function getNode($catalogGuid)
	{
		$modelCatalogFolder = new App_Model_Db_Table_CatalogFolder();
		$rowset = $modelCatalogFolder->fetchRow("catalogGuid='".$catalogGuid."'");
		
		if ($rowset)
			return $rowset->folderGuid;
		else 
			return;
	}
}