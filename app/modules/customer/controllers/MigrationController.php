<?php

/**
 * Description of MigrationController
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Customer_MigrationController extends Zend_Controller_Action
{
    function  preDispatch()
    {
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
         */

        $title = "<h4>MIGRASI HUKUMONLINE INDONESIA</h4><hr/>";

        echo $title.'<br>';

        $groupId = 36;

        require_once(CONFIG_PATH.'/master-status.php');

        $aroMap = App_Model_Show_Migration_AroGroupMapIn::show()->getObjectsByGroup($groupId);
        
        echo '<pre>';
        print_r($aroMap);
        echo '</pre>';
        
        foreach ($aroMap as $value)
        {
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

                $rowUser['packageId'] = $groupId;

                list($ret, $body) = Pandamp_Lib_Remote::serverCmd('migrationUser', $rowUser);
				
                /*
                echo '<pre>';
                print_r($body);
                echo '</pre>';
                die;
                *
                */

                switch ($ret)
                {
                    case 200:
                        $message = "
                            <div class='box box-info closeable'>
                            User&nbsp;:&nbsp;<abbr>".$rowUser['username']."</abbr> data has been successfully saved to local.
                            </div>";
                        break;
                    default:
                        $message = "
                        <div class='box box-error'>ERROR</div>    
                        <div class='box box-error-msg'>
                        <ol>
                        <li>User&nbsp;:&nbsp;<abbr>".$rowUser['username']."</abbr> data has failed saved to local.</li>
                        </ol>
                        </div>";
                }

                echo $message.'<br>';
            }
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
}
