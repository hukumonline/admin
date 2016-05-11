<?php

class Pandamp_Controller_Action_Helper_GetCatalogDocType
{
	public function GetCatalogDocType($catalogGuid, $relatedGuid=NULL)
	{
		$tblCatalog = new App_Model_Db_Table_Catalog();
		$rowsetCatalog = $tblCatalog->find($catalogGuid);

		if(count($rowsetCatalog))
		{
			$rowCatalog = $rowsetCatalog->current();
			$rowsetCatAtt = $rowCatalog->findDependentRowsetCatalogAttribute();

			$docType = $this->imageDocumentType($this->dl_file($rowsetCatAtt->findByAttributeGuid('docOriginalName')->value),$catalogGuid,$relatedGuid);
		}
		else
		{
			$docType = '';
		}

		return $docType;
	}
	static function dl_file($file)
	{
	    //Gather relevent info about file
	    $filename = basename($file);
	    $file_extension = strtolower(substr(strrchr($filename,"."),1));
	    return $file_extension;
	}
	static function imageDocumentType($type, $catalogGuid, $relatedGuid=NULL)
	{
	    $registry = Zend_Registry::getInstance();
	    $config = $registry->get(Pandamp_Keys::REGISTRY_APP_OBJECT);
	    $cdn = $config->getOption('cdn');
	    $sDir = $cdn['static']['images'];
		switch ($type)
		{
			case 'pdf':
				$type = '<img src="'.$sDir.'/file_type/pdf.gif">';
			break;
			case 'doc':
				$type = '<img src="'.$sDir.'/file_type/doc.gif">';
			break;
			case 'xls':
				$type = '<img src="'.$sDir.'/file_type/xls.gif">';
			break;
			case 'html':
			case 'htm':
				$type = '<img src="'.$sDir.'/file_type/html.gif">';
			break;
			case 'avi':
			case 'mpg':
			case 'mpeg':
			case 'flv':
			case 'mp3':
				$type = '<img src="'.$sDir.'/file_type/prefs.gif">';
			break;
			case 'gif':

//                                $sDir = ROOT_URL.'/uploads/images';
                                if (Pandamp_Lib_Formater::thumb_exists($sDir."/".$relatedGuid."/tn_".$catalogGuid.".gif")) { $thumb = $sDir."/".$relatedGuid."/tn_".$catalogGuid.".gif"; 	}
                                if (!isset($thumb)) { $thumb = $sDir."/file_type/image_new.gif"; }

                                $screenshot = "<img src=\"".$thumb."\" />";

				$type = $screenshot;

			break;
			case 'jpg':
			case 'jpeg':

//                                $sDir = ROOT_URL.'/uploads/images';
                                if (Pandamp_Lib_Formater::thumb_exists($sDir."/".$relatedGuid."/tn_".$catalogGuid.".jpg")) { $thumb = $sDir."/".$relatedGuid."/tn_".$catalogGuid.".jpg"; 	}
                                if (!isset($thumb)) { $thumb = $sDir."/file_type/jpg.gif"; }

                                $screenshot = "<img src=\"".$thumb."\" />";

				$type = $screenshot;
                                
			break;
			default:
				$type = '<img src="'.$sDir.'/file_type/txt.gif">';
		}

		return $type;
	}
}
