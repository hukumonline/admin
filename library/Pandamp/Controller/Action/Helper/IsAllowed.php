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

        $front = Zend_Controller_Front::getInstance();
        $aclMan = $front->getParam('bootstrap')->getResource('acl');

        return $aclMan->isAllowed($auth->getIdentity()->username, $itemGuid, $action, $section);

    }
}
