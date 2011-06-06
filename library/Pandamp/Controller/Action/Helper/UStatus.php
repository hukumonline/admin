<?php

/**
 * Description of UStatus
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Pandamp_Controller_Action_Helper_UStatus
{
    public function uStatus($usta=NULL)
    {
        $modelUserStatus = new App_Model_Db_Table_UserStatus();
        $row = $modelUserStatus->fetchAll();

        $select_user_status = "<select name=\"ustatus\" id=\"ustatus\">\n";
        if ($usta) {
            $rowUserStatus = $modelUserStatus->find($usta)->current();
            $select_user_status .= "<option value='$rowUserStatus->accountStatusId' selected>$rowUserStatus->status</option>";
            $select_user_status .= "<option value ='0'>Choose:</option>";
        } else {
            $select_user_status .= "<option value ='0' selected>Choose:</option>";
        }

        foreach ($row as $rowset) {
            if (($usta) and ($rowset->accountStatusId == $rowUserStatus->accountStatusId)) {
                continue;
            } else {
                $select_user_status .= "<option value='$rowset->accountStatusId'>$rowset->status</option>";
            }
        }

        $select_user_status .= "</select>\n\n";
        return $select_user_status;
    }
}
