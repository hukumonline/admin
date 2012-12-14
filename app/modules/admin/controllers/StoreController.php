<?php

/**
 * Description of StoreController
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Admin_StoreController extends Zend_Controller_Action
{
    protected $_user;

    function  preDispatch()
    {
        $this->_helper->layout->setLayout('store');

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

            $zl = Zend_Registry::get("Zend_Locale");
            
            $acl = Pandamp_Acl::manager();
            //if (!$acl->checkAcl("site",'all','user', $this->_user->username, false,false))
            if (!Pandamp_Controller_Action_Helper_IsAllowed::isAllowed('store','all'))
            {
                //$this->_redirect(ROOT_URL.'/'.$zl->getLanguage().'/error/restricted');
                $this->_forward('restricted','error','admin',array('lang'=>$zl->getLanguage()));
            }
            
			// [TODO] else: check if user has access to admin page and status website is online
			$tblSetting = new App_Model_Db_Table_Setting();
			$rowset = $tblSetting->find(1)->current();
			
			if ($rowset)
			{
				if (($rowset->status == 1 && $zl->getLanguage() == 'id') || ($rowset->status == 2 && $zl->getLanguage() == 'en') || ($rowset->status == 3))
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
        $this->view->user = $this->_user;
    }
    function indexAction()
    {
        $status = App_Model_Show_OrderStatus::show()->getStatus();
        for($i =0 ;$i<count($status);$i++){
            $statusId[$i] = $status[$i]['orderStatusId'];
            $orderStatus[$i] = $status[$i]['ordersStatus'];
        }
        for($i=0; $i<count($statusId);$i++){
            $rowset[$i] = App_Model_Show_Order::show()->getOrderSummaryAdmin(' KO.orderStatus = '. $statusId[$i],5, 0);
            $total[$i] = App_Model_Show_Order::show()->countOrdersAdmin(' KO.orderStatus = '.$statusId[$i]);
        }
        
        $this->view->total = $total;
        $this->view->statusId = $statusId;
        $this->view->status = $orderStatus;
        $this->view->rowset = $rowset;
        
    }
    public function paymentsettingAction()
    {
        
        $tblPaymentSetting = new App_Model_Db_Table_PaymentSetting();
        
        $rowset = $tblPaymentSetting->fetchAll();
        $numi = count($rowset);
        $this->view->totalItems = $numi;
		$this->view->rows = $rowset;
    }
    public function editpaymentsettingAction()
    {
        $idSetting = $this->_request->getParam('id');
        
        $tblPaymentSetting = new App_Model_Db_Table_PaymentSetting();
        
        $rowset = $tblPaymentSetting->fetchAll("settingId = ".$idSetting);
        $this->view->id = $idSetting;
		$this->view->rows = $rowset;
        
        if($this->_request->isPost($this->_request->getParam('save'))){
            $id = $this->_request->getParam('id');
            $data['settingValue'] = $this->_request->getParam('value');
            $data['note'] = $this->_request->getParam('note');
            $update = $tblPaymentSetting->update($data, 'settingId = '.$id);
            
			$zl = Zend_Registry::get('Zend_Locale');
			$this->_redirect(ROOT_URL.'/'.$zl->getLanguage().'/store/paymentsetting');
        }
    }
    function orderAction()
    {
        $limit = ($this->_request->getParam('limit'))?$this->_request->getParam('limit'):10;
        $this->view->limit =$limit;
        $itemsPerPage = $limit;
        $this->view->itemsPerPage = $itemsPerPage;
        $offset = ($this->_request->getParam('offset'))?$this->_request->getParam('offset'):0;
        $this->view->offset = $offset;
        $sStatus = ($this->_request->getPost('sStatus'))? $this->_request->getPost('sStatus') : '';
        $this->view->sStatus = $sStatus;
        $sUsername = ($this->_request->getPost('sUsername'))? $this->_request->getPost('sUsername') : '';
        $this->view->sUsername = $sUsername;
        $fdate = ($this->_request->getPost('fdate'))? $this->_request->getPost('fdate') : '';
        $this->view->fdate = $fdate;
        $ldate = ($this->_request->getPost('ldate'))? $this->_request->getPost('ldate') : '';
        $this->view->ldate = $ldate;

        $where = '';
        
        if($this->_request->get('status')){
            $valStatus =$this->_request->getParam('status');
            ($valStatus == 0)?$where .=' KO.orderStatus != 0 ':$where .= ' KO.orderStatus = '.$valStatus;
            $statName = App_Model_Show_OrderStatus::show()->getSpecifiedStatus($valStatus);
            $statName = $statName[0]['ordersStatus'];
        }else{
            $where .= 'KO.orderStatus != 0';
            $statName = 'All';
            $valStatus = ' ';
        }

        if(!empty($sStatus)){
            $where .= " AND KOS.ordersStatus LIKE '%$sStatus%'";
        }
        if(!empty($sStatus)){
            $where .= " AND KU.username LIKE '%$sUsername%'";
        }
        if(!empty($fdate)){
            $where .= " AND datePurchased > '$fdate'";
        }
        if(!empty($ldate)){
            $where .= " AND datePurchased < '$ldate'  ";
        }

        $rowset = App_Model_Show_Order::show()->getOrderSummaryAdmin($where,$limit, $offset);
        $numi = App_Model_Show_Order::show()->countOrdersAdmin($where);

        $this->view->totalItems = $numi;
        $this->view->rows = $rowset;
        $this->view->where = $where;
    }
    public function editorderAction()
    {
        $idOrder = $this->_request->getParam('id');

        $rowset = App_Model_Show_Order::show()->getOrder($idOrder);

        $rowsStatus = App_Model_Show_OrderStatus::show()->getStatus();

        $this->view->offset = $this->_request->getParam('offset');
        $this->view->id = $idOrder;
        $this->view->rows = $rowset;
        $this->view->rowsStatus = $rowsStatus;

        if($this->_request->isPost($this->_request->getParam('save'))){
            $id = $this->_request->getParam('id');
            $data['invoiceNumber'] = $this->_request->getParam('invoiceNumber');
            $data['userId'] = $this->_request->getParam('userId');
            $data['taxNumber'] = $this->_request->getParam('taxNumber');
            $data['taxCompany'] = $this->_request->getParam('taxCompany');
            $data['taxAddress'] = $this->_request->getParam('taxAddress');
            $data['taxCity'] = $this->_request->getParam('taxCity');
            $data['taxZip'] = $this->_request->getParam('taxZip');
            $data['taxProvince'] = $this->_request->getParam('taxProvince');
            $data['taxCountryId'] = $this->_request->getParam('taxCountryId');
            $data['telephone'] = $this->_request->getParam('telephone');
            $data['paymentMethod'] = $this->_request->getParam('paymentMethod');
            $data['paymentMethodNote'] = $this->_request->getParam('paymentMethodNote');
            $data['lastModified'] = $this->_request->getParam('lastModified');
            $data['datePurchased'] = $this->_request->getParam('datePurchased');
            $data['orderStatus'] = $this->_request->getParam('orderStatus');
            $data['dateOrderFinished'] = $this->_request->getParam('dateOrderFinished');
            $data['currency'] = $this->_request->getParam('currency');
            $data['currencyValue'] = $this->_request->getParam('currencyValue');
            $data['orderTotal'] = $this->_request->getParam('orderTotal');
            $data['orderTax'] = $this->_request->getParam('orderTax');
            $data['paypalIpnId'] = $this->_request->getParam('paypalIpnId');
            $data['ipAddress'] = $this->_request->getParam('ipAddress');

            $tblOrder = new App_Model_Db_Table_Order();
            $update = $tblOrder->update($data, 'orderId = '.$id);
            $redirector = $this->_helper->getHelper('redirector');
            $redirector->gotoSimple(array('order', 'store', 'admin', 'order'));
        }

    }
    function detailorderAction()
    {
        $r = $this->getRequest();
        $limit = ($r->getParam('limit'))?$r->getParam('limit'):10;
        $this->view->limit =$limit;
        $itemsPerPage = $limit;
        $this->view->itemsPerPage = $itemsPerPage;
        $offset = ($r->getParam('offset'))?$r->getParam('offset'):0;
        $this->view->offset = $offset;

        $idOrder = $r->getParam('id');

        $rowset = App_Model_Show_Order::show()->getOrderAndStatus($idOrder);
        
        $rowsetDetail = App_Model_Show_OrderDetail::show()->getOrderDetail($idOrder);

        $this->view->rowsHistory = App_Model_Show_OrderHistory::show()->getHistory($idOrder);

        $this->view->id = $idOrder;
        $this->view->rows = $rowset;
        $this->view->rowsDetail = $rowsetDetail;
    }
    function deleteorderAction()
    {
    	$this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $idOrder = ($this->_request->getParam('id'))? $this->_request->getParam('id') : '';
        
        // delete Order
        $tblOrder = new App_Model_Db_Table_Order();
        $row = $tblOrder->find($idOrder)->current();
        if ($row)
        {
            $row->delete();
        }
        
//        $this->_redirect(ROOT_URL.'/store/order');
    }
    public function transactionAction()
    {
        $limit = ($this->_request->getParam('limit'))?$this->_request->getParam('limit'):10;
        $this->view->limit =$limit;
        $itemsPerPage = $limit;
        $this->view->itemsPerPage = $itemsPerPage;
        $offset = ($this->_request->getParam('offset'))?$this->_request->getParam('offset'):0;
        $this->view->offset = $offset;
        $sStatus = ($this->_request->getPost('sStatus'))? $this->_request->getPost('sStatus') : '';
        $this->view->sStatus = $sStatus;
        $sUsername = ($this->_request->getPost('sUsername'))? $this->_request->getPost('sUsername') : '';
        $this->view->sUsername = $sUsername;
        $fdate = ($this->_request->getPost('fdate'))? $this->_request->getPost('fdate') : '';
        $this->view->fdate = $fdate;
        $ldate = ($this->_request->getPost('ldate'))? $this->_request->getPost('ldate') : '';
        $this->view->ldate = $ldate;

        
        $where = '(KO.orderStatus = 3 OR KO.orderStatus = 5 OR KO.orderStatus = 2) ';

        if(!empty($sStatus)){
            $where .= " AND KOS.ordersStatus LIKE '%$sStatus%'";
        }
        if(!empty($sStatus)){
            $where .= " AND KU.username LIKE '%$sUsername%'";
        }
        if(!empty($fdate)){
            $where .= " AND datePurchased > '$fdate'";
        }
        if(!empty($ldate)){
            $where .= " AND datePurchased < '$ldate'  ";
        }

        $valStatus = ' ';

        $status = App_Model_Show_OrderStatus::show()->getStatus();
        for($i =0 ;$i<count($status);$i++){
            $statusId[$i] = $status[$i]['orderStatusId'];
            $orderStatus[$i] = $status[$i]['ordersStatus'];
        }

        $rowset = App_Model_Show_Order::show()->getOrderSummaryAdmin($where,$limit, $offset);
        $numi = App_Model_Show_Order::show()->countOrdersAdmin('('.$where.')');

        $this->view->statusId = $statusId;
        $this->view->orderStatus = $orderStatus;
        $this->view->valStatus = $valStatus;
        $this->view->totalItems = $numi;
        $this->view->rows = $rowset;
        $this->view->where = $where;

    }
    public function confirmAction()
    {
        $limit = ($this->_request->getParam('limit'))?$this->_request->getParam('limit'):10;
        $this->view->limit =$limit;
        $itemsPerPage = $limit;
        $this->view->itemsPerPage = $itemsPerPage;
        $offset = ($this->_request->getParam('offset'))?$this->_request->getParam('offset'):0;
        $this->view->offset = $offset;
        $sStatus = ($this->_request->getPost('sStatus'))? $this->_request->getPost('sStatus') : '';
        $this->view->sStatus = $sStatus;
        $sUsername = ($this->_request->getPost('sUsername'))? $this->_request->getPost('sUsername') : '';
        $this->view->sUsername = $sUsername;
        $fdate = ($this->_request->getPost('fdate'))? $this->_request->getPost('fdate') : '';
        $this->view->fdate = $fdate;
        $ldate = ($this->_request->getPost('ldate'))? $this->_request->getPost('ldate') : '';
        $this->view->ldate = $ldate;

        $where ='';

        if(!empty($sStatus)){
            $where .= " AND KO.orderStatus LIKE '%$sStatus%'";
        }
        if(!empty($sStatus)){
            $where .= " AND KU.username LIKE '%$sUsername%'";
        }
        if(!empty($fdate)){
            $where .= " AND KO.datePurchased > '$fdate'";
        }
        if(!empty($ldate)){
            $where .= " AND KO.datePurchased < '$ldate'  ";
        }

        $rowset = App_Model_Show_PaymentConfirmation::show()->unconfirmList($where,$limit, $offset);
        $count = App_Model_Show_PaymentConfirmation::show()->unconfirmListCount($where);

        $this->view->rowset = $rowset;
        $this->view->totalItems = $count;
    }
    public function payconfirmAction()
    {
		$idOrder = $this->_request->getParam('id');
		
		$tblOrder = new App_Model_Db_Table_Order();
        $tblOrderDetail = new App_Model_Db_Table_OrderDetail();
        $tblConfirm = new App_Model_Db_Table_PaymentConfirmation();
		
        $rowset = App_Model_Show_Order::show()->getOrderAndStatus($idOrder);
        
        $rowsetDetail = $tblOrderDetail->fetchAll("orderId = ". $idOrder);
		$rowsetConfirm = $tblConfirm->fetchAll("orderId = ". $idOrder);
		$Paid = $tblConfirm->fetchAll("orderId = ". $idOrder,'id DESC',1,0);
		
        $this->view->Paid = $Paid[0]->paymentDate;
		$this->view->idOrder = $idOrder;
		$this->view->rowset = $rowset;
		$this->view->rowsetDetail = $rowsetDetail;
		$this->view->rowsetConfirm = $rowsetConfirm;
    }
	public function payconfirmyesAction()
	{
		$this->_helper->viewRenderer->setNoRender(TRUE);
		print_r($this->_request->getParams());
		
		$id = $this->_request->getParam('id');
		
		$tblOrder = new App_Model_Db_Table_Order();
		$tblHistory = new App_Model_Db_Table_OrderHistory();
        $tblConfirm = new App_Model_Db_Table_PaymentConfirmation();
		
		//select payment date from paymentconfirmation
		$date = $tblConfirm->fetchAll("orderId = ". $id." AND confirmed = 0");
		
		$data['paymentDate'] = $date[0]->paymentDate;
		//update order
		$data['orderStatus'] = 3;
		$tblOrder->update($data,"orderId = ". $id);
		
		//update paymentconfirmation
		$dataConfirm['confirmed'] =1;
		$tblConfirm->update($dataConfirm, "orderId = ". $id);
		
		//add history
		$dataHistory = $tblHistory->fetchNew();
		//history data
		$dataHistory['orderId'] = $id; 
		$dataHistory['orderStatusId'] = 3; 
		$dataHistory['dateCreated'] = date('Y-m-d'); 
		$dataHistory['userNotified']   = 1; 
		$dataHistory['note'] = 'confirmed'; 
		$dataHistory->save();
		
		//mailer 
		//$this->Mailer($id, 'user-confirm', 'user');
		$mod = new App_Model_Store_Mailer();
		$mod->sendReceiptToUser($id,'Bank Transfer');
		
		//redirect to confirmation page
		$zl = Zend_Registry::get('Zend_Locale');
		$this->_redirect(ROOT_URL.'/'.$zl->getLanguage().'/store/confirm');
			
		
	}
	public function payconfirmnoAction()
	{
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$id = $this->_request->getParam('id');
		
		$method = 6;
		
		$tblOrder = new App_Model_Db_Table_Order();
		$tblHistory = new App_Model_Db_Table_OrderHistory();
        $tblConfirm = new App_Model_Db_Table_PaymentConfirmation();
        
		$date = $tblConfirm->fetchAll("orderId = ". $id." AND confirmed = 0");
		//$data['paidDate'] = @$date[0]->paymentDate;
		//update order
		$data['orderStatus'] = $method;
		$tblOrder->update($data,"orderId = ". $id);
		
		//update paymentconfirmation
		$dataConfirm['confirmed'] =1;
		$tblConfirm->update($dataConfirm, "orderId = ". $id);
		
		//add history
		$dataHistory = $tblHistory->fetchNew();
		//history data
		$dataHistory['orderId'] = $id; 
		
		$dataHistory['orderStatusId'] = $method; 
		$dataHistory['dateCreated'] = date('Y-m-d'); 
		$dataHistory['userNotified']   = 1; 
		$dataHistory['note'] = 'rejected'; 
		$dataHistory->save();
		
		$mod = new App_Model_Store_Mailer();
		$mod->sendReceiptToUser($id,'Bank Transfer', "REJECTED");
		
		//redirect to confirmation page
		$zl = Zend_Registry::get('Zend_Locale');
		$this->_redirect(ROOT_URL.'/'.$zl->getLanguage().'/store/confirm');
	}
    public function trdetailAction()
    {
	    $orderId = $this->_request->getParam('id');
        
		$tblOrder = new App_Model_Db_Table_Order();
		$tblOrderDetail = new App_Model_Db_Table_OrderDetail();
		$tblOrderHistory = new App_Model_Db_Table_OrderHistory();
		//$tblOrderPaypalHistory = new Kutu_Core_Orm_Table_PaypalPaymentHistory();
		$tblOrderNsiapay = new App_Model_Db_Table_Nsiapay();
		
		$rowset = $tblOrder->fetchAll("orderID ='".$orderId."'");
		$rowsetDetail = $tblOrderDetail->fetchAll("orderId='".$orderId."'");
		$rowsetHistory = App_Model_Show_OrderHistory::show()->getHistory($orderId);
		//$rowsetPaypalHistory = $tblOrderPaypalHistory->fetchAll($tblOrderPaypalHistory->select()->where("orderId='".$orderId."'"));
		$rowsetNsiapay = $tblOrderNsiapay->fetchAll("orderID ='".$orderId."'");
		
		$this->view->listOrder = $rowset;
		$this->view->listOrderDetail = $rowsetDetail;
		$this->view->rowsetHistory = $rowsetHistory;	
		//$this->view->rowsetPaypalHistory = $rowsetPaypalHistory;	
		$this->view->rowsetNsiapay = $rowsetNsiapay;	
		
	}
    public function refundAction()
    {
        $orderId = $this->_request->getParam('id');
        
        $tblOrder = new App_Model_Db_Table_Order();
        $tblOrderDetail = new App_Model_Db_Table_OrderDetail();
        $rowset = App_Model_Show_Order::show()->getOrderAndStatus($orderId);
        $rowsetDetail = $tblOrderDetail->fetchAll("orderId = ". $orderId);
        
		$this->view->id = $orderId;
   		$this->view->rows = $rowset;
   		$this->view->rowsDetail = $rowsetDetail;
    }
	public function refundedAction()
	{
		$this->_helper->viewRenderer->setNoRender(TRUE);
        $orderId = $this->_request->getParam('id');
        
        print_r($this->_request->getParams());
        
        $tblOrder = new App_Model_Db_Table_Order();
        $tblOrderDetail = new App_Model_Db_Table_OrderDetail();
        $tblOrderHistory = new App_Model_Db_Table_OrderHistory();
        
        $data['orderStatus'] = 2;
        $rowOrder = $tblOrder->update($data, 'orderId = '.$orderId);
        
        $data2['orderId'] = $orderId;
        $data2['orderStatusId'] = 2;
        $data2['dateCreated'] = date('Y-m-d H:i:s');
        $data2['userNotified'] = '1';
        $data2['note'] = 'Refund Payment on process';
        $updateHistory = $tblOrderHistory->insert($data2);
        
		$zl = Zend_Registry::get('Zend_Locale');
		$this->_redirect(ROOT_URL.'/'.$zl->getLanguage().'/store/transaction');
    }
	public function deleteconfirmAction()
	{
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$id = $this->_request->getParam('id');
		
		$modelPc = new App_Model_Db_Table_PaymentConfirmation();
		$rowPc = $modelPc->find($id)->current();
		if ($rowPc)
		{
			$rowPc->delete();
		}
	}
    public function nsiapaypaymentAction()
    {
        $r = $this->getRequest();
        $limit = ($r->getParam('limit'))?$r->getParam('limit'):10;
        $this->view->limit =$limit;
        $itemsPerPage = $limit;
        $this->view->itemsPerPage = $itemsPerPage;
        $offset = ($r->getParam('offset'))?$r->getParam('offset'):0;
        $this->view->offset = $offset;
        $Query = ($r->getParam('Query'))?$r->getParam('Query'):'';

        $where = "KO.orderStatus = 3 AND KO.paymentMethod ='nsiapay'";

        $status = App_Model_Show_OrderStatus::show()->getStatus();
        for($i =0 ;$i<count($status);$i++){
            $statusId[$i] = $status[$i]['orderStatusId'];
            $orderStatus[$i] = $status[$i]['ordersStatus'];
        }

        $rowset = App_Model_Show_Order::show()->getOrderSummaryAdmin($where,$limit, $offset);
        $numi = App_Model_Show_Order::show()->countOrdersAdmin('('.$where.')');

        $this->view->statusId = $statusId;
        $this->view->orderStatus = $orderStatus;
        $this->view->totalItems = $numi;
        $this->view->rows = $rowset;
        $this->view->where = $where;
    }
}
