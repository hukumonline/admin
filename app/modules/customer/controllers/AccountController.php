<?php

/**
 * Description of AccountController
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Customer_AccountController extends Zend_Controller_Action
{
    function  preDispatch()
    {
        $this->_helper->layout->setLayout('layout-customer-account');

    }
    function registerAction()
    {
        if ($this->getRequest()->getPost())
        {
            $value = $this->getRequest()->getPost();

            $this->view->value = $value;

            /*
            list($ret, $body) = Pandamp_Lib_Remote::serverCmd('register', $value);
            
            switch ($ret)
            {
                case 200:
                    $this->view->message = "User&nbsp;:&nbsp;<abbr>".$value['username']."</abbr> data has been successfully saved.";
                    break;
                default:
                    $this->view->error_message = "failure";
            }
            */
            
			$data = $this->transformRegister($value);
			
			$modelUser = new App_Model_Db_Table_User();
			$id = $modelUser->insert($data);
			
			$this->updateKopel();
			
			/**
			 * SELECT id, parent_id, value, name, lft, rgt
			 * eg. $aReturn = $acl->getGroupData(15)
			 * print_r($aReturn);
			 * output: Array ( [0] => 15 [1] => 10 [2] => Super Administrator [3] => super_admin [4] => 10 [5] => 11 ) 
			 */
			$acl = Pandamp_Acl::manager();
			//$aReturn = $acl->getGroupData($value['aro_groups']);
			$aReturn = App_Model_Show_AroGroup::show()->getUserGroup($value['aro_groups']);
			//echo '<pre>';
			//print_r($aReturn);
			//echo '</pre>';
			//$acl->addUser($value['username'],$aReturn[3]);
			$acl->addUserToGroup($value['username'], $aReturn['name']);
			
			$formater = new Pandamp_Core_Hol_User();
			
			// Do you want Email Confirmation send?
			if (isset($value['ec']) == 1)
			{
				//echo 'y';
				
				$payment = ($value['payment'])? $value['payment'] : 0;
				$promotionCode = ($value['promotioncode'])? $value['promotioncode'] : '';
				
				switch ($value['aro_groups'])
				{
					/**
					 * @modifiedDate: December 07, 2012
					 * @modifiedDate-2: January 29, 2013
					 */
					case 14: // individual
						
						$mailcontent = $formater->getMailContent('konfirmasi-email-individual');
						//$disc = $formater->checkPromoValidation('Disc',$value['aro_groups'],$promotionCode,$payment);
						//$total = $formater->checkPromoValidation('Total',$value['aro_groups'],$promotionCode,$payment);
						$total = $formater->checkPromoValidation('Total',$value['aro_groups'],$payment);
						$disc = $formater->checkPromoValidation('Disc',$value['aro_groups'],$payment);
						
						//$m = $formater->_writeConfirmIndividualEmail($mailcontent,$value['fullname'],$value['username'],$value['password'],$payment,$disc,$total,base64_encode($id),$value['email']);
						$m = $formater->_writeConfirmIndividualEmail($mailcontent,$value['aro_groups'],$value['fullname'],$value['username'],$value['password'],$payment,$disc,$total,base64_encode($id),$value['email']);
						
						break;
						
					case 15: // corporate/basic
					case 16: // standard
					case 18: // professional
						
						$mailcontent = $formater->getMailContent('konfirmasi-email-korporasi');
						//$disc = $formater->checkPromoValidation('Disc',$value['aro_groups'],$promotionCode,$payment);
						//$total = $formater->checkPromoValidation('Total',$value['aro_groups'],$promotionCode,$payment);
						$total = $formater->checkPromoValidation('Total',$value['aro_groups'],$payment);
						$disc = $formater->checkPromoValidation('Disc',$value['aro_groups'],$payment);
						
						//$m = $formater->_writeConfirmCorporateEmail($mailcontent,$value['fullname'],$value['company'],$payment,$disc,$total,$value['username'],base64_encode($id),$value['email']);
						$m = $formater->_writeConfirmCorporateEmail($mailcontent,$value['aro_groups'],$value['fullname'],$value['company'],$payment,$disc,$total,$value['username'],base64_encode($id),$value['email']);
						
						break;
						
					default:
						
						$mailcontent = $formater->getMailContent('konfirmasi email gratis');
						$m = $formater->_writeConfirmFreeEmail($mailcontent,$value['fullname'],$value['username'],$value['password'],base64_encode($id),$value['email'],$aReturn['name']);
						
						break;
				}
				
				$this->view->message = $m;
			}
			else
			{
				//echo 't';
			}
            
        }
    }
	function transformRegister($value)
	{
		$obj = new Pandamp_Crypt_Password();
		
		$month 			= ($value['month'])? $value['month'] : '00';
		$day 			= ($value['day'])? $value['day'] : '00';
		$year 			= ($value['year'])? $value['year'] : '0000';
		$newArticle		= (isset($value['newArticle']))? $value['newArticle'] : '';
		$newRegulation	= (isset($value['newRegulation']))? $value['newRegulation'] : '';
		$newWRegulation	= (isset($value['newWeeklyRegulation']))? $value['newWeeklyRegulation'] : '';
		$isContact 		= (isset($value['iscontact']))? $value['iscontact'] : '';
		
		if ($value['gender'] == 1)
		{
			$gender = 'L';
		}
		else if($value['gender'] == 2)
		{
			$gender = 'P';
		}
		else
		{
			$gender = 'N';
		}
		
		$data = array(
			 'kopel'			=> $this->generateKopel()
			,'username'			=> $value['username']
			,'password'			=> $obj->encryptPassword($value['password'])
			,'fullName'			=> ($value['fullname'])? $value['fullname'] : ''
			,'birthday'			=> $year.'-'.$month.'-'.$day
			,'phone'			=> ($value['phone'])? $value['phone'] : ''
			,'fax'				=> ($value['fax'])? $value['fax'] : ''
			,'gender'			=> $gender
			,'email'			=> $value['email']
			,'company'			=> ($value['company'])? $value['company'] : ''
			,'address'			=> ($value['address'])? $value['address'] : '' 
			,'city'				=> ($value['city'])? $value['city'] : ''
			,'state'			=> ($value['province'])? $value['province'] : ''
			,'countryId'		=> ($value['countryId'])? $value['countryId'] : ''
			,'zip'				=> ($value['zip'])? $value['zip'] : ''
			,'indexCol'			=> 0
			,'newArticle'		=> ($newArticle == 1)? 'Y' : 'N'
			,'weeklyList'		=> ($newWRegulation == 1)? 'Y' : 'N'
			,'monthlyList'		=> ($newRegulation == 1)? 'Y' : 'N'
			,'packageId'		=> $value['aro_groups']
			,'promotionId'		=> ($value['promotioncode'])? $value['promotioncode'] : ''
			,'educationId'		=> ($value['education'])? $value['education'] : 0
			,'expenseId'		=> ($value['expense'])? $value['expense'] : 0
			,'paymentId'		=> ($value['payment'])? $value['payment'] : 0
			,'trialInDays'		=> ($value['trial'])? $value['trial'] : 0
			,'businessTypeId'	=> ($value['businessType'])? $value['businessType'] : 0
			,'periodeId'		=> 1
			,'createdDate'		=> date('Y-m-d H:i:s')
			,'createdBy'		=> $value['createdBy']
			,'isContact'		=> ($isContact == 1)? 'Y' : 'N'
			,'notes'			=> $value['notes']
		);
		
		return $data;
	}
	protected function generateKopel()
	{
		$rowset = App_Model_Show_Number::show()->getNumber();
		$num = $rowset['user'];
		$totdigit = 6;
		$num = strval($num);
		$jumdigit = strlen($num);
		$kopel = str_repeat("0",$totdigit-$jumdigit).$num;
		
		return $kopel;
	}
	protected function updateKopel()
	{
		$modelNumber = new App_Model_Db_Table_Number();
		$rowset = $modelNumber->fetchRow();
		$rowset->user = $rowset->user += 1;
		$rowset->save();
	}
    

    /*
    function checkusernameAction()
    {
        $this->_helper->viewRenderer->setNoRender(TRUE);
        
        $username = $this->_getParam('username');

        $tbluser = new App_Model_Db_Table_User();
        $where = $tbluser->getAdapter()->quoteInto("username=?",$username);
        $rowset = $tbluser->fetchRow($where);
        if(count($rowset)>0)
            $valid = 'false';
        else
            $valid = 'true';

        echo $valid;
        die();
    }
     *
     */
}

