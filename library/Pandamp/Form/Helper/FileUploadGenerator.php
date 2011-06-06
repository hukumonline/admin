<?php

class Pandamp_Form_Helper_FileUploadGenerator
{
	function generateFormAdd($relatedGuid)
	{
		$aBaseAttributes = array();
		$aBaseAttributes['profileGuid']['description'] = '';
		$aBaseAttributes['profileGuid']['form'] = "<input type='hidden' name='profileGuid' id='profileGuid' value='kutu_doc'>";
		$aBaseAttributes['uploadedFile']['description'] = 'Upload File';
		$aBaseAttributes['uploadedFile']['form'] = "<input type='file' name='uploadedFile' id='uploadedFile' size='50'>";
		$aBaseAttributes['relatedGuid']['description'] = '';
		$aBaseAttributes['relatedGuid']['form'] = "<input type='hidden' name='relatedGuid' id='relatedGuid' value='$relatedGuid'>";
		$tableProfileAttribute = new Pandamp_Modules_Dms_Profile_Model_ProfileAttribute();
		$where = $tableProfileAttribute->getAdapter()->quoteInto('profileGuid=?', 'kutu_doc');
		$rows = $tableProfileAttribute->fetchAll($where,'viewOrder ASC');
		$aRenderedAttributes = array();
		
		$i = 0;
		foreach ($rows as $row)
		{
			$row3 = $row->findParentRow('Pandamp_Modules_Dms_Catalog_Model_Attribute');
			$attributeRenderer = new Pandamp_Form_Attribute_Renderer($row3->guid,null,$row3->type,null,'kutu_doc');
			$aRenderedAttributes[$row3->guid]['description'] = $row3->description;
			$aRenderedAttributes[$row3->guid]['form'] = $attributeRenderer->render();
			$i++;
			
		}
		
		$aReturn = array();
		$aReturn['baseForm'] = $aBaseAttributes;
		$aReturn['attributeForm'] = $aRenderedAttributes;
		
		return $aReturn;
	}
}

?>