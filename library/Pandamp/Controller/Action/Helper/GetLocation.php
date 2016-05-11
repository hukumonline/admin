<?php
/**
 * @author	2011-2018 Nihki Prihadi <nihki@madaniyah.com>
 * @version $Id: GetLocation.php 1 2013-12-05 15:23Z $
 */

class Pandamp_Controller_Action_Helper_GetLocation
{
	public function getLocation($catalogGuid)
	{
		$catalogDb = new App_Model_Db_Table_Catalog();
		$catalogs = $catalogDb->find($catalogGuid)->current();
		$folders = $catalogs->findManyToManyRowset('App_Model_Db_Table_Folder', 'App_Model_Db_Table_CatalogFolder');
		
		if ($folders) {
			return $folders;
		}
		
		return;
	}
}