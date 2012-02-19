<?php

/**
 * Description of LastLogin
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Pandamp_Controller_Action_Helper_LastLogin
{
    public function lastLogin($userId=NULL)
    {
        if ($userId)
        {
            $id = $userId;
        }
        else
        {
            $auth = Zend_Auth::getInstance();
            if (!$auth->hasIdentity())
            {
                return;
            }

            $identity = $auth->getIdentity();
            $id = $identity->kopel;

        }
		
        /*
        list($ret, $body) = Pandamp_Lib_Remote::serverCmd('lastLogin',array('kopel'=>$id));
        switch ($ret)
        {
            case 200:
                return $body;
                break;
            default :
                return NULL;
        }
        */
        
        $conn = Zend_Registry::get('db2');
		//$tblUserAccessLog = new App_Model_Db_Table_Log();
		$rowUserAccessLog = $conn->fetchRow("SELECT * FROM KutuUserAccessLog WHERE user_id='".$id."' AND NOT (lastlogin='0000-00-00 00:00:00' or isnull(lastlogin))",'user_access_log_id DESC');
		
		if (isset($rowUserAccessLog)) 
			$dLog = date('l F jS, Y \a\t g:ia',strtotime($rowUserAccessLog['lastlogin'])). ' from '.$rowUserAccessLog['user_ip'];
		else
			$dLog = '-';
        
			
		return $dLog;			

        /*
        $tblUserAccessLog = new App_Model_Db_Table_Log();
        $rowUserAccessLog = $tblUserAccessLog->fetchRow("user_id='".$id."' AND NOT (lastlogin='0000-00-00 00:00:00' or isnull(lastlogin))",'user_access_log_id DESC');

        if (isset($rowUserAccessLog))
                echo date('l F jS, Y \a\t g:ia',strtotime($rowUserAccessLog->lastlogin)). ' from '.$rowUserAccessLog->user_ip;
         *
         */
    }

}
