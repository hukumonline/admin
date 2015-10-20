<?php
/**
 * @author	2011-2018 Nihki Prihadi <nihki@madaniyah.com>
 * @version $Id: GetLabelNode.php 1 2013-12-04 11:02Z $
 */

class Pandamp_Controller_Action_Helper_GetLabelNode
{
	public function getLabelNode($folderGuid)
	{
		$tblFolder = new App_Model_Db_Table_Folder();
		$rowFolder = $tblFolder->find($folderGuid)->current();
		if ($rowFolder) {
			$path = explode("/",$rowFolder->path);
			$rpath = $path[0];
			$rowFolder1 = $tblFolder->find($rpath)->current();
			if ($rowFolder1) {
				$rowFolder2 = $tblFolder->find($rowFolder1->parentGuid)->current();
				if ($rowFolder2) {
					if ($rowFolder2->title == "Peraturan") {
						return "nprt";
					}
					elseif ($rowFolder2->title == "Putusan") {
						return "npts";
					}
					else
					{
						return "node";
					}
				}
				else
				{
					return "node";
				}
			}
			else
			{
				return "node";
			}
		}
		else
		{
			return "node";
		}
		
	}
}