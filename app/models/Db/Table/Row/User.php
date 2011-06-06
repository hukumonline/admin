<?php

/**
 * Description of User
 *
 * @author nihki <nihki@madaniyah.com>
 */
class App_Model_Db_Table_Row_User extends Zend_Db_Table_Row_Abstract
{
    protected function  _postDelete()
    {
        $modelUserFinance = new App_Model_Db_Table_UserFinance();
        $modelUserFinance->delete("userId='".$this->kopel."'");

        $acl = Pandamp_Acl::manager();
        $acl->deleteUser($this->username);

        $modelOrder = new App_Model_Db_Table_Order();
        $fetchOrder = $modelOrder->fetchAll("userId='".$this->kopel."'");
        foreach ($fetchOrder as $rowOrder)
        {
            $rowOrder->delete();
        }
        
	    $registry = Zend_Registry::getInstance();
	    $config = $registry->get(Pandamp_Keys::REGISTRY_APP_OBJECT);
	    $cdn = $config->getOption('cdn');
	    
	    $sDir = $cdn['static']['dir']['photo'];
        //$sDir = ROOT_DIR.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'photo';
        try {
            if (file_exists($sDir."/".$this->kopel.".gif"))		{ unlink($sDir."/".$this->kopel.".gif"); 	}
            if (file_exists($sDir."/".$this->kopel.".jpg"))		{ unlink($sDir."/".$this->kopel.".jpg"); 	}
            if (file_exists($sDir."/".$this->kopel.".jpeg")) 	{ unlink($sDir."/".$this->kopel.".jpeg"); 	}
            if (file_exists($sDir."/".$this->kopel.".png"))		{ unlink($sDir."/".$this->kopel.".png"); 	}
        }
        catch (Exception $e)
        {

        }
    }
}
