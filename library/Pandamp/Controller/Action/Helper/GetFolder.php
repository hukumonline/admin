<?php
/**
 * @author	2011-2018 Nihki Prihadi <nihki@madaniyah.com>
 * @version $Id: GetFolder.php 1 2016-04-11 17:34Z $
 */

class Pandamp_Controller_Action_Helper_GetFolder
{
	public function getFolder($folderGuid)
	{
		$modelFolder = new App_Model_Db_Table_Folder();
		$rowset = $modelFolder->find($folderGuid)->current();

		if ($rowset)
			return $rowset;
		else
			return;
	}
}