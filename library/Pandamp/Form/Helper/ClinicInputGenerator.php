<?php

class Pandamp_Form_Helper_ClinicInputGenerator
{
	function generateFormAdd()
	{
		$tableProfileAttribute = new App_Model_Db_Table_ProfileAttribute();
		$where = $tableProfileAttribute->getAdapter()->quoteInto('profileGuid=?', 'klinik');
		$rows = $tableProfileAttribute->fetchAll($where,'viewOrder ASC');
		$aRenderedAttributes = array();
		$aBaseAttributes = array();
		
		$i = 0;
		foreach ($rows as $row)
		{
			$row3 = $row->findParentRow('Pandamp_Modules_Dms_Catalog_Model_Attribute');
			$attributeRenderer = new Pandamp_Form_Attribute_Renderer($row3->guid,null,$row3->type,null);
			if ($row3->description == 'Jawaban' || $row3->description == 'Mitra' || $row3->description == 'Name' || $row3->description == 'Kategori Klinik') continue;
			$aRenderedAttributes[$row3->guid]['description'] = $row3->description;
			$aRenderedAttributes[$row3->guid]['form'] = $attributeRenderer->render();
			$i++;
		}
		
		$aBaseAttributes['profileGuid']['description'] = '';
		$aBaseAttributes['profileGuid']['form'] = "<input type='hidden' name='profileGuid' id='profileGuid' value='klinik'>";
		$aBaseAttributes['folderGuid']['description'] = '';
		$aBaseAttributes['folderGuid']['form'] = "<input type='hidden' name='folderGuid' id='folderGuid' value='lt4a0a533e31979'>";
		$aBaseAttributes['status']['description'] = '';
		$aBaseAttributes['status']['form'] = "<input type='hidden' name='status' id='status' value='0'>";
		
		$aReturn = array();
		$aReturn['baseForm'] = $aBaseAttributes;
		$aReturn['attributeForm'] = $aRenderedAttributes;
		
		return $aReturn;
	}
	function generateFormAnswer($catalogGuid)
	{
		$today = date('Y-m-d H:i:s');
		
		$aRenderedAttributes = array();
		$aBaseAttributes = array();
		
		$tableCatalog = new App_Model_Db_Table_Catalog();
		$rowsetCatalog = $tableCatalog->find($catalogGuid);
		$rowCatalog = $rowsetCatalog->current();
		
		$tableProfileAttribute = new App_Model_Db_Table_ProfileAttribute();
		$where = $tableProfileAttribute->getAdapter()->quoteInto('profileGuid=?', $rowCatalog->profileGuid);
		$rowsetProfileAttribute = $tableProfileAttribute->fetchAll($where,array('viewOrder ASC'));
		
		$rowsetCatalogAttribute = $rowCatalog->findDependentRowsetCatalogAttribute();
		
		$i = 0;
		foreach ($rowsetProfileAttribute as $row)
		{
			$rowCatalogAttribute = $rowsetCatalogAttribute->findByAttributeGuid($row->attributeGuid);
			
			$rowAttribute = $row->findParentRow('App_Model_Db_Table_Attribute');
			if(isset($rowCatalogAttribute->value))
				$attributeValue = $rowCatalogAttribute->value;
			else 
				$attributeValue = '';
			if(isset($rowCatalogAttribute->guid))
				$catalogAttributeGuid = $rowCatalogAttribute->guid;
			else 
			{
				$guidMan = new Pandamp_Core_Guid();
				$catalogAttributeGuid = $guidMan->generateGuid();
			}
				
			$attributeRenderer = new Pandamp_Form_Attribute_Renderer($rowAttribute->guid, $attributeValue, $rowAttribute->type,null,'author','partner');
			
			$aRenderedAttributes[$rowAttribute->guid]['description'] = $rowAttribute->description;
			$aRenderedAttributes[$rowAttribute->guid]['form'] = $attributeRenderer->render();
			$i++;
			
		}
		
		$aBaseAttributes['guid']['description'] = 'guid';
		$aBaseAttributes['guid']['form'] = $rowCatalog->guid."<input type='hidden' name='guid' id='guid' value='$rowCatalog->guid'>";
		$aBaseAttributes['shortTitle']['description'] = 'shortTitle';
		$aBaseAttributes['shortTitle']['form'] = "<textarea name='shortTitle' id='shortTitle' rows='1'' cols='50' readonly>$rowCatalog->shortTitle</textarea>";
		$aBaseAttributes['profileGuid']['description'] = 'profile';
		$aBaseAttributes['profileGuid']['form'] = $rowCatalog->profileGuid."<input type='hidden' name='profileGuid' id='profileGuid' value='$rowCatalog->profileGuid'>";
		
    	$registry = Zend_Registry::getInstance();
    	$config = $registry->get(Pandamp_Keys::REGISTRY_APP_OBJECT);
        $cdn = $config->getOption('cdn');
   	        
		$s = '<input type="Text" id="publishedDate" maxlength="25" size="25" name="publishedDate" value="'.$rowCatalog->publishedDate.'"><a href="javascript:NewCal(\'publishedDate\',\'yyyymmdd\',true,24)"><img src="'.$cdn['static']['images'].'/img.gif" width="16" height="16" border="0" alt="Pick a date"></a>';
		$aBaseAttributes['publishedDate']['description'] = 'Published Date';
		$aBaseAttributes['publishedDate']['form'] = $s;
		
		$n = '<input type="Text" id="expiredDate" maxlength="25" size="25" name="expiredDate" value="'.$rowCatalog->expiredDate.'"><a href="javascript:NewCal(\'expiredDate\',\'yyyymmdd\',true,24)"><img src="'.$cdn['static']['images'].'/img.gif" width="16" height="16" border="0" alt="Pick a date"></a>';
		$aBaseAttributes['expiredDate']['description'] = 'Expired Date';
		$aBaseAttributes['expiredDate']['form'] = $n;
		
		$aBaseAttributes['createdDate']['description'] = 'Created on';
		$aBaseAttributes['createdDate']['form'] = $rowCatalog->createdDate."<input type='hidden' name='createdDate' id='createdDate' value='$rowCatalog->createdDate'>";
		$aBaseAttributes['modifiedDate']['description'] = 'Last Modified on';
		$aBaseAttributes['modifiedDate']['form'] = $rowCatalog->modifiedDate."<input type='hidden' name='modifiedDate' id='modifiedDate' value='$today'>";
		$aBaseAttributes['deletedDate']['description'] = 'Deleted on';
		$aBaseAttributes['deletedDate']['form'] = $rowCatalog->deletedDate."<input type='hidden' name='deletedDate' id='deletedDate' value='$rowCatalog->deletedDate'>";
		
//		$aBaseAttributes['profileGuid']['description'] = 'Sender';
//		$aBaseAttributes['profileGuid']['form'] = "$rowCatalog->createdBy";
		
//		$aBaseAttributes['status']['description'] = '';
//		$aBaseAttributes['status']['form'] = "<input type='hidden' name='status' id='status' value='$rowCatalog->status'>";
		
		$aBaseAttributes['status']['description'] = 'Status';
		
		require_once(CONFIG_PATH.'/master-status.php');
		$statusConfig = MasterStatus::getPublishingStatus();
		
		$attributeRenderer = new Pandamp_Form_Attribute_Renderer('status',$rowCatalog->status,101);
		$aBaseAttributes['status']['form'] = $attributeRenderer->render();
		
		$aReturn = array();
		$aReturn['baseForm'] = $aBaseAttributes;
		$aReturn['attributeForm'] = $aRenderedAttributes;
		
		return $aReturn;
	}
}

?>