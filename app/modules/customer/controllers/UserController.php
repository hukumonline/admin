<?php

/**
 * Description of UserController
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Customer_UserController extends Zend_Controller_Action
{
    protected $_user;
    protected $_zl;
    protected $_acl;

    function  preDispatch()
    {
        $auth = Zend_Auth::getInstance();

		$identity = Pandamp_Application::getResource('identity');

		$loginUrl = $identity->loginUrl;
		
		/*
		$multidb = Pandamp_Application::getResource('multidb');
		$multidb->init();
		
		$db = $multidb->getDb('db2');
		*/
		
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

            $this->_acl = Pandamp_Acl::manager();
            if (!$this->_acl->checkAcl("site",'all','user', $this->_user->username, false,false))
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
    function headerAction()
    {
        $r = $this->getRequest();
        $sOffset = $r->getParam('sOffset');
        $this->view->sOffset = $sOffset;
        $sLimit = $r->getParam('sLimit');
        $this->view->sLimit = $sLimit;

        $query = ($r->getParam('q'))? $r->getParam('q') : '';
        $this->_helper->layout()->searchQuery = $query;
        $this->view->user = $this->_user;
    }
    
    /*
    function approvalAction()
    {
        $userQueue = App_Model_Show_User::show()->getUserQueue();

        $this->view->userQueue = $userQueue;
        $this->view->user = $this->_user;
    }
    */
    
    function approvalAction()
    {
    	$userQueue = App_Model_Show_User::show()->getUserQueue();
        
		$a['totalCount'] = count($userQueue);
		$limit = 25;
		$a['limit'] = $limit;
		
		$this->view->aData = $a;

        $this->view->user = $this->_user;
    }
    function setApprovalAction()
    {
		$this->_helper->getHelper('layout')->disableLayout();
		$this->_helper->getHelper('viewRenderer')->setNoRender();
		
		$request = $this->getRequest();
        $result  = 'RESULT_ERROR';
        
        if (Pandamp_Controller_Action_Helper_IsAllowed::isAllowed('membership','all'))
        {
        	if ($request->isPost()) {
        		$formater = new Pandamp_Core_Hol_User();
        		
        		$id     = $request->getPost('id');
        		$ids    = array();
        		$ids = Zend_Json::decode($id);
        		
   		        $modelUser = new App_Model_Db_Table_User();

        		foreach ($ids as $id) {
					$rowset = $modelUser->find($id)->current();
					if ($rowset != null) 
					{
				        if (in_array($rowset->packageId,array(14,15,16,17,18,36,37,38)))
				        {
				        	$periodeId = 2;
							// Get disc promo
							//$disc = $formater->checkPromoValidation('Disc',$rowset->packageId,$rowset->promotionId,$rowset->paymentId);
							// Get total promo
							//$total = $formater->checkPromoValidation('Total',$rowset->packageId,$rowset->promotionId,$rowset->paymentId);
							//$formater->_writeInvoice($rowset->kopel,$total,$disc,$rowset->paymentId);
				        }
				        else 
				        {
				        	$periodeId = 3;
				        }
				        
				        $data = array(
				        	'periodeId' => $periodeId,
				        	'activationDate' => date("Y-m-d h:i:s"),
				            'isActive' => 1
				        );
				
				        $modelUser->update($data, "kopel='".$id."'");
						
					}
        		}
        	}
        	$result = 'RESULT_OK';
        }
        
        $this->getResponse()->setBody($result);
    }
    
    function setSuspendedAction()
    {
		$this->_helper->getHelper('layout')->disableLayout();
		$this->_helper->getHelper('viewRenderer')->setNoRender();
		
		$request = $this->getRequest();
        $result  = 'RESULT_ERROR';
        
        if (Pandamp_Controller_Action_Helper_IsAllowed::isAllowed('membership','all'))
        {
        	if ($request->isPost()) {
        		$formater = new Pandamp_Core_Hol_User();
        		
        		$id     = $request->getPost('id');
        		$ids    = array();
        		$ids = Zend_Json::decode($id);
        		
   		        $modelUser = new App_Model_Db_Table_User();

        		foreach ($ids as $id) {
					$rowset = $modelUser->find($id)->current();
					if ($rowset != null) 
					{
				        $data = array(
				        	'periodeId' => 6,
				        	'modifiedDate' => date("Y-m-d h:i:s"),
				        	'modifiedBy' => $this->_user->username,
				            'isActive' => 0
				        );
				
				        $modelUser->update($data, "kopel='".$id."'");
						
					}
        		}
        	}
        	$result = 'RESULT_OK';
        }
        
        $this->getResponse()->setBody($result);
    }
    
	/**	
	 * TODO
	 * admin setActive
	 */
	function setActiveAction()
	{
		$this->_helper->getHelper('layout')->disableLayout();
		$this->_helper->getHelper('viewRenderer')->setNoRender();
		
		$request = $this->getRequest();
        $result  = 'RESULT_ERROR';
        
        if (Pandamp_Controller_Action_Helper_IsAllowed::isAllowed('membership','all'))
        {
        	if ($request->isPost()) {
        		$id  = $request->getPost('id');
        		$ids = array();
        		$ids = Zend_Json::decode($id);
        		
   		        $modelUser = new App_Model_Db_Table_User();

        		foreach ($ids as $id) {
					$rowset = $modelUser->find($id)->current();
					if ($rowset != null) 
					{
						if (($rowset->isActive == 0)) 
						{
					        if (in_array($rowset->packageId,array(14,15,16,17,18,36,37,38)))
					        {
					        	$periodeId = 2;
					        }
					        else 
					        {
					        	$periodeId = 3;
					        }
					        
					        $isActive = 1;
						}
						else 
						{
							$periodeId = 1;
							$isActive = 0;
						}
						
				        $data = array(
				        	'periodeId' => $periodeId,
				        	'modifiedDate' => date("Y-m-d h:i:s"),
				        	'modifiedBy' => $this->_user->username,
				            'isActive' => $isActive
				        );
				
				        $modelUser->update($data, "kopel='".$id."'");
						
					}
        		}
        	}
        	$result = 'RESULT_OK';
        }
		
		$this->getResponse()->setBody($result);
	}
    
	function confirmAction()
	{
		$this->_helper->getHelper('layout')->disableLayout();
		$this->_helper->getHelper('viewRenderer')->setNoRender();
		
		$request = $this->getRequest();
        $result  = 'RESULT_ERROR';
        
        if (Pandamp_Controller_Action_Helper_IsAllowed::isAllowed('membership','all'))
        {
        	if ($request->isPost()) {
        		$id  = $request->getPost('id');
//        		$ids = array();
//        		$ids = Zend_Json::decode($id);
        		
        		$aclMan	= Pandamp_Acl::manager();
        		
   		        $modelUser = new App_Model_Db_Table_User();
        		
//   		        foreach ($ids as $id) {
			    	$sql = $modelUser->select()->setIntegrityCheck(false);
					$sql->from(array('ku' => 'KutuUser'))->join(array('gag' => 'gacl_aro_groups'),'ku.packageId = gag.id')
						->where('ku.kopel=?',$id);
			
					$rowUser = $modelUser->fetchRow($sql);
   		        	if (in_array($rowUser->packageId,array(14,15,16,17,18,36,37,38))) {
   		        		
						$tblInvoice = new App_Model_Db_Table_Invoice();
						$where = $tblInvoice->getAdapter()->quoteInto("uid=?",$id);
						$rowInvoice = $tblInvoice->fetchRow($where);
						if ($rowInvoice) {
							$rowInvoice->invoiceConfirmDate = date("Y-m-d");
							$rowInvoice->isPaid = 'Y';
							// get expiration date
							$temptime = time();
							$temptime = Pandamp_Lib_Formater::DateAdd('m',$rowUser->paymentId,$temptime);
							$rowInvoice->expirationDate = strftime('%Y-%m-%d',$temptime);
							$rowInvoice->save();
							
							// delete user group = trial from gacl
							$aclMan->deleteUser($rowUser->username);
							
							// add user to gacl
							$aReturn = $aclMan->getGroupData($rowUser->packageId);
							$aclMan->addUser($rowUser->username,$aReturn[2]);
							
							$rowUser->periodeId = 3;
							$rowUser->isActive = 1;
							$rowUser->modifiedDate = date("Y-m-d h:i:s");
							$rowUser->modifiedBy = "$rowUser->username";
							
							$rowUser->save();
							
							$result = 'RESULT_OK';
						}
						else 
						{
							$result = 'Create Invoice First';
						}
   		        	}
   		        	else 
   		        	{
   		        		$result = 'Wrong Package';
   		        	}
//   		        }
        	}        	
        }
        
        $this->getResponse()->setBody($result);
	}
	
	/**	
	 * Set Invoice
	 * @param guid
	 */
	function setInvoiceAction()
	{
		$this->_helper->getHelper('layout')->disableLayout();
		$this->_helper->getHelper('viewRenderer')->setNoRender();
		
		$request = $this->getRequest();
        $result  = 'RESULT_ERROR';
        
        if (Pandamp_Controller_Action_Helper_IsAllowed::isAllowed('membership','all'))
        {
        	if ($request->isPost()) {
        		$id  = $request->getPost('id');
        		
   		        $modelUser = new App_Model_Db_Table_User();
        		$rowset = $modelUser->fetchRow("kopel='".$id."'");
				if ((in_array($rowset->packageId,array(14,15,16,17,18,36,37,38))) && ($rowset->paymentId <> 0) && ($rowset->isActive == 1))
				{
					$formater = new Pandamp_Core_Hol_User();
					/**
					 * @modifiedDate: Dec 07, 2012
					 */
					// GET disc promo
					//$disc = $formater->checkPromoValidation('Disc',$rowset->packageId,$rowset->promotionId,$rowset->paymentId);
					// GET total promo
					//$total = $formater->checkPromoValidation('Total',$rowset->packageId,$rowset->promotionId,$rowset->paymentId);
					$total = $formater->checkPromoValidation($rowset->packageId,$rowset->paymentId);
					// WRITE invoice
					//$r = $formater->_writeInvoice($rowset->kopel, $total, $disc, $rowset->paymentId,'admin');
					$r = $formater->_writeInvoice($rowset->kopel, $total, 0, $rowset->paymentId,'admin');
					
					$result = $r;
				}
				else
				{
					$result = "check your payment/status";
				}
        	}
        }
        
        $this->getResponse()->setBody($result);		
	}
	
	function delAction()
	{
		$this->_helper->getHelper('layout')->disableLayout();
		$this->_helper->getHelper('viewRenderer')->setNoRender();
		
		$request = $this->getRequest();
        $result  = 'RESULT_ERROR';
        
        if (Pandamp_Controller_Action_Helper_IsAllowed::isAllowed('membership','all'))
        {
        	if ($request->isPost()) {
        		$id  = $request->getPost('id');
        		
   		        $modelUser = new App_Model_Db_Table_User();
		        $row = $modelUser->find($id)->current();
		        if ($row)
		        {
		            $row->delete();
		        }
        		$result  = 'RESULT_OK';
        	}
        }
        $this->getResponse()->setBody($result);		
	}
	
    /*
    function setApprovalAction()
    {
        if (!Pandamp_Controller_Action_Helper_IsAllowed::isAllowed('membership','all'))
        {
            $this->_redirect(ROOT_URL.'/'.$this->_zl->getLanguage().'/error/restricted');
        }

        $this->_helper->layout->setLayout('layout-customer-credential');
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $formater 	= new Pandamp_Core_Hol_User();
        
        $r = $this->getRequest();
		$kopel = $r->getParam('id');
		
        $modelUser = new App_Model_Db_Table_User();
        $rowset = $modelUser->find($kopel)->current();
        
        //if ($rowset->packageId == 18 or $rowset->packageId == 21)
        if (in_array($rowUser->packageId,array(14,15,16,17,18,36,37,38)))
        {
        	$periodeId = 2;
			// Get disc promo
			$disc = $formater->checkPromoValidation('Disc',$rowset->packageId,$rowset->promotionId,$rowset->paymentId);
			// Get total promo
			$total = $formater->checkPromoValidation('Total',$rowset->packageId,$rowset->promotionId,$rowset->paymentId);
			$formater->_writeInvoice($rowset->kopel,$total,$disc,$rowset->paymentId);
        }
        else 
        {
        	$periodeId = 3;
        }
        
        $data = array(
        	'periodeId' => $periodeId,
        	'activationDate' => date("Y-m-d h:i:s"),
            'isActive' => 1
        );

        $modelUser->update($data, "kopel='".$kopel."'");

        $zl = Zend_Registry::get("Zend_Locale");

        $this->_redirect(ROOT_URL."/".$zl->getLanguage().'/customer/user/list');
    }
    */
    
    function __listAction()
    {
        if (!Pandamp_Controller_Action_Helper_IsAllowed::isAllowed('membership','all'))
        {
            $this->_redirect(ROOT_URL.'/'.$this->_zl->getLanguage().'/error/restricted');
        }

        $this->_helper->layout->setLayout('layout-customer-credential');
        
    }
    function _listAction()
    {
        if (!Pandamp_Controller_Action_Helper_IsAllowed::isAllowed('membership','all'))
        {
            $this->_redirect(ROOT_URL.'/'.$this->_zl->getLanguage().'/error/restricted');
        }
        
        $this->_helper->layout->setLayout('layout-customer-credential');

        $userList = App_Model_Show_User::show()->getUserList();
        $this->view->user = $userList;

        $this->view->identity = $this->_user;
    }
    function listAction()
    {
        if (!Pandamp_Controller_Action_Helper_IsAllowed::isAllowed('membership','all'))
        {
            $this->_redirect(ROOT_URL.'/'.$this->_zl->getLanguage().'/error/restricted');
        }
        
        $this->_helper->layout->setLayout('layout-customer-credential');
        
        $time_start = microtime(true);
    	
        $userList = App_Model_Show_User::show()->getUserList();
        
		$a['totalCount'] = count($userList);
		$limit = 25;
		$a['limit'] = $limit;
		
		$this->view->aData = $a;

        $this->view->identity = $this->_user;
        
		$time_end = microtime(true);
		$time = $time_end - $time_start;
    }
    function logAction()
    {
        $this->_helper->layout->setLayout('layout-customer-credential');

        $r = $this->getRequest();
		$kopel = $r->getParam('id');
		
        $modelUserLog = new App_Model_Db_Table_UserLog();
        $this->view->userLog = $modelUserLog->fetchAll("user_id='".$kopel."'");

        $user = App_Model_Show_User::show()->getUserById($kopel);
        if ($user)
            $this->view->user = $user;
            
            
       $this->view->identity = $this->_user;
    }
    function deletelogAction()
    {
        if (!Pandamp_Controller_Action_Helper_IsAllowed::isAllowed('membership','all'))
        {
            $this->_redirect(ROOT_URL.'/'.$this->_zl->getLanguage().'/error/restricted');
        }

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);
        
        $r = $this->getRequest();
    	
        $userId = explode(',',$r->getParam('id'));
        
        $modelUserLog = new App_Model_Db_Table_UserLog();
        
        if (is_array($userId))
        {
            foreach($userId as $guid)
            {
            	$rowUserLog = $modelUserLog->find($guid)->current();
            	if ($rowUserLog)
            	{
                	$rowUserLog->delete();
            	}
            }

        }
        else
        {
        	$rowUserLog = $modelUserLog->find($userId)->current();
        	if ($rowUserLog)
        	{
            	$rowUserLog->delete();
        	}
        }
    }
    function uploadphotoAction()
    {
    	/*
        if (!Pandamp_Controller_Action_Helper_IsAllowed::isAllowed('membership','all'))
        {
            $this->_redirect(ROOT_URL.'/'.$this->_zl->getLanguage().'/error/restricted');
        }
        */

        $this->_helper->layout->setLayout('layout-customer-credential');

        $r = $this->getRequest();

        if($r->isPost()){

            $guid = $r->getParam('id');

            $arraypictureformat = array("jpg", "jpeg", "gif");
            
		    $registry = Zend_Registry::getInstance();
		    $config = $registry->get(Pandamp_Keys::REGISTRY_APP_OBJECT);
		    $cdn = $config->getOption('cdn');
		    
		    $sDir = $cdn['static']['dir']['photo'];
            //$sDir = ROOT_DIR.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'photo';

            if ($r->getParam('txt_erase') == 'on') {
                foreach ($arraypictureformat as $key => $val) {
                    if (is_file($sDir."/".$guid.".".$val)) {
                        unlink($sDir."/".$guid.".".$val);
                        break;
                    }
                }
            }

            $registry = Zend_Registry::getInstance();
            $files = $registry->get('files');

            if (isset($files['file_picture']))
            {
                $file = $files['file_picture'];
            }

            if ($files['file_picture']['error'] == 0 && $files['file_picture']['size'] > 0) {
                    $file = $files['file_picture']['name'];
                    $ext = explode(".",$file);
                    $ext = strtolower(array_pop($ext));
                    if (in_array($ext,$arraypictureformat)) {
                            $image_size = getimagesize($files['file_picture']['tmp_name']);

                            if ($image_size[0] > 200 || $image_size[1] > 250)
                            {
                                $this->view->message = 'Ukuran gambar melebihi batas maksimal. Proses pengunggahan batal!';

                            }
                            else
                            {
                                foreach ($arraypictureformat as $key => $val)
                                {
                                    if (is_file($sDir."/".$guid.".".$val)) {
                                        unlink($sDir."/".$guid.".".$val);
                                        break;
                                    }
                                }

                                if (is_uploaded_file($files['file_picture']['tmp_name'])) {
                                    @move_uploaded_file($files['file_picture']['tmp_name'], $sDir."/".$guid.".".$ext);
                                    @chmod($files['file_picture']['tmp_name'], $sDir."/".$guid.".".$ext, 0755);
                                }

                                $this->view->message = "Data has been successfully saved.";
                            }
                    }
                    else 
                    {
                    	$this->view->message = "Format foto yang diperkenankan: jpg; jpeg; gif";
                    }


            }


        }

        $id = $this->_getParam("id");
        $user = App_Model_Show_User::show()->getUserById($id);
        if ($user)
            $this->view->user = $user;
    }
    function changepasswordAction()
    {
    	/*
        if (!Pandamp_Controller_Action_Helper_IsAllowed::isAllowed('membership','all'))
        {
            $this->_redirect(ROOT_URL.'/'.$this->_zl->getLanguage().'/error/restricted');
        }
        */

        $this->_helper->layout->setLayout('layout-customer-credential');

        $r = $this->getRequest();

        if($r->isPost()){

            $modelUser = new App_Model_Db_Table_User();
            $row = $modelUser->find($r->getParam('id'))->current();

            $obj = new Pandamp_Crypt_Password();
            if($obj->matchPassword($r->getParam('opasswd'), $row->password))
            {
                $row->password = $obj->encryptPassword($r->getParam('newpasswd'));
                $row->save();

                $this->view->message = "Password was sucessfully changed.";
            }
            else
            {
                $this->view->message = "Old password was wrong. Please retry with correct password.";
            }
        }

        
        $id = $this->_getParam("id");
        $user = App_Model_Show_User::show()->getUserById($id);
        if ($user)
            $this->view->user = $user;
    }
    function deleteAction()
    {
        $this->_helper->layout->setLayout('layout-customer-credential');
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $id = ($this->_request->getParam('id'))? $this->_request->getParam('id') : '';

        // delete Order
        $modelUser = new App_Model_Db_Table_User();
        $row = $modelUser->find($id)->current();
        if ($row)
        {
            $row->delete();
        }
    }
    function resetAction()
    {
        $this->_helper->layout->setLayout('layout-customer-credential');
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $id = ($this->_request->getParam('id'))? $this->_request->getParam('id') : '';

        $obj = new Pandamp_Crypt_Password();

        $modelUser = new App_Model_Db_Table_User();
        $row = $modelUser->find($id)->current();
        $newpass = Pandamp_Lib_Formater::randompassowrd();
        $to = $row->email;
        $row->password = $obj->encryptPassword($newpass);
        $subject = "Your new password for Hukumonline.com";
        $message = "Dear $row->fullName,\n\n";
        $message .= "As you requested, your password has now been reset. Your new details are as follows:\n\n";
        $message .= "Username: ".$row->username."\n";
        $message .= "Password: ".$newpass."\n\n";
        $message .= "All the best,\n";
        $message .= "Hukumonline.com";

        $config = new Zend_Config_Ini(ROOT_DIR.'/app/configs/mail.ini', 'mail');
        $options = array('auth' => $config->mail->auth,
                        'username' => $config->mail->username,
                        'password' => $config->mail->password);
        $transport = new Zend_Mail_Transport_Smtp($config->mail->host, $options);
        $mail = new Zend_Mail();
        $mail->setBodyText($message);
        $mail->setFrom($config->mail->sender->support->email, $config->mail->sender->support->name);
        $mail->addTo($to, $row->fullName);
        $mail->setSubject($subject);

        try
        {
            $mailTransport = Pandamp_Application::getResource('mail');
            $mail->send($mailTransport);
            $row->save();
        }
        catch (Zend_Exception $e)
        {
            echo $e->getMessage();
            die();
        }
    }
    function editAction()
    {
    	/*
        if (!Pandamp_Controller_Action_Helper_IsAllowed::isAllowed('membership','all'))
        {
            $this->_redirect(ROOT_URL.'/'.$this->_zl->getLanguage().'/error/restricted');
        }
        */

        $this->_helper->layout->setLayout('layout-customer-credential');

        $r = $this->getRequest();

        if($r->isPost()){

            $month              = ($r->getParam('month'))? $r->getParam('month') : '00';
            $day                = ($r->getParam('day'))? $r->getParam('day') : '00';
            $year               = ($r->getParam('year'))? $r->getParam('year') : '0000';
            $newArticle		= ($r->getParam('newArticle'))? $r->getParam('newArticle') : '';
            $newRegulation	= ($r->getParam('newRegulation'))? $r->getParam('newRegulation') : '';
            $newWRegulation	= ($r->getParam('newWeeklyRegulation'))? $r->getParam('newWeeklyRegulation') : '';
            $isContact 		= ($r->getParam('iscontact'))? $r->getParam('iscontact') : '';

            if ($r->getParam('gender') == 1)
            {
                $gender = 'L';
            }
            else if($r->getParam('gender') == 2)
            {
                $gender = 'P';
            }
            else
            {
                $gender = 'N';
            }

            $data = array(
                'fullName'		=> $r->getParam('fullname')
                ,'birthday'		=> $year.'-'.$month.'-'.$day
                ,'phone'		=> $r->getParam('phone')
                ,'fax'			=> $r->getParam('fax')
                ,'gender'		=> $gender
                ,'email'		=> $r->getParam('email')
                ,'address'		=> $r->getParam('address')
                ,'city'			=> $r->getParam('city')
                ,'state'		=> ($r->getParam('province'))? $r->getParam('province') : 7
                ,'countryId'		=> ($r->getParam('countryId'))? $r->getParam('countryId') : 'ID'
                ,'zip'			=> $r->getParam('zip')
                ,'newArticle'		=> ($newArticle == 1)? 'Y' : 'N'
                ,'weeklyList'		=> ($newWRegulation == 1)? 'Y' : 'N'
                ,'monthlyList'		=> ($newRegulation == 1)? 'Y' : 'N'
                ,'promotionId'		=> $r->getParam('promotioncode')
                ,'educationId'		=> ($r->getParam('education'))? $r->getParam('education') : 0
                ,'expenseId'		=> ($r->getParam('expense'))? $r->getParam('expense') : 0
                ,'paymentId'		=> ($r->getParam('payment'))? $r->getParam('payment') : 0
                ,'businessTypeId'	=> ($r->getParam('businessType'))? $r->getParam('businessType') : 0
                ,'periodeId'		=> $r->getParam('ustatus')
                ,'modifiedDate'		=> date('Y-m-d h:i:s')
                ,'modifiedBy'		=> $r->getParam('modifiedBy')
                ,'isActive'		=> $r->getParam('isactive')
                ,'isContact'		=> ($isContact == 1)? 'Y' : 'N'
            );

            $modelUser = new App_Model_Db_Table_User();
            try {
            	$modelUser->update($data, "kopel='".$r->getParam('id')."'");
            }
            catch (Exception $e)
            {
            	throw new Zend_Exception($e->getMessage());
            }

            $this->view->data = $data;
            $this->view->message = "Data has been successfully saved.";
        }

        $id = $this->_getParam("id");
        $user = App_Model_Show_User::show()->getUserById($id);
        if ($user) 
            $this->view->user = $user;
            $this->view->identity = $this->_user;
    }
    function alertAction()
    {
    	$this->_helper->layout->disableLayout();
    	
    	$tblInvoice = new App_Model_Db_Table_Invoice();
    	
    	$rowInvoice = $tblInvoice->fetchExpireInvoice();
    	
    	$this->view->invoice = $rowInvoice;
    }
    function expirationalertAction()
    {
    	$this->_helper->layout->setLayout('layout-customer-credential');
    	
    	$request = $this->getRequest();
    	
    	$period = $request->getParam('period');
    	
    	$tblInvoice = new App_Model_Db_Table_Invoice();
    	
    	$countInvoice = $tblInvoice->getCountExpireInvoice($period);
    	
    	$userIE = $tblInvoice->fetchUserExpireInvoice($period);
    	
    	$this->view->uie = $userIE;
    	$this->view->count_invoice = $countInvoice;
    	$this->view->period = $period;
    }
    function invoicelistAction()
    {
        if (!Pandamp_Controller_Action_Helper_IsAllowed::isAllowed('membership','all'))
        {
            $this->_redirect(ROOT_URL.'/'.$this->_zl->getLanguage().'/error/restricted');
        }

    	$this->_helper->layout->setLayout('layout-customer-credential');
    	
        $id = $this->_getParam("id");
        
        $userInvoiceList = App_Model_Show_Invoice::show()->getInvoiceById($id);
        $this->view->userInvoiceList = $userInvoiceList;

        $user = App_Model_Show_User::show()->getUserById($id);
        if ($user) 
            $this->view->user = $user;
            $this->view->identity = $this->_user;
            
        
        /**
         * UPDATED:July 25, 2012
         * @modifiedDate: 2012-11-20 15:10N
         * @todo Invoice can add more than one
         * comment line 897-899
         */
        if ($this->getRequest()->isPost()) {
			$tblInvoice = new App_Model_Db_Table_Invoice();
//			$where = $tblInvoice->getAdapter()->quoteInto("uid=?",$this->getRequest()->getPost('kopel'));
//			$rowInvoice = $tblInvoice->fetchAll($where);
//			if (count($rowInvoice) <= 0)
//			{
				$rowInvoice = $tblInvoice->fetchNew();
				$rowInvoice->uid = $this->getRequest()->getPost('kopel');
				$rowInvoice->price = $this->getRequest()->getPost('price');
				$rowInvoice->discount = $this->getRequest()->getPost('disc');
				$rowInvoice->invoiceOutDate = $this->getRequest()->getPost('invoiceOutDate');
				$rowInvoice->invoiceConfirmDate = $this->getRequest()->getPost('invoiceConfirmDate');
				$rowInvoice->clientBankAccount = $this->getRequest()->getPost('clientBankAccount');
				$rowInvoice->isPaid = $this->getRequest()->getPost('isPaid');
				$rowInvoice->expirationDate = $this->getRequest()->getPost('expirationDate');
				
				$rowInvoice->save();
				
				$this->_redirect(ROOT_URL.'/'.$this->_zl->getLanguage().'/customer/user/invoicelist/id/'.$this->getRequest()->getPost('kopel'));
//			}
        }
    }
    function delassAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $id = $this->_getParam("id");
        $username = $this->_getParam("uname");

        $acl = Pandamp_Acl::manager();
        $aclId = $acl->getGroupIds($id);
        
        $acl->removeUserFromGroup($username, $id);
    }
    function associateAction()
    {
        if (!Pandamp_Controller_Action_Helper_IsAllowed::isAllowed('membership','all'))
        {
            $this->_redirect(ROOT_URL.'/'.$this->_zl->getLanguage().'/error/restricted');
        }

        $this->_helper->layout->setLayout('layout-customer-credential');

        $r = $this->getRequest();

        if($r->isPost()){

            $newGroup = $r->getParam('aro_groups');
            $data = array(
                'packageId' => $newGroup
            );

            $id = $r->getParam('id');
            
            $oldUser = App_Model_Show_User::show()->getUserById($id);

            $modelUser = new App_Model_Db_Table_User();
            $modelUser->update($data, "kopel='".$id."'");
            
            $dataUserDetail = array(
            	'userId'			=> $oldUser['kopel']
            	,'packageId'		=> $oldUser['packageId']
            	,'promotionId'		=> $oldUser['promotionId']
            	,'educationId'		=> $oldUser['educationId']
            	,'expenseId'		=> $oldUser['expenseId']
            	,'paymentId'		=> $oldUser['paymentId']
            	,'businessTypeId'	=> $oldUser['businessTypeId']
            	,'periodeId'		=> $oldUser['periodeId']
            	,'activationDate'	=> $oldUser['activationDate']
            	,'createdDate'		=> $oldUser['createdDate']
            	,'createdBy'		=> $oldUser['createdBy']
            	,'modifiedDate'		=> $oldUser['modifiedDate']
            	,'modifiedBy'		=> $oldUser['modifiedBy']
            	,'isActive'			=> $oldUser['isActive']
            	,'isContact'		=> $oldUser['isContact']
            );
            
            $modelUserDetail = new App_Model_Db_Table_UserDetail();
            $modelUserDetail->insert($dataUserDetail);

            $username = $r->getParam('username');
            $acl = Pandamp_Acl::manager();
            //$acl->deleteUser($username);
            //$acl->removeUserFromGroup($username, $oldUser['packageId']);

            $groupName = App_Model_Show_AroGroup::show()->getUserGroup($newGroup);
            //$acl->addUser($username,$groupName['name']);
            $acl->addUserToGroup($username, $groupName['name']);

            $this->view->message = "Package was sucessfully changed.";
        }

        $id = $this->_getParam("id");
        $user = App_Model_Show_User::show()->getUserById($id);
        if ($user) {
	        $acl = Pandamp_Acl::manager();
	        $role = $acl->getUserGroupIds($user['username']);//print_r($role);
	        
	        $this->view->UserRoles = $role;
	
            $this->view->user = $user;
        }
    }
    
    /**
     * Who's Online
     *
     */
    function wholAction()
    {
        if (!Pandamp_Controller_Action_Helper_IsAllowed::isAllowed('membership','all'))
        {
            $this->_redirect(ROOT_URL.'/'.$this->_zl->getLanguage().'/error/restricted');
        }

        $this->_helper->layout->setLayout('layout-customer-credential');

		$rowset = App_Model_Show_User::show()->countUser("ku.packageId IN (14,15,16,17,18) AND ku.ses!='*'");    	
		
		$a['totalCount'] = $rowset;
		$limit = 25;
		$a['limit'] = $limit;
		
		$this->view->aData = $a;

        $this->view->identity = $this->_user;
    }
    
    /**
     * Kick User
     *
     */
    function kickAction()
    {
        $this->_helper->layout->setLayout('layout-customer-credential');
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $id = ($this->_request->getParam('id'))? $this->_request->getParam('id') : '';

        $modelUser = new App_Model_Db_Table_User();
        $modelUser->update(array('ses' => '*'), array('kopel = ?' => $id));
        
        $modelSession = new App_Model_Db_Table_Session();
        $rowSession = $modelSession->fetchRow("sessionData LIKE '%$id%'");
        if ($rowSession)
        {
        	/**
        	 * Destroy any active session identified by sessionId
        	 */
        	session_id($rowSession->sessionId);
        	session_destroy();
        }
    }
    
    /**
     * Kick all user
     *
     */
    function kickallAction()
    {
		$this->_helper->getHelper('layout')->disableLayout();
		$this->_helper->getHelper('viewRenderer')->setNoRender();
		
		$request = $this->getRequest();
        $result  = 'RESULT_ERROR';
        
        if (Pandamp_Controller_Action_Helper_IsAllowed::isAllowed('membership','all'))
        {
        	if ($request->isPost()) {
        		$id  = $request->getPost('id');
        		$ids = array();
        		$ids = Zend_Json::decode($id);
        		
        		foreach ($ids as $id) {
			        $modelUser = new App_Model_Db_Table_User();
			        $modelUser->update(array('ses' => '*'), array('kopel = ?' => $id));
			        
			        $modelSession = new App_Model_Db_Table_Session();
			        $rowSession = $modelSession->fetchRow("sessionData LIKE '%$id%'");
			        if ($rowSession)
			        {
			        	/**
			        	 * Destroy any active session identified by sessionId
			        	 */
			        	try {
			        		session_id($rowSession->sessionId);
			        		session_destroy();
			        	}
			        	catch (Exception $e) {}
			        }
        		}        		
        	}
        	
        	$result = 'RESULT_OK';
        }    	
        
        $this->getResponse()->setBody($result);
    }
    
    function rightupmenuAction()
    {
        $modelUser = App_Model_Show_User::show()->getUserById($this->_getParam('id'));
        $this->view->user = $modelUser;
    }
    function rightdownmenuAction()
    {
        $modelUser = App_Model_Show_User::show()->getUserById($this->_getParam('id'));
        $this->view->user = $modelUser;
    }
    function sidebarAction()
    {
        $this->view->user = $this->_user;
    }
}
