<?php
class Error_ErrorController extends Zend_Controller_Action
{
	public function notifyAction()
	{
		$req = $this->getRequest();
		$type = ($req->getParam('type'))? $req->getParam('type') : '';
		$num =  ($req->getParam('num'))? $req->getParam('num') : '';
		$msg =  ($req->getParam('msg'))? $req->getParam('msg') : '';
		switch ($type)
		{
			case "folder":
				switch ($num) 
				{
					case 101:
						$error_msg = $msg;
						break;
				}
			break;
		}
		$this->view->error_msg = $error_msg;
	}
}