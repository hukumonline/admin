<?php

/**
 * Description of MigrationController
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Customer_MigrationController extends Zend_Controller_Action
{
    function preDispatch()
    {
    	Zend_Session::start();
    	
        $this->_helper->layout->setLayout('layout-customer-migration');
    }
    function inaAction()
    {
        /* get GroupName
           echo $this->getUserGroupName('enisetiati');
         *
         */

        /* get Group Name Id
           $getGroupId = $this->getUserGroupId($this->getUserGroupName('enisetiati'));
           echo $getGroupId;
         *
         */

        /*
         * Hukumonline Indonesia
         * 11 = admin
         * 41 = klinik_admin
         * 39 = marketing
         * 36 = member_admin
         * 34 = news_admin
         * 40 = holproject
         * 20 = dc_admin
         * 25 = member_gratis
         * 26 = member_individual
         * 27 = member_corporate
         */

        $title = "<h4>MIGRASI HUKUMONLINE INDONESIA</h4><hr/>";

        echo $title.'<br>';

        $groupId = 27;

        require_once(CONFIG_PATH.'/master-status.php');

        $aroMap = App_Model_Show_Migration_AroGroupMapIn::show()->getObjectsByGroup($groupId);
        
        /*
        echo '<pre>';
        print_r($aroMap);
        echo '</pre>';
        *
        */
        
        foreach ($aroMap as $value)
        {
//			$modelUser = new App_Model_Db_Table_User();
//			$rowUser = $modelUser->fetchRow("username='".$value['name']."'");
//			if (!$rowUser) {
            $ignoredUser = MasterStatus::ignoreUserMigration();

            if (!in_array($value['name'], $ignoredUser))
            {
                //echo $id.' - '.$value['name'].'<br>';
                $rowUser = App_Model_Show_Migration_UserIn::show()->getUser($value['name']);
                //echo $id.' - '.$rowUser['fullName'].'<br>';

                /*
                $groupName = $this->getUserGroupName($rowUser['username']);
                $getGroupId = $this->getUserGroupId($groupName);
                 * 
                 */
                
                if ($rowUser) {

                $rowUser['packageId'] = $groupId;

                //list($ret, $body) = Pandamp_Lib_Remote::serverCmd('migrationUser', $rowUser);
                
				$modelUser = new App_Model_Db_Table_User();
				$dUser = $modelUser->fetchRow("username='".$rowUser['username']."'");
				$groupName = $this->getGroupName($groupId);

				if (!$dUser) 
				{
	                $data = $this->transformMigrationUser($rowUser);
					$result = $modelUser->insert($data);
					
					if ($result) {
						
						$this->updateKopel();
						
						$groupName = $this->getGroupName($groupId);
						
						//$acl = new Kutu_Acl_Adapter_Local();
						$acl = Pandamp_Acl::manager();
						//$acl->addUser($_POST['username'],$groupName);
						$acl->addUserToGroup($rowUser['username'],$groupName);
						
                        $message = "
                            <div class='box box-info closeable'>
                            User&nbsp;:&nbsp;<abbr>".$rowUser['username']."</abbr> data has been successfully saved to local.
                            </div><br>";
						
					}
					else 
					{
                        $message = "
                        <div class='box box-error'>ERROR</div>    
                        <div class='box box-error-msg'>
                        <ol>
                        <li>User&nbsp;:&nbsp;<abbr>".$rowUser['username']."</abbr> data has failed saved to local.</li>
                        </ol>
                        </div><br>";
						
					}
				
	                echo $message;
				}
				
                /*
                echo '<pre>';
                print_r($body);
                echo '</pre>';
                die;
                *
                */

                /*
                switch ($ret)
                {
                    case 200:
                        $message = "
                            <div class='box box-info closeable'>
                            User&nbsp;:&nbsp;<abbr>".$rowUser['username']."</abbr> data has been successfully saved to local.
                            </div><br>";
                        break;
                    default:
                        $message = "
                        <div class='box box-error'>ERROR</div>    
                        <div class='box box-error-msg'>
                        <ol>
                        <li>User&nbsp;:&nbsp;<abbr>".$rowUser['username']."</abbr> data has failed saved to local.</li>
                        </ol>
                        </div><br>";
                }
                */

//                echo $message;
                
                }
                /*
                else 
                {
                	echo "
                        <div class='box box-error'>ERROR</div>    
                        <div class='box box-error-msg'>
                        <ol>
                        <li>User&nbsp;:&nbsp;<abbr>".$value['name']."</abbr> not active.</li>
                        </ol>
                        </div><br>";
                }
                */
            }
//			}
        }
    }
    protected function getUserGroupId($groupName)
    {
        $getGroupId = App_Model_Show_Migration_AroGroupIn::show()->get_group_id($groupName);

        return ($getGroupId['id'])? $getGroupId['id'] : 0;
    }
    protected function getUserGroupName($username)
    {
        //$modelAro = new App_Model_Db_Table_Migration_AroIn();
        //$aro = $modelAro->getUserGroupId('zapatista');
        $aro = App_Model_Show_Migration_AroIn::show()->getUserGroupId($username);
        //echo $aro['id']; -> output:17661

        $aReturn = App_Model_Show_Migration_AroGroupMapIn::show()->get_object_groups($aro['id']);

        for ($i=0; $i < count($aReturn); $i++)
        {
            $aTmp = App_Model_Show_Migration_AroGroupIn::show()->get_group_data($aReturn[$i]['group_id']);
            $aReturn[$i] = $aTmp[0]['value'];
        }

        // eg. output: admin
        return ($aReturn[1])? $aReturn[1] : '';
    }
	function transformMigrationUser($value)
	{
		if (($value["birthday"] == "1970-01-01") || ($value["birthday"] == ""))
		{
			$birthday = "0000-00-00";
		}
		else
		{
			$birthday = $value["birthday"];
		}
		
		$groupName = $this->getGroupName($value['packageId']);
		
		$acl = Pandamp_Acl::manager();
		$groupId = $acl->getGroupIds($groupName);
		
		
		$data = array(
			 'kopel'			=> $this->generateKopel()
			,'username'			=> $value['username']
			,'password'			=> $value['password']
			,'fullName'			=> ($value['fullName'])? $value['fullName'] : ''
			,'birthday'			=> $birthday
			,'phone'			=> ($value['phone'])? $value['phone'] : ''
			,'fax'				=> ($value['fax'])? $value['fax'] : ''
			,'gender'			=> $value['gender']
			,'email'			=> $value['email']
			,'company'			=> ($value['company'])? $value['company'] : ''
			,'address'			=> ($value['address'])? $value['address'] : '' 
			,'state'			=> 7
			,'countryId'		=> 'ID'
			,'newArticle'		=> $value['newArticle']
			,'weeklyList'		=> $value['weeklyList']
			,'monthlyList'		=> $value['monthlyList']
			,'packageId'		=> $groupId
			,'promotionId'		=> $value['promotionId']
			,'educationId'		=> $value['educationId']
			,'expenseId'		=> $value['expenseId']
			,'paymentId'		=> $value['paymentId']
			,'businessTypeId'	=> $value['businessTypeId']
			,'periodeId'		=> $value['periodeId']
			,'activationDate'	=> $value['activationDate']
			,'isEmailSent'		=> $value['isEmailSent']
			,'isEmailSentOver'	=> $value['isEmailSentOver']
			,'createdDate'		=> $value['createdDate']
			,'createdBy'		=> $value['createdBy']
			,'modifiedDate'		=> ($value['updatedDate'])? $value['updatedDate'] : ''
			,'modifiedBy'		=> ($value['updatedBy'])? $value['updatedBy'] : ''
			,'isActive'			=> $value['isActive']
			,'isContact'		=> $value['isContact']
		);
		
		return $data;
	}
	protected function generateKopel()
	{
		$modelNumber = new App_Model_Db_Table_Number();
        $rowset = $modelNumber->fetchRow();
        $num = $rowset->user;
		$totdigit = 5;
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
	protected function getGroupName($groupId)
	{
		if ($groupId == 11)
		{
			$groupName = "Master";
			//$groupName = "Super Admin";
			//$groupName = "Admin Ina";
		}
		else if ($groupId == 41) 
		{
			$groupName = "Clinic Admin";
		}
		else if ($groupId == 39) 
		{
			$groupName = "Marketing";
		}
		else if ($groupId == 36) 
		{
			$groupName = "Klanten";
		}
		else if ($groupId == 34) 
		{
			$groupName = "News Admin";
		}
		else if ($groupId == 40) 
		{
			$groupName = "HolProject";
		}
		else if ($groupId == 20) 
		{
			$groupName = "Dc Admin";
		}
		else if ($groupId == 21) 
		{
			$groupName = "Admin En";
		}
		else if ($groupId == 25) 
		{
			$groupName = "Free";
		}
		else if ($groupId == 26) 
		{
			$groupName = "Individual";
		}
		else if ($groupId == 27) 
		{
			//$groupName = "Corporate";
			$groupName = "Ilb";
		}
		
		return $groupName;
	}
}
