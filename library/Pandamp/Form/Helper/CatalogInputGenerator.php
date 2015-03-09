<?php

class Pandamp_Form_Helper_CatalogInputGenerator
{
	function generateFormAdd($profileGuid, $folderGuid=null)
	{
            if(empty($folderGuid))
                    throw new Zend_Exception('Pandamp_Form_Helper_CatalogInputGenerator: Can not generate form with empty folderGuid');

            $tableProfileAttribute = new App_Model_Db_Table_ProfileAttribute();
            $where = $tableProfileAttribute->getAdapter()->quoteInto('profileGuid=?', $profileGuid);
            $rows = $tableProfileAttribute->fetchAll($where,'viewOrder ASC');
            $aRenderedAttributes = array();
            $aBaseAttributes = array();

            $i = 0;
            foreach ($rows as $row)
            {
                $row3 = $row->findParentRow('App_Model_Db_Table_Attribute');
                $attributeRenderer = new Pandamp_Form_Attribute_Renderer($row3->guid,null,$row3->type,null, $profileGuid,'partner');

                // $aRenderedAttributes[$row3->guid]['description'] = $row3->description;
                $aRenderedAttributes[$row3->guid]['description'] = ($row3->description == 'Category')? 'Kategori Klinik' : $row3->description;
                $aRenderedAttributes[$row3->guid]['form'] = $attributeRenderer->render();
                $i++;
            }

            $today = date('Y-m-d H:i:s');

            $aBaseAttributes['stickyCategory']['form'] = '';
            $aBaseAttributes['stickyCategory']['description'] = "<input type=\"checkbox\" name=\"stickyCategory\" value=\"1\" />&nbsp;Set this article sticky";
            $aBaseAttributes['shortTitle']['description'] = 'shortTitle';
            $aBaseAttributes['shortTitle']['form'] = "<textarea name='shortTitle' id='shortTitle' rows='1' cols='50'></textarea>";
            $aBaseAttributes['profileGuid']['description'] = 'Profile';
            $aBaseAttributes['profileGuid']['form'] = $profileGuid."<input type='hidden' name='profileGuid' id='profileGuid' value='$profileGuid'>";
            $aBaseAttributes['folderGuid']['description'] = '';
            $aBaseAttributes['folderGuid']['form'] = "<input type='hidden' name='folderGuid' id='folderGuid' value='$folderGuid'>";

            /*
            $aBaseAttributes['publishedDate']['description'] = '';
            $aBaseAttributes['publishedDate']['form'] = "<input type='hidden' name='publishedDate' id='publishedDate' value='$today'>";
            $aBaseAttributes['expiredDate']['description'] = '';
            $aBaseAttributes['expiredDate']['form'] = "<input type='hidden' name='expiredDate' id='expiredDate' value=''>";
            */

	    	$registry = Zend_Registry::getInstance();
	    	$config = $registry->get(Pandamp_Keys::REGISTRY_APP_OBJECT);
   	        $cdn = $config->getOption('cdn');

	    	
            $s = '<input type="Text" id="publishedDate" maxlength="25" size="25" name="publishedDate" value="'.$today.'"><a href="javascript:NewCal(\'publishedDate\',\'yyyymmdd\',true,24)"><img src="'.$cdn['static']['images'].'/img.gif" width="16" height="16" border="0" alt="Pick a date"></a>';
            $aBaseAttributes['publishedDate']['description'] = 'Published Date';
            $aBaseAttributes['publishedDate']['form'] = $s;

            $n = '<input type="Text" id="expiredDate" maxlength="25" size="25" name="expiredDate" value="0000-00-00 00:00:00"><a href="javascript:NewCal(\'expiredDate\',\'yyyymmdd\',true,24)"><img src="'.$cdn['static']['images'].'/img.gif" width="16" height="16" border="0" alt="Pick a date"></a>';
            $aBaseAttributes['expiredDate']['description'] = 'Expired Date';
            $aBaseAttributes['expiredDate']['form'] = $n;

            $aBaseAttributes['createdDate']['description'] = 'Created on';
            $aBaseAttributes['createdDate']['form'] = $today."<input type='hidden' name='createdDate' id='createdDate' value='$today'>";
            $aBaseAttributes['modifiedDate']['description'] = 'Modified on';
            $aBaseAttributes['modifiedDate']['form'] = "0000-00-00 00:00:00"."<input type='hidden' name='modifiedDate' id='modifiedDate' value='0000-00-00 00:00:00'>";
            $aBaseAttributes['deletedDate']['description'] = 'Deleted on';
            $aBaseAttributes['deletedDate']['form'] = "0000-00-00 00:00:00"."<input type='hidden' name='deletedDate' id='deletedDate' value='0000-00-00 00:00:00'>";

            $aBaseAttributes['status']['description'] = 'Status';

            $aBaseAttributes['price']['description'] = 'Price';
            $aBaseAttributes['price']['form'] = "<input type='text' name='price' id='price' value='0'>";
            
            require_once(CONFIG_PATH.'/master-status.php');
            $statusConfig = MasterStatus::getPublishingStatus();

            //$aBaseAttributes['status']['form'] = $statusConfig[0]."<input type='hidden' name='status' id='status' value='0'>";
            $attributeRenderer = new Pandamp_Form_Attribute_Renderer('status',99,101);
            $aBaseAttributes['status']['form'] = $attributeRenderer->render();

            $aReturn = array();
            $aReturn['baseForm'] = $aBaseAttributes;
            $aReturn['attributeForm'] = $aRenderedAttributes;

            return $aReturn;
	}
	function generateFormEdit($catalogGuid)
	{
		$zl = Zend_Registry::get("Zend_Locale");
		
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
			if(isset($rowAttribute))
			{
				if ($zl->getLanguage() == 'en')
					$attributeRenderer = new Pandamp_Form_Attribute_Renderer($rowAttribute->guid,$attributeValue,$rowAttribute->type,null, $rowCatalog->profileGuid,'clinic_partner');
				else
					$attributeRenderer = new Pandamp_Form_Attribute_Renderer($rowAttribute->guid,$attributeValue,$rowAttribute->type,null, $rowCatalog->profileGuid,'partner');
				
				$aRenderedAttributes[$rowAttribute->guid]['description'] = ($rowAttribute->description == 'Category')? 'Kategori Klinik' : $rowAttribute->description;
				
				$aRenderedAttributes[$rowAttribute->guid]['form'] = $attributeRenderer->render();
			}
			$i++;
		}
		
		$aBaseAttributes['guid']['description'] = '';
		$aBaseAttributes['guid']['form'] = "<input type='hidden' name='guid' id='guid' value='$rowCatalog->guid'>";
		$checked = ($rowCatalog->sticky == 1) ? 'checked="checked"' : '';
        $aBaseAttributes['stickyCategory']['form'] = '';
        $aBaseAttributes['stickyCategory']['description'] = "<input type=\"checkbox\" name=\"stickyCategory\" value=\"1\" $checked />&nbsp;Set this article sticky";
		$aBaseAttributes['shortTitle']['description'] = 'shortTitle';
		$aBaseAttributes['shortTitle']['form'] = "<textarea name='shortTitle' id='shortTitle' rows='1'' cols='50'>$rowCatalog->shortTitle</textarea>";
		$aBaseAttributes['profileGuid']['description'] = 'Profile';
		$aBaseAttributes['profileGuid']['form'] = $rowCatalog->profileGuid."<input type='hidden' name='profileGuid' id='profileGuid' value='$rowCatalog->profileGuid'>";
		
		//TO DO: I don't think we should put category/folder input here in cataloginputgenerator.
		/*$aBaseAttributes['folderGuid']['description'] = 'Category';
		$aBaseAttributes['folderGuid']['form'] = $folderGuid."<input type='hidden' name='folderGuid' id='folderGuid' value='$folderGuid'>";*/
		
    	$registry = Zend_Registry::getInstance();
    	$config = $registry->get(Pandamp_Keys::REGISTRY_APP_OBJECT);
        $cdn = $config->getOption('cdn');
   	        
		$s = '<input type="Text" id="publishedDate" maxlength="25" size="25" name="publishedDate" value="'.$rowCatalog->publishedDate.'"><a href="javascript:NewCal(\'publishedDate\',\'yyyymmdd\',true,24)"><img src="'.$cdn['static']['images'].'/img.gif" width="16" height="16" border="0" alt="Pick a date"></a>';
		$aBaseAttributes['publishedDate']['description'] = 'Published Date';
		$aBaseAttributes['publishedDate']['form'] = $s;
		
		$n = '<input type="Text" id="expiredDate" maxlength="25" size="25" name="expiredDate" value="'.$rowCatalog->expiredDate.'"><a href="javascript:NewCal(\'expiredDate\',\'yyyymmdd\',true,24)"><img src="'.$cdn['static']['images'].'/img.gif" width="16" height="16" border="0" alt="Pick a date"></a>';
		$aBaseAttributes['expiredDate']['description'] = 'Expired Date';
		$aBaseAttributes['expiredDate']['form'] = $n;
		
		/*
		$aBaseAttributes['publishedDate']['description'] = '';
		$aBaseAttributes['publishedDate']['form'] = "<input type='hidden' name='publishedDate' id='publishedDate' value='$rowCatalog->publishedDate'>";
		$aBaseAttributes['expiredDate']['description'] = '';
		$aBaseAttributes['expiredDate']['form'] = "<input type='hidden' name='expiredDate' id='expiredDate' value='$rowCatalog->expiredDate'>";
		*/
		
		$aBaseAttributes['createdDate']['description'] = 'Created on';
		$aBaseAttributes['createdDate']['form'] = $rowCatalog->createdDate."<input type='hidden' name='createdDate' id='createdDate' value='$rowCatalog->createdDate'>";
		$aBaseAttributes['modifiedDate']['description'] = 'Last Modified on';
		$aBaseAttributes['modifiedDate']['form'] = $rowCatalog->modifiedDate."<input type='hidden' name='modifiedDate' id='modifiedDate' value='$today'>";
		$aBaseAttributes['deletedDate']['description'] = 'Deleted on';
		$aBaseAttributes['deletedDate']['form'] = $rowCatalog->deletedDate."<input type='hidden' name='deletedDate' id='deletedDate' value='$rowCatalog->deletedDate'>";
		
		$aBaseAttributes['status']['description'] = 'Status';
		
		$aBaseAttributes['price']['description'] = 'Price';
		$aBaseAttributes['price']['form'] = "<input type='text' name='price' id='price' value='$rowCatalog->price'>";
		
		require_once(CONFIG_PATH.'/master-status.php');
		$statusConfig = MasterStatus::getPublishingStatus();
		
		//$aBaseAttributes['status']['form'] = $statusConfig[$rowCatalog->status]."<input type='hidden' name='status' id='status' value='$rowCatalog->status'>";
		$attributeRenderer = new Pandamp_Form_Attribute_Renderer('status',$rowCatalog->status,101);
		$aBaseAttributes['status']['form'] = $attributeRenderer->render();
		
		$aReturn = array();
		$aReturn['baseForm'] = $aBaseAttributes;
		$aReturn['attributeForm'] = $aRenderedAttributes;
		
		return $aReturn;
		
	}
}

?>