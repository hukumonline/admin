<?php

/**
 * Description of State
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Pandamp_Controller_Action_Helper_State
{
    public function state($province=null)
    {
        /*
        list($ret, $body) = Pandamp_Lib_Remote::serverCmd('selectState', array('state'=>$province));
        return $body;
         *
         */

        $tblProvince = new App_Model_Db_Table_State();
        $row = $tblProvince->fetchAll();

        $select_province = "<select name=\"province\" id=\"province\">\n";
        if ($province) {
            $rowProvince = $tblProvince->find($province)->current();
            $select_province .= "<option value='$rowProvince->pid' selected>$rowProvince->pname</option>";
            $select_province .= "<option value =''>Choose:</option>";
        } else {
            $select_province .= "<option value ='' selected>Choose:</option>";
        }

        foreach ($row as $rowset) {
            if (($province) and ($rowset->pid == $rowProvince->pid)) {
                continue;
            } else {
                $select_province .= "<option value='$rowset->pid'>$rowset->pname</option>";
            }
        }

        $select_province .= "</select>\n\n";
        return $select_province;
    }
}
