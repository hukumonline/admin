<?php
class Dev_FolderController extends Zend_Controller_Action 
{
	function preDispatch()
	{
		$this->_helper->layout->setLayout('layout-customer-migration');
	}
	function folderAction()
	{
		$this->_helper->viewRenderer->setNoRender(TRUE);
		$traverse = $this->_traverseFolder_('lt4dc919d4046b3','', 0);
		print_r($traverse);
	}
	protected function _traverseFolder($folderGuid, $sGuid, $level)
	{
		$tblFolder = new App_Model_Db_Table_Migration_Detik_Folder();
		$rowSet = $tblFolder->fetchChildren($folderGuid);
		$row = $tblFolder->find($folderGuid)->current();
		$sGuid = '';
			if(count($rowSet))
			{
				$sGuid = '<li>'."<a href='".ROOT_URL."/pages/g/$row->guid/h/1'>".$row->title.'</a><ul>';
			}
			else
			{
				$sGuid = '<li>'."<a href='".ROOT_URL."/pages/g/$row->guid/h/1'>".$row->title.'</a>';
			}
		
		if(true)
		{
			//echo $level;
			foreach($rowSet as $row)
			{
				//$sTab = '<ul>';
				//$sTab = '';
				//for($i=0;$i<$level;$i++)
					//$sTab .= '<li>';
				
				//$option = '<option value="'.$row->guid.'">'.$sTab.$row->title.'</option>';
				//$option = '"'.$row->guid.'" :'.'"'.$sTab.$row->title.'",';
				//$option = $sTab.$row->title;
				$sGuid .= $this->_traverseFolder($row->guid, '', $level+1)."";
			
				//$sGuid .= $sTab.$row->title . '|<br>'. $this->_traverseFolder($row->guid, '', $level+1);
			
			}
			if(count($rowSet))
			{
				return $sGuid.'</ul></li>';
			}
			else
			{
				return $sGuid.'</li>';
			}
		}
		
	}
	protected function _traverseFolder_($folderGuid, $sGuid, $level)
	{
		$tblFolder = new App_Model_Db_Table_Folder();
		$rowSet = $tblFolder->fetchChildren($folderGuid);
		$row = $tblFolder->find($folderGuid)->current();
		$sGuid = '';
			/*
			if(count($rowSet))
			{
				$sGuid = $row->guid;
			}
			else
			{
				$sGuid = $row->guid;
			}
			*/
		
//		if(true)
//		{
			//echo $level;
			foreach($rowSet as $row)
			{
				//$sTab = '<ul>';
				//$sTab = '';
				//for($i=0;$i<$level;$i++)
					//$sTab .= '<li>';
				
				//$option = '<option value="'.$row->guid.'">'.$sTab.$row->title.'</option>';
				//$option = '"'.$row->guid.'" :'.'"'.$sTab.$row->title.'",';
				//$option = $sTab.$row->title;
				$sGuid .= $this->_traverseFolder_($row->guid, '', $level+1)."";
			//echo $row->guid.'<br>';
			echo 'Insert '.$row->title.'<br>';
			$tblFolder = new App_Model_Db_Table_Migration_Detik_Folder();
			$rowFolder = $tblFolder->fetchNew();
			$rowFolder->guid = $row->guid;
			$rowFolder->title = $row->title;
			$rowFolder->description = $row->description;
			$rowFolder->parentGuid = $row->parentGuid;
			$rowFolder->path = $row->path;
			$rowFolder->type = $row->type;
			$rowFolder->viewOrder = $row->viewOrder;
			$rowFolder->cmsParams = $row->cmsParams;
			//$rowFolder->save();
			
			$rowCatalog = App_Model_Show_Catalog::show()->fetchCatalogInFolder4Mig($row->guid);
			//print_r($row->guid);die();
			if ($rowCatalog) {
			foreach ($rowCatalog as $rc)
			{
				$rowsetCatalogAttributeJenis = App_Model_Show_CatalogAttribute::show()->getCatalogAttributeValue($rc['guid'],'prtJenis');
				//print_r($rowsetCatalogAttributeJenis);
				if (($rowsetCatalogAttributeJenis == 'Undang-Undang ') || ($rowsetCatalogAttributeJenis == "uu") || ($rowsetCatalogAttributeJenis == "pp") || ($rowsetCatalogAttributeJenis == "Peraturan Pemerintah") || ($rowsetCatalogAttributeJenis == "konstitusi"))
				{
					$modelMigrationCatalog = new App_Model_Db_Table_Migration_Detik_Catalog();
					$where = $modelMigrationCatalog->getAdapter()->quoteInto('guid=?', $rc['guid']);
					
					if(!$modelMigrationCatalog->fetchRow($where)) {
						
					$data1 = array(
						 'guid'				=> $rc['guid']
						,'shortTitle'		=> $rc['shortTitle']
						,'profileGuid'		=> $rc['profileGuid']
						,'publishedDate'	=> $rc['publishedDate']
						,'expiredDate' 		=> $rc['expiredDate']
						,'createdBy'		=> $rc['createdBy']
						,'modifiedBy'		=> $rc['modifiedBy']
						,'createdDate'		=> $rc['createdDate']
						,'modifiedDate'		=> $rc['modifiedDate']
						,'deletedDate'		=> $rc['deletedDate']
						,'price'			=> (isset($rc['price']))? $rc['price'] : 0
						,'status'			=> $rc['status']
					);
					
					$modelMigrationCatalog->insert($data1);
					
					
					$tblCatalogAttribute = new App_Model_Db_Table_CatalogAttribute();
					$rcam = $tblCatalogAttribute->fetchAll("catalogGuid='".$rc['guid']."'");
					foreach ($rcam as $rowca)
					{
						$modelMigrationCatalogAttribute = new App_Model_Db_Table_Migration_Detik_CatalogAttribute();
						$data2 = array(
							 'catalogGuid'		=> $rowca->catalogGuid
							,'attributeGuid'	=> $rowca->attributeGuid
							,'value'			=> $rowca->value
						);
						$modelMigrationCatalogAttribute->insert($data2);
					}
					
					$tblCatalogFolder = new App_Model_Db_Table_CatalogFolder();
					$rcfm = $tblCatalogFolder->fetchAll("catalogGuid='".$rc['guid']."'");
					foreach ($rcfm as $rowcf)
					{
						$modelMigrationCatalogFolder = new App_Model_Db_Table_Migration_Detik_CatalogFolder();
						$data3 = array(
							 'catalogGuid'		=> $rowcf->catalogGuid
							,'folderGuid'		=> $rowcf->folderGuid
						);
						$modelMigrationCatalogFolder->insert($data3);
					}
					
					$tblRelatedItem = new App_Model_Db_Table_RelatedItem();
					$rrim = $tblRelatedItem->fetchAll("relatedGuid='".$rc['guid']."'");
					foreach ($rrim as $rowri)
					{
						$modelMigrationRelatedItem = new App_Model_Db_Table_Migration_Detik_RelatedItem();
						$rowRelated = $modelMigrationRelatedItem->createNew();
			            $rowRelated->itemGuid = $rowri->itemGuid;
			            $rowRelated->relatedGuid = $rowri->relatedGuid;
			            $rowRelated->relateAs = $rowri->relateAs;
			            $rowRelated->valueIntRelation = $rowri->valueIntRelation;
						$rowRelated->save();
					}
					
					
					
					}
			
					
				}
			}
			}
				//$sGuid .= $sTab.$row->title . '|<br>'. $this->_traverseFolder($row->guid, '', $level+1);
			 
			}
//			if(count($rowSet))
//			{
//				return $sGuid;
//			}
//			else
//			{
//				return $sGuid;
//			}
//		}
	}
}