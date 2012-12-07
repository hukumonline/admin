<?php

/**
 * Description of InvoiceController
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Customer_InvoiceController extends Zend_Controller_Action
{
    protected $_user;
    protected $_zl;

    function  preDispatch()
    {
        $auth = Zend_Auth::getInstance();

		$identity = Pandamp_Application::getResource('identity');

		$loginUrl = $identity->loginUrl;
		
        $sReturn = "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
        $sReturn = base64_encode($sReturn);

        //$sso = new Pandamp_Session_Remote();
        //$user = $sso->getInfo();

        if (!$auth->hasIdentity()) {
            //$this->_forward('login','account','admin');
			
			$this->_redirect($loginUrl.'?returnUrl='.$sReturn);  
        }
        else
        {
            $this->_user = $auth->getIdentity();

            $this->_zl = Zend_Registry::get("Zend_Locale");
            
            $acl = Pandamp_Acl::manager();
            if (!$acl->checkAcl("site",'all','user', $this->_user->username, false,false))
            {
                //$this->_redirect(ROOT_URL.'/'.$this->_zl->getLanguage().'/error/restricted');
                $this->_forward('restricted','error','admin',array('lang'=>$this->_zl->getLanguage()));
            }
            
			// [TODO] else: check if user has access to admin page and status website is online
			$tblSetting = new App_Model_Db_Table_Setting();
			$rowset = $tblSetting->find(1)->current();
			
			if ($rowset)
			{
				if (($rowset->status == 1 && $this->_zl->getLanguage() == 'id') || ($rowset->status == 2 && $this->_zl->getLanguage() == 'en') || ($rowset->status == 3))
				{
					// it means that user offline other than admin
					$aReturn = App_Model_Show_AroGroup::show()->getUserGroup($this->_user->packageId);
					
					if (isset($aReturn['name']))
					{
						//if (($aReturn[1] !== "admin"))
						if (($aReturn['name'] !== "Master") && ($aReturn['name'] !== "Super Admin"))
						{
							$this->_forward('temporary','error','admin'); 
						}
					}
				}
			}
			
			// check session expire
			/*
			$timeLeftTillSessionExpires = $_SESSION['__ZF']['Zend_Auth']['ENT'] - time();

			if (Pandamp_Lib_Formater::diff('now', $this->_user->dtime) > $timeLeftTillSessionExpires) {
				$db->update('KutuUser',array('ses'=>'*'),"ses='".Zend_Session::getId()."'");
				$flashMessenger = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
		        $flashMessenger->addMessage('Session Expired');
		        $auth->clearIdentity();
		        
		        $this->_redirect($loginUrl.'?returnUrl='.$sReturn);     
			}
			
			$dat = Pandamp_Lib_Formater::now();
			$db->update('KutuUser',array('dtime'=>$dat),"ses='".Zend_Session::getId()."'");
			*/
        }
    }
	function confirmAction()
	{
        if (!Pandamp_Controller_Action_Helper_IsAllowed::isAllowed('membership','all'))
        {
            $this->_redirect(ROOT_URL.'/'.$this->_zl->getLanguage().'/error/restricted');
        }

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);
        
        $aResult = array();

        $id = ($this->_request->getParam('id'))? $this->_request->getParam('id') : '';

		$tblInvoice = new App_Model_Db_Table_Invoice();
		$rowInvoice = $tblInvoice->find($id)->current();
		
		if ($rowInvoice)
		{
			if ($rowInvoice->isPaid == "N") 
			{
				$tblUser = new App_Model_Db_Table_User();
				
		    	$sql = $tblUser->select()->setIntegrityCheck(false);
				$sql->from(array('ku' => 'KutuUser'))->join(array('gag' => 'gacl_aro_groups'),'ku.packageId = gag.id')
					->where('ku.kopel=?',$rowInvoice->uid);
		
				$rowUser = $tblUser->fetchRow($sql);
				
				if ($rowUser) 
				{
					if (in_array($rowUser->packageId,array(14,15,16,17,18)))
					{
						$rowInvoice->invoiceConfirmDate = date("Y-m-d");
						$rowInvoice->isPaid = 'Y';
						// get expiration date
						$temptime = time();
						$temptime = Pandamp_Lib_Formater::DateAdd('m',$rowUser->paymentId,$temptime);
						$rowInvoice->expirationDate = strftime('%Y-%m-%d',$temptime);
						$rowInvoice->save();
						
						$rowUser->periodeId = 3;
						$rowUser->modifiedDate = date("Y-m-d h:i:s");
						$rowUser->modifiedBy = "$rowUser->username";
						$result = $rowUser->save();
						
						if ($result)
						{
				            $aResult['isError'] = false;
				            $aResult['msg'] = $rowUser->username.", confirm saved";
						} else {
				            $aResult['isError'] = true;
				            $aResult['msg'] = "Error user confirmation!";
						}
						
					}
				}
				else 
				{
		            $aResult['isError'] = true;
		            $aResult['msg'] = 'There is no such user!';
				}
			}
			else 
			{
	            $aResult['isError'] = true;
	            $aResult['msg'] = 'Invoice has been paid!';
			}
		}
		else 
		{
            $aResult['isError'] = true;
            $aResult['msg'] = 'Wrong Package!';
		}
		
		echo Zend_Json::encode($aResult);
	}
	function deleteAction()
	{
        if (!Pandamp_Controller_Action_Helper_IsAllowed::isAllowed('membership','all'))
        {
            $this->_redirect(ROOT_URL.'/'.$this->_zl->getLanguage().'/error/restricted');
        }

		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$id = ($this->_request->getParam('id'))? $this->_request->getParam('id') : '';
		$change = ($this->_request->getParam('change'))? $this->_request->getParam('change') : '';
		
		$tblInvoice = new App_Model_Db_Table_Invoice();
		$rowInvoice = $tblInvoice->find($id)->current();
		
		if ($rowInvoice)
		{
			if ($change == "y")
			{
				$modelUser = new App_Model_Db_Table_User();
				$rowset = $modelUser->find($rowInvoice->uid)->current();
				
				if ($rowset)
				{
					$aclMan	= Pandamp_Acl::manager();
					$aclMan->deleteUser($rowset->username);
					// add user to gacl
					$aReturn = $aclMan->getGroupData(20);  // free
					//print_r($aReturn);
					$aclMan->addUser($rowset->username,$aReturn[2]);
					
		            $dataUserDetail = array(
		            	'userId'			=> $rowset->kopel
		            	,'packageId'		=> $rowset->packageId
		            	,'promotionId'		=> $rowset->promotionId
		            	,'educationId'		=> $rowset->educationId
		            	,'expenseId'		=> $rowset->expenseId
		            	,'paymentId'		=> $rowset->paymentId
		            	,'businessTypeId'	=> $rowset->businessTypeId
		            	,'periodeId'		=> $rowset->periodeId
		            	,'activationDate'	=> $rowset->activationDate
		            	,'createdDate'		=> $rowset->createdDate
		            	,'createdBy'		=> $rowset->createdBy
		            	,'modifiedDate'		=> $rowset->modifiedDate
		            	,'modifiedBy'		=> $rowset->modifiedBy
		            	,'isActive'			=> $rowset->isActive
		            	,'isContact'		=> $rowset->isContact
		            );
		            
		            $modelUserDetail = new App_Model_Db_Table_UserDetail();
		            $modelUserDetail->insert($dataUserDetail);
					
					$rowset->periodeId = 4;
					$rowset->modifiedDate = date("Y-m-d h:i:s");
					
					$rowset->save();
				}
			}
			
			$rowInvoice->delete();
		}
	}
	function newAction()
	{
        if (!Pandamp_Controller_Action_Helper_IsAllowed::isAllowed('membership','all'))
        {
            $this->_redirect(ROOT_URL.'/'.$this->_zl->getLanguage().'/error/restricted');
        }

		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
        $formater 	= new Pandamp_Core_Hol_User();
        
        $r = $this->getRequest();
		$kopel = $r->getParam('id');
		
		$tblInvoice = new App_Model_Db_Table_Invoice();
		$rowInvoice = $tblInvoice->fetchRow("uid='".$kopel."'");
		
		// add invoice but only if empty
		if (!$rowInvoice) {
		
	        $modelUser = new App_Model_Db_Table_User();
	        $rowset = $modelUser->find($kopel)->current();
	        
	        if (in_array($rowset->packageId,array(14,15,16,17,18)))
	        {
	        	$periodeId = 2;
	        	/**
	        	 * @modifiedDate: Dec 07, 2012
	        	 */
				// Get disc promo
				//$disc = $formater->checkPromoValidation('Disc',$rowset->packageId,$rowset->promotionId,$rowset->paymentId);
				// Get total promo
				//$total = $formater->checkPromoValidation('Total',$rowset->packageId,$rowset->promotionId,$rowset->paymentId);
				$total = $formater->checkPromoValidation($rowset->packageId,$rowset->paymentId);
				$formater->_writeInvoice($rowset->kopel,$total,0,$rowset->paymentId);
	        }
	        else 
	        {
	        	$periodeId = 3;
	        }
	        
	        $data = array(
	        	'periodeId' => $periodeId,
	        	'modifiedDate' => date("Y-m-d h:i:s"),
	            'isActive' => 1
	        );
	
	        $modelUser->update($data, "kopel='".$kopel."'");
		}
		
        $zl = Zend_Registry::get("Zend_Locale");

        $this->_redirect(ROOT_URL."/".$zl->getLanguage().'/customer/user/invoicelist/id/'.$kopel);
	}
	
	/**
	 * @modifiedDate: 2012-11-20 15:25
	 * @todo Changed $temptime = strtotime($rowset->expirationDate)
	 *
	 */
	function renewAction()
	{
        if (!Pandamp_Controller_Action_Helper_IsAllowed::isAllowed('membership','all'))
        {
            $this->_redirect(ROOT_URL.'/'.$this->_zl->getLanguage().'/error/restricted');
        }

		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$aResult = array();
		
		$id = ($this->_request->getParam('id'))? $this->_request->getParam('id') : '';
		
		$tblInvoice = new App_Model_Db_Table_Invoice();
		$rowset = $tblInvoice->fetchRow("invoiceId=".$id." AND isPaid='Y'");
		
		if ($rowset)
		{
			$rowInvoice = $tblInvoice->fetchNew();
			
			$rowInvoice->uid 				= $rowset->uid;
			
			$rowUser = App_Model_Show_User::show()->getUserById($rowset->uid);
			
			$tblPackage = new App_Model_Db_Table_Package();
			$rowPackage = $tblPackage->fetchRow("packageId=".$rowUser['packageId']."");
			if ($rowUser['paymentId'] == 12) {
				$price = $rowPackage->charge * 11;
			}
			else 
			{
				$price = $rowPackage->charge * $rowUser['paymentId'];
			}
			
			//$rowInvoice->price				= $rowset->price;
			$rowInvoice->price				= $price;
			$rowInvoice->discount			= $rowset->discount;
			$rowInvoice->invoiceOutDate 	= $rowset->expirationDate;
			$rowInvoice->invoiceConfirmDate	= date("Y-m-d");
			$rowInvoice->clientBankAccount	= $rowset->clientBankAccount;
			$rowInvoice->isPaid				= 'Y';
			
			// get expiration date
			//$temptime = time();
			$temptime = strtotime($rowset->expirationDate);
			$temptime = Pandamp_Lib_Formater::DateAdd('m',$rowUser['paymentId'],$temptime);
			$rowInvoice->expirationDate = strftime('%Y-%m-%d',$temptime);
			$rowInvoice->save();
			
            $aResult['isError'] = true;
            $aResult['msg'] = 'Invoice has been updated';
		}
		else 
		{
            $aResult['isError'] = true;
            $aResult['msg'] = 'Invalid Invoice ID';
		}
		
		echo Zend_Json::encode($aResult);
	}
}