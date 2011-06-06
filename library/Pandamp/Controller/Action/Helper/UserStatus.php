<?php

/**
 * Description of GetCatalogAttribute
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Pandamp_Controller_Action_Helper_UserStatus
{
    public function userStatus($statusId)
    {
        $rowset = App_Model_Show_UserStatus::show()->getUserStatus($statusId);
        if ($rowset) return $rowset['status'];
    }

}
