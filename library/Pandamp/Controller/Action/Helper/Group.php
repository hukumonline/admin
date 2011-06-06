<?php

/**
 * Description of Group
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Pandamp_Controller_Action_Helper_Group
{
    public function group($group)
    {
        $acl = Pandamp_Acl::manager();
        $aReturn = $acl->getGroupData($group);

        return ($aReturn[3])? $aReturn[3] : '-';
    }
}
