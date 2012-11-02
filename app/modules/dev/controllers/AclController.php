<?php

/**
 * Description of AclController
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Dev_AclController extends Zend_Controller_Action
{
    function isAllowedAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $acl = Pandamp_Acl::manager();
        
        if ($acl->isAllowed('seyimut','all','aclist'))
            echo 'ALLOWED';
        else
            echo 'NO ACCESS';
            

//		$aReturn = $acl->getUserGroupIds('zapatista'); 
//		if ($acl->getPermissionsOnContent('', $aReturn[1], 'membership'))
//			echo 'ALLOWED';
//		else 
//			echo 'NO ACCESS';

//		if ($acl->checkAcl('site', 'all', 'user', 'dedi',false,false))
//			echo 'ALLOWED';
//		else 
//			echo 'NO ACCESS';
		
		
    }
}
