<?php

/**
 * Description of IsAllowed
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Pandamp_Controller_Action_Helper_IsAllowed
{
    public function isAllowed($itemGuid, $action, $section='content')
    {
        $auth = Zend_Auth::getInstance();
        if (!$auth->hasIdentity()) {
            return false;
        }
        
        $username = $auth->getIdentity()->username;

        $front = Zend_Controller_Front::getInstance();
        $aclMan = $front->getParam('bootstrap')->getResource('acl');
        
        $aReturn = App_Model_Show_AroGroup::show()->getUserGroup($auth->getIdentity()->packageId);
        
        if (($aReturn['name'] == "Master") || ($aReturn['name'] == "Super Admin"))
        	$content = 'all-access';
        else 
        	$content = $itemGuid;

        return $aclMan->isAllowed($username, $content, $action, $section);

    }
}
