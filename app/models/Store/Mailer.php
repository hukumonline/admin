<?php

class App_Model_Store_Mailer
{
	public function sendReceiptToUser($orderId, $paymentMethod='')
	{
		$config = new Zend_Config_Ini(CONFIG_PATH.'/mail.ini', 'mail');
		
		$siteOwner = "Hukumonline";
		$siteName = $config->mail->sender->support->name;
		$contactEmail = $config->mail->sender->support->email;
		
		$tblOrder = new App_Model_Db_Table_Order();
		$rowOrder = $tblOrder->find($orderId)->current();
		$userId = $rowOrder->userId;
		
		//first check if orderId status is PAID, then send the email.
		
		switch ($rowOrder->orderStatus)
		{
			case 1:
				die('ORDER STATUS IS NOT YET PAID. CAN NOT SEND RECEIPT!.');
				break;
			case 3:
				$orderStatus = "PAID";
				break;
			case 5:
				$orderStatus = "POSTPAID PENDING";
				break;
			case 6:
				$orderStatus = "PAYMENT REJECTED";
				break;
			case 7:
				$orderStatus = "PAYMENT ERROR";
				break;
			default:
				$orderStatus = "PAYMENT ERROR";
				break;
		}
		
		$tblUser = new App_Model_Db_Table_User();
		$rowUser = $tblUser->find($userId)->current();
		
		$userEmail = $rowUser->email;
		$userFullname = $rowUser->fullName;
		
		$registry = Zend_Registry::getInstance();
	    $config = $registry->get(Pandamp_Keys::REGISTRY_APP_OBJECT);
	    $store = $config->getOption('store');
	    
	    $holConfig = Pandamp_Config::getConfig();
		
		switch(strtolower($rowOrder->paymentMethod))
		{
			case 'paypal':
			case 'manual':
			case 'bank':
			case 'postpaid':
			default:
				$message = 
"					
Dear $userFullname,

This is a payment receipt for Invoice # $rowOrder->invoiceNumber

Total Amount: IDR $rowOrder->orderTotal
Transaction #:
Total Paid: IDR $rowOrder->orderTotal
Status: $orderStatus
Your payment method is: $paymentMethod

You may review your invoice history at any time by logging in to your account ".$holConfig->cdn->id->url."/user/payment/list

Note: This email will serve as an official receipt for this payment.

Salam,

HUKUMONLINE

==============================";

		}
		
		$this->send($config->mail->sender->support->email, $config->mail->sender->support->name, 
				$userEmail, 'Layanan Hukumonline', "[HUKUMONLINE] Receipt Invoice# ". $rowOrder->invoiceNumber, $message);
	}
    public function send($mailFrom, $fromName, $mailTo, $mailToName, $subject, $body)
    {
        $config = new Zend_Config_Ini(CONFIG_PATH.'/mail.ini', 'mail');
        
        $options = array('auth' => $config->mail->auth,
                        'username' => $config->mail->username,
                        'password' => $config->mail->password);

        if(!empty($config->mail->ssl))
        {
            $options = array('auth' => $config->mail->auth,
                            'ssl' => $config->mail->ssl,
                            'username' => $config->mail->username,
                            'password' => $config->mail->password);
        }

        $transport = new Zend_Mail_Transport_Smtp($config->mail->host, $options);

        $mail = new Zend_Mail();
        $mail->setBodyText($body);
        $mail->setFrom($mailFrom, $fromName);
        $mail->addTo($mailTo, $mailToName);
        $mail->setSubject($subject);

        try
        {
            $mail->send($transport);
        }
        catch (Zend_Exception $e)
        {
            echo $e->getMessage();
        }
    }
}