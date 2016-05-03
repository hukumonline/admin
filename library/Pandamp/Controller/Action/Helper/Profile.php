<?php
/**
 * Description of State
 * @author nihki <nihki@madaniyah.com>
 */

class Pandamp_Controller_Action_Helper_Profile
{
    public function profile($profile=null)
    {
    	$auth = Zend_Auth::getInstance();
    	
    	$packageId = $auth->getIdentity()->packageId;
    	
    	$zl   = Zend_Registry::get("Zend_Locale");
    	
    	$lang = $zl->getLanguage();
    	
    	$acl  = Pandamp_Acl::manager();
    	
    	$modelAroGroup = App_Model_Show_AroGroup::show();
    	
        $tblProfile = new App_Model_Db_Table_Profile();
        $row = $tblProfile->fetchAll();

        $select_profile = "<select name=\"profile\" id=\"profile\" class=\"form-control\" style=\"width: 60%;\">\n";
        if ($profile) {
            $rowProfile = $tblProfile->find($profile)->current();
            $select_profile .= "<option value='$rowProfile->guid' selected>$rowProfile->title</option>";
            $select_profile .= "<option value =''>Choose:</option>";
        } else {
            $select_profile .= "<option value ='' selected>Choose:</option>";
        }

        foreach ($row as $rowset) {
        	
			$aReturn = $modelAroGroup->getUserGroup($packageId);
			
			if (($aReturn['name'] == "Master") || ($aReturn['name'] == "Super Admin"))
				$content = 'all-access';
			else 
				$content = $rowset->profileType;
				
            if (($profile) and ($rowset->guid == $rowProfile->guid)) {
            	
                continue;
                
            } else {
            	
            	if (($lang == 'en')) {
            		$select_profile .= "<option value='$rowset->guid'>$rowset->title</option>";
            	}
            	else 
            	{
					if ($acl->getPermissionsOnContent('', $aReturn['name'], $content))
					{
						$select_profile .= "<option value='$rowset->guid'>$rowset->title</option>";
					}
					else 
					{
						continue;
					}
            	}
            }
        }

        $select_profile .= "</select>\n\n";
        return $select_profile;
    }
}
