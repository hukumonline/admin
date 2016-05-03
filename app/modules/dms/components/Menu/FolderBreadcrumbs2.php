<?php
class Dms_Menu_FolderBreadcrumbs2
{
	public $view;
	public $folderGuid;
	public $rootGuid;
	
	public function __construct($folderGuid, $rootGuid='')
	{
		$this->view = new Zend_View();
		$this->view->setScriptPath(dirname(__FILE__));
		
		$this->view->addHelperPath(ROOT_DIR.'/library/Pandamp/Controller/Action/Helper', 'Pandamp_Controller_Action_Helper');
	
		$this->folderGuid = $folderGuid;
		$this->rootGuid = $rootGuid;
	
		$this->view();
	}
	public function view()
	{
		$zl = Zend_Registry::get("Zend_Locale");
		$browserUrl = ROOT_URL . '/'.$zl->getLanguage().'/dms/explorer/browse/node';
		
		$folderGuid = ($this->folderGuid)? $this->folderGuid : 'root';
		
		$tblFolder = new App_Model_Db_Table_Folder();
		
		$aPath = array();
		
		if($folderGuid == 'root')
		{
			$aPath[0]['title'] = 'Root';
			$aPath[0]['url'] = $browserUrl;
		}
		else
		{
			$rowFolder = $tblFolder->find($folderGuid)->current();
			if(!empty($rowFolder->path))
			{
				$aFolderGuid = explode("/", $rowFolder->path);
				$sPath = 'root >';
				$aPath[0]['title'] = 'Root';
				$aPath[0]['url'] = $browserUrl;
				$i = 1;
				if(count($aFolderGuid))
				{
					$sPath1 = '';
					foreach ($aFolderGuid as $guid)
					{
						if(!empty($guid))
						{
							$rowFolder1 = $tblFolder->find($guid)->current();
							$sPath1 .= $rowFolder1->title . ' > ';
							$aPath[$i]['title'] = $rowFolder1->title;
							$aPath[$i]['url'] = $browserUrl . DS . $rowFolder1->guid;
							$i++;
						}
					}
		
					$aPath[$i]['title'] = $rowFolder->title;
		
					$aPath[$i]['url'] = $browserUrl . DS . $rowFolder->guid;
				}
		
			}
			else
			{
				if ($rowFolder) {
					$aPath[0]['title'] = 'Root';
					$aPath[0]['url'] = $browserUrl;
					$aPath[1]['title'] = $rowFolder->title;
					$aPath[1]['url'] = $browserUrl . DS . $rowFolder->guid;
				}
				else
				{
					die();
				}
			}
		}
		
		$this->view->assign('aPath', $aPath);
		
		$a=$this->_tf($folderGuid,'', 0);
		 
		Zend_Layout::getMvcInstance()->assign('folderGuid', $folderGuid);
		Zend_Layout::getMvcInstance()->assign('breadcrumb', rtrim($a,','));
	}

	private function _tf($folderGuid, $sGuid, $level)
	{
		$tblFolder = new App_Model_Db_Table_Folder();
		$rowSet = $tblFolder->fetchAll("guid = '$folderGuid' AND NOT parentGuid=guid");
		$sGuid = '';
	
		foreach($rowSet as $row)
		{
			$option = '"'.$row->parentGuid.'",';
			$sGuid .= $option . $this->_tf($row->parentGuid, '', $level+1);
	
		}
		return $sGuid;
	}
	
	public function render()
	{
		return $this->view->render(str_replace('.php','.phtml',strtolower(basename(__FILE__))));
	}
}