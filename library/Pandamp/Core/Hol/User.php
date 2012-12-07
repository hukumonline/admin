<?php
class Pandamp_Core_Hol_User
{
    /**
     * getMailContent
     */
    function getMailContent($title)
    {
        $tblCatalog = new App_Model_Db_Table_Catalog();
        $where = $tblCatalog->getAdapter()->quoteInto("shortTitle=?",$title);
        $rowset = $tblCatalog->fetchRow($where);
        $content = App_Model_Show_CatalogAttribute::show()->getCatalogAttributeValue($rowset->guid,'fixedContent');

        return $content;
    }
    
	/**
	 * checkPromoValidation : Individual & Korporasi
	 * @return disc :: Total
	 * @modifiedDate: December 07, 2012
	 */
	//function checkPromoValidation($whatPromo,$package,$promotionId='',$payment=0)
	function checkPromoValidation($package,$payment=0)
	{
		/* ======================= NEW ============== 
		 * December 07, 2012
		*/
		$tblPackage = new App_Model_Db_Table_Package();
		$rowPackage = $tblPackage->fetchRow("packageId=$package");
		if ($payment == 12) {
			$total = $rowPackage->charge * 11;
		}
		else 
		{
			$total = $rowPackage->charge * $payment;
		}
		
		return $total;
		
		/* ======================= END-NEW ============== */
		
		/*
		$tblPackage = new App_Model_Db_Table_Package();
		$rowPackage = $tblPackage->fetchRow("packageId=$package");
		$periode = $rowPackage->charge * $payment;
		
		$tblPromosi = new App_Model_Db_Table_Promotion();
		$rowPromo = $tblPromosi->find($promotionId)->current();
		
		if (isset($rowPromo)) {
			
			if ($payment == 6) {
				$disc = $rowPromo->discount + 5;
			} elseif ($payment == 12) {
				$disc = $rowPromo->discount + 10;
			} else {
				$disc = $rowPromo->discount;
			}
			
			$total = ($periode - ($disc/100 * $periode)) * 1.1;
			
		} else {
			
			$getPromo = $tblPromosi->fetchRow("periodeStart <= '".date("Y-m-d")."' AND periodEnd >= '".date("Y-m-d")."' AND monthlySubscriber=".$payment."");
			
			if (!empty($getPromo))
			{
				if ($payment == 6) {
					$disc = $getPromo->discount + 5;
				} elseif ($payment == 12) {
					$disc = $getPromo->discount + 10;
				} else {
					$disc = $getPromo->discount;
				}
				
				$total = ($periode - ($disc/100 * $periode)) * 1.1;
				
			} else { 
				
				if ($payment == 6) {
					$disc = 5;
				} elseif ($payment == 12) {
					$disc = 10;
				} else {
					$disc = 0;
				}
				
				$total = ($periode - ($disc/100 * $periode)) * 1.1;
				
			}
		}
		
		switch ($whatPromo)
		{
			case 'Disc':
				return $disc;
			break;
			case 'Total':
				return $total;
			break;
		}
		*/
	}
	
	/**
	 * _writeInvoice : Individual & Korporasi
	 * @modifiedDate : 2012-11-20 12:53N
	 * @todo Changed invoiceOutDate with createdDate from User Table
	 * @return 
	 */
	function _writeInvoice($memberId, $totalPromo, $discPromo, $payment, $access='')
	{
		//$aclMan	= Pandamp_Acl::manager();
		
		$tblInvoice = new App_Model_Db_Table_Invoice();
		$where = $tblInvoice->getAdapter()->quoteInto("uid=?","$memberId");
		$rowInvoice = $tblInvoice->fetchAll($where);
		if (count($rowInvoice) <= 0)
		{
			$tblUser = new App_Model_Db_Table_User();
			$rowUser = $tblUser->fetchRow("kopel='".$memberId."'");
				
			$rowInvoice = $tblInvoice->fetchNew();
			$rowInvoice->uid = $memberId;
			$rowInvoice->price = $totalPromo;
			$rowInvoice->discount = $discPromo;
			//$rowInvoice->invoiceOutDate = date("Y-m-d");
			$rowInvoice->invoiceOutDate = date("Y-m-d",strtotime($rowUser->createdDate));
			$rowInvoice->invoiceConfirmDate = "0000-00-00";
			
			//$temptime = time();
			$temptime = strtotime($rowUser->createdDate);
			$temptime = Pandamp_Lib_Formater::DateAdd('d',5,$temptime);
			
			$rowInvoice->expirationDate = strftime('%Y-%m-%d',$temptime);
			
			if (empty($access))
			{
				$rowInvoice->save();
			}
			else 
			{
				$result = $rowInvoice->save();
				
				if ($result)
				{
					$r = $memberId . " OK";
				}
				else 
				{
					$r = 'failure';
				}
				
			}
		}
		else 
		{
			if (!empty($access))
			{
				$r = "invoice:$memberId sudah pernah dibuat!";
			}
		}
		
		return $r;
	}
	
	/**
	 * _writeConfirmIndividualEmail
	 * @return JSON
	 */
	function _writeConfirmIndividualEmail($mailcontent, $fullname, $username, $password, $payment, $disc, $total, $guid, $email)
	{
		$obj 			= new Pandamp_Crypt_Password();
		
		$mailcontent 	= str_replace('$fullname',$fullname,$mailcontent);
		$mailcontent 	= str_replace('$username',$username,$mailcontent);
		$mailcontent 	= str_replace('$password',$password,$mailcontent);
		$mailcontent 	= str_replace('$disc',$disc,$mailcontent);
		$mailcontent 	= str_replace('$timeline',$payment,$mailcontent);
		$mailcontent 	= str_replace('$price',number_format($total),$mailcontent);
		$mailcontent 	= str_replace('$guid',$guid,$mailcontent);
		
		$mail_body 		= $mailcontent;
		
		// parse ini_file
		$config = new Zend_Config_Ini(CONFIG_PATH.'/mail.ini', 'mail');
		
		$mailAttempt = $this->add_mail($config->mail->sender->support->email,$email,$username,$config->mail->sender->support->name,$mail_body);		
		
		// try to save mail before send
		if ($mailAttempt)			
		{
			$sendAttempt = $this->send_mail();
			if ($sendAttempt)
			{
				
				$message =  "Please check your email at $email!";
				
				// update user
				$tblUser = new App_Model_Db_Table_User();
				$rowUser = $tblUser->find($obj->decryptPassword($guid))->current();
				if ($rowUser)
				{
					$rowUser->isEmailSent = 'Y';
				
					$rowUser->save();
				}
					
			}
			else 
			{
				$message =  "Error send mail but register user successfully!<br>Please contact our customer service for more information";
			}
		}
		else 
		{
			$message =  "Error saving mail!";
		}
		
		return $message;
	}
	
	/**
	 * _writeConfirmCorporateEmail
	 * @return JSON
	 */
	function _writeConfirmCorporateEmail($mailcontent, $fullname, $company, $payment, $disc, $total, $username, $guid, $email)
	{
		$obj 			= new Pandamp_Crypt_Password();
		
		$mailcontent 	= str_replace('$fullname',$fullname,$mailcontent);
		$mailcontent 	= str_replace('$company',$company,$mailcontent);
		$mailcontent 	= str_replace('$timeline',$payment,$mailcontent);
		$mailcontent 	= str_replace('$disc',$disc,$mailcontent);
		$mailcontent 	= str_replace('$price',number_format($total),$mailcontent);
		$mailcontent 	= str_replace('$username1',$username,$mailcontent);
		$mailcontent 	= str_replace('$guid',$guid,$mailcontent);
		
		// table User
		$tblUser = new App_Model_Db_Table_User();
		$where = $tblUser->getAdapter()->quoteInto('company=?',$company);
		$rowUser = $tblUser->fetchAll($where,'username ASC');
		
		$tag = '<table>';
		$tag .= '<tr><td><b>Username</b></td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td><b>Password</b></td></tr>';
		
		foreach ($rowUser as $rowsetUser)
		{
			$tag .= '<tr><td>'.$rowsetUser->username.'</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>'.$obj->decryptPassword($rowsetUser->password).'</td></tr>';					
		}
		
		$tag .= '</table>';
		
		$mailcontent = str_replace('$tag',$tag,$mailcontent);
		
		$mail_body = $mailcontent;
		
		// parse ini_file
		$config = new Zend_Config_Ini(CONFIG_PATH.'/mail.ini', 'mail');
		
		$mailAttempt = $this->add_mail($config->mail->sender->support->email,$email,$username,$config->mail->sender->support->name,$mail_body);		
		
		// try to save mail before send
		if ($mailAttempt)			
		{
			$sendAttempt = $this->send_mail();
			if ($sendAttempt)
			{
				
				$message =  "Please check your email at $email!";
				
				// update user
				$rowUser = $tblUser->find($obj->decryptPassword($guid))->current();
				if ($rowUser)
				{
					$rowUser->isEmailSent = 'Y';
				
					$rowUser->save();
				}
					
			}
			else 
			{
				$message =  "Error send mail but register user successfully!<br>Please contact our customer service for more information";
			}
		}
		else 
		{
			$message =  "Error saving mail!";
		}
		
		return $message;
	}
	
	/**
	 * _writeConfirmFreeEmail
	 * @return JSON
	 */
	function _writeConfirmFreeEmail($mailcontent, $fullname, $username, $password, $guid, $email, $package='')
	{
		$obj 			= new Pandamp_Crypt_Password();
		$aclMan 		= Pandamp_Acl::manager();
		
		$mailcontent 	= str_replace('$fullname',$fullname,$mailcontent);
		$mailcontent 	= str_replace('$username',$username,$mailcontent);
		$mailcontent 	= str_replace('$password',$password,$mailcontent);
		$mailcontent 	= str_replace('$guid',$guid,$mailcontent);
		$mailcontent 	= str_replace('$package',$package,$mailcontent);
		
		$mail_body 		= $mailcontent;
		
		// parse ini_file
		$config = new Zend_Config_Ini(CONFIG_PATH.'/mail.ini', 'mail');
		
		$mailAttempt = $this->add_mail($config->mail->sender->support->email,$email,$username,$config->mail->sender->support->name,$mail_body);		
		
		// try to save mail before send
		if ($mailAttempt)			
		{
			$sendAttempt = $this->send_mail();
			if ($sendAttempt)
			{
				
				$message =  "Please check your email at $email!";
				
				// update user
				$tblUser = new App_Model_Db_Table_User();
				$rowUser = $tblUser->find($obj->decryptPassword($guid))->current();
				if ($rowUser)
				{
					$rowUser->isEmailSent = 'Y';
				
					$rowUser->save();
				}
					
			}
			else 
			{
				$message =  "Error send mail but register user successfully!<br>Please contact our customer service for more information";
			}
		}
		else 
		{
			$message =  "Error saving mail!";
		}
		
		return $message;
	}
	
    function add_mail($sender,$recepientMail,$recepientName,$subject,$body)
    {
        $data=array('sender'    => $sender,
                    'recepientMail' => $recepientMail,
                    'recepientName' => $recepientName,
                    'subject'       => $subject,
                    'body'          => $body,
                    'ContentType'   => 'text/html'
                );

        $newsletter = new Pandamp_Lib_Newsletter();

        $add = $newsletter->addMail($data);

        if ($add===false) return $newsletter->errorMsg;
    }
    function send_mail()
    {
        require_once(ROOT_DIR.'/library/Pandamp/Lib/class.phpmailer.php');
        // set all attribute
        // ------------------------------- LOAD FROM CONFIG.ini
        $config = new Zend_Config_Ini(CONFIG_PATH.'/mail.ini', 'mail');
        $data=array('method'   => $config->mail->method,
                                'From'     => $config->mail->sender->support->email,
                                'FromName' => $config->mail->sender->support->name,
                                'Host'     => $config->mail->host,
                                'SMTPAuth' => $config->mail->auth,
                                'Username' => $config->mail->username,
                                'Password' => $config->mail->password
                                );

        $newsletter = new Pandamp_Lib_Newsletter();

        return $newsletter->Sendmail();
    }
	
	
	
}