<?php

class Pandamp_Controller_Action_Helper_GetCatalogDocSize
{
	public function GetCatalogDocSize($catalogGuid)
	{
		$tblCatalog = new App_Model_Db_Table_Catalog();
		$rowsetCatalog = $tblCatalog->find($catalogGuid);

		if(count($rowsetCatalog))
		{
			$rowCatalog = $rowsetCatalog->current();
			$rowsetCatAtt = $rowCatalog->findDependentRowsetCatalogAttribute();

			$docSize = $this->bytesToString($rowsetCatAtt->findByAttributeGuid('docSize')->value);
		}
		else
		{
			$docSize = '0kB';
		}

		return $docSize;
	}
	static function bytesToString($size, $precision = 0) {
	    $sizes = array('YB', 'ZB', 'EB', 'PB', 'TB', 'GB', 'MB', 'kB', 'B');
	    $total = count($sizes);

	    while($total-- && $size > 1024) $size /= 1024;
	    return round($size, $precision).$sizes[$total];
	}
}
