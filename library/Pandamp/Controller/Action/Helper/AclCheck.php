<?php

/**
 * Description of AclCheck
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Pandamp_Controller_Action_Helper_AclCheck
{
    public function aclCheck($section, $aco)
    {
        $auth = Zend_Auth::getInstance();
        if (!$auth->hasIdentity()) {
            return false;
        }
        
        $username = $auth->getIdentity()->username;
        
		$acl = Pandamp_Acl::manager();
		return $acl->checkAcl($section,$aco,'user', $username, false,false);
    }
}
