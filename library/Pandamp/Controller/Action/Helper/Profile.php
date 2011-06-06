<?php

/**
 * Description of State
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Pandamp_Controller_Action_Helper_Profile
{
    public function profile($profile=null)
    {
        $tblProfile = new App_Model_Db_Table_Profile();
        $row = $tblProfile->fetchAll();

        $select_profile = "<select name=\"profile\" id=\"profile\">\n";
        if ($profile) {
            $rowProfile = $tblProfile->find($profile)->current();
            $select_profile .= "<option value='$rowProfile->guid' selected>$rowProfile->title</option>";
            $select_profile .= "<option value =''>Choose:</option>";
        } else {
            $select_profile .= "<option value ='' selected>Choose:</option>";
        }

        foreach ($row as $rowset) {
            if (($profile) and ($rowset->guid == $rowProfile->guid)) {
                continue;
            } else {
                $select_profile .= "<option value='$rowset->guid'>$rowset->title</option>";
            }
        }

        $select_profile .= "</select>\n\n";
        return $select_profile;
    }
}
