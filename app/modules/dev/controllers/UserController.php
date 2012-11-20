<?php

class Dev_UserController extends Zend_Controller_Action 
{
	public function invoiceAction()
	{
		$this->_helper->layout->disableLayout();
		$this->_helper->getHelper('viewRenderer')->setNoRender(TRUE);
		
		$tblUser = new App_Model_Db_Table_User();
		$rowUser = $tblUser->fetchRow("kopel='00015'");
		
		$temptime = strtotime($rowUser->createdDate);
		
			
		//$temptime = time();
		$temptime = Pandamp_Lib_Formater::DateAdd('d',5,$temptime);
		$rowInvoice = strftime('%Y-%m-%d',$temptime);
		Pandamp_Debug::manager($rowInvoice);
	}
}