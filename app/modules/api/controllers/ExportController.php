<?php
/**
 * @author	2011-2018 Nihki Prihadi <nihki@madaniyah.com>
 * @version $Id: ExportController.php 1 2014-01-29 10:49
 */

class Api_ExportController extends Zend_Controller_Action
{
	public function init()
	{
		$excelConfig = array(
				'excel' => array(
						'suffix' 	=> 'excel',
						'headers'	=> array(
								'Content-Type'	=> 'application/vnd.ms-excel',
								'Content-Disposition'	=> "attachment; filename=".date('Ymd').".xls",
								'Pragma'		=> 'no-cache',
								'Expires'		=> '0'
						)
				),
				'json' => array(
						'suffix'    => 'json',
						'headers'   => array('Content-Type' => 'application/json'),
						'callbacks' => array(
								'init' => 'initJsonContext',
								'post' => 'postJsonContext'
						)
				)
		);
		 
		$contextSwitch = $this->_helper->contextSwitch();
		 
		$contextSwitch->setContexts($excelConfig);
		 
		$contextSwitch->addActionContext('peraturan', 'excel')
					->addActionContext('report.by.selection', 'excel')
					->addActionContext('reportsearch.by.selection', 'excel')
					->initContext();
	}
	
	public function peraturanAction()
	{
		$request = $this->getRequest();
		
		$folderGuid = $request->getParam('folderGuid');
		
		$this->view->assign('folderGuid',$folderGuid);
	}
	
	public function reportBySelectionAction()
	{
		$request = $this->getRequest();
		
		$catalogGuid = $request->getParam('querySearch');
		$folderGuid = $request->getParam('folderGuid');
		
		$this->view->assign('folderGuid',$folderGuid);
		
		$selectedRows = Zend_Json::decode($catalogGuid);
		$this->view->assign('selectedRows',$selectedRows);
	}
	
	public function reportsearchBySelectionAction()
	{
		$request = $this->getRequest();
		
		$catalogGuid = $request->getParam('querySearch');
		$daterange = $request->getParam('dateRange');
		
		if (null != $catalogGuid) {
			//$exp = rawurldecode(base64_decode($catalogGuid));
			$selectedRows = Zend_Json::decode($catalogGuid);
			$this->view->assign('selectedRows',$selectedRows);
			
			//$daterange = rawurldecode(base64_decode($daterange));
			//$daterange = Zend_Json::decode($daterange);
			
			$this->view->assign('daterange',$daterange);
		}
	}
	
}
