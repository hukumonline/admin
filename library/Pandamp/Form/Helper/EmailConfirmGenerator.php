<?php

class Pandamp_Form_Helper_EmailConfirmGenerator
{
	function generateFormAdd($catalogGuid, $folderGuid=null)
	{ 
		$aRenderedAttributes = array();
		$aBaseAttributes = array();
		
		$tableCatalog = new Pandamp_Modules_Dms_Catalog_Model_Catalog();
		$rowsetCatalog = $tableCatalog->find($catalogGuid);
		$rowCatalog = $rowsetCatalog->current();
		
		if (!isset($rowCatalog)) 
		{
			$tableProfileAttribute = new Pandamp_Modules_Dms_Profile_Model_ProfileAttribute();
			$where = $tableProfileAttribute->getAdapter()->quoteInto('profileGuid=?','kutu_emailconfirm');
			$rows = $tableProfileAttribute->fetchAll($where, 'viewOrder ASC');
			$i=0;
			foreach ($rows as $row)
			{
				$rowset = $row->findParentRow('Pandamp_Modules_Dms_Catalog_Model_Attribute');
				$attributeRenderer = new Pandamp_Form_Attribute_Renderer($rowset->guid,null,$rowset->type,null);
				$aRenderedAttributes[$rowset->guid]['description'] = $rowset->description;
				$aRenderedAttributes[$rowset->guid]['form'] = $attributeRenderer->render();
				$i++;
			}
	
			$aBaseAttributes['profileGuid']['description'] = '';
			$aBaseAttributes['profileGuid']['form'] = "<input type='hidden' name='profileGuid' id='profileGuid' value='kutu_emailconfirm'>";
			$aBaseAttributes['folderGuid']['description'] = '';
			$aBaseAttributes['folderGuid']['form'] = "<input type='hidden' name='folderGuid' id='folderGuid' value='$folderGuid'>";
			$aBaseAttributes['status']['description'] = '';
			$aBaseAttributes['status']['form'] = "<input type='hidden' name='status' id='status' value='1'>";
					
		}
		else
		{
			$tableProfileAttributes = new Pandamp_Modules_Dms_Profile_Model_ProfileAttribute();
			$where = $tableProfileAttributes->getAdapter()->quoteInto('profileGuid=?', $rowCatalog->profileGuid);
			$rowsetProfileAttributes = $tableProfileAttributes->fetchAll($where, array('viewOrder ASC'));
			
			$rowsetCatalogAttribute = $rowCatalog->findDependentRowsetCatalogAttribute();
			
			$i = 0;
			foreach ($rowsetProfileAttributes as $row)
			{
				$rowCatalogAttribute = $rowsetCatalogAttribute->findByAttributeGuid($row->attributeGuid);

				$rowAttribute = $row->findParentRow('Pandamp_Modules_Dms_Catalog_Model_Attribute');
				if (isset($rowCatalogAttribute->value))
					$attributeValue = $rowCatalogAttribute->value;
				else 
					$attributeValue = '';
				if (isset($rowCatalogAttribute->guid))
					$catalogAttributeGuid = $rowCatalogAttribute->guid;
				else 
				{
					$guidMan = new Pandamp_Core_Guid();
					$catalogAttributeGuid = $guidMan->generateGuid();
				}
				
				$attributeRenderer = new Pandamp_Form_Attribute_Renderer($rowAttribute->guid,$attributeValue,$rowAttribute->type,null);
				
				$aRenderedAttributes[$rowAttribute->guid]['description'] = $rowAttribute->description;
				$aRenderedAttributes[$rowAttribute->guid]['form'] = $attributeRenderer->render();
				$i++;
			}
			
			$aBaseAttributes['guid']['description'] = '';
			$aBaseAttributes['guid']['form'] = "<input type='hidden' name='guid' id='guid' value='$rowCatalog->guid'>";
			$aBaseAttributes['profileGuid']['description'] = '';
			$aBaseAttributes['profileGuid']['form'] = "<input type='hidden' name='profileGuid' id='profileGuid' value='$rowCatalog->profileGuid'>";
			$aBaseAttributes['status']['description'] = '';
			$aBaseAttributes['status']['form'] = "<input type='hidden' name='status' id='status' value='1'>";
			
		}
					
		$aReturn = array();
		$aReturn['baseForm'] = $aBaseAttributes;
		$aReturn['attributeForm'] = $aRenderedAttributes;
		
		return $aReturn;
	}
}