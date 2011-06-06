<?php

/**
 * Description of BusinessType
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Pandamp_Controller_Action_Helper_BusinessType
{
    public function businessType($businessTypeId=NULL)
    {
        /*
        list($ret, $body) = Pandamp_Lib_Remote::serverCmd('selectUserBusiness', array('businessTypeId'=>$businessTypeId));
        return $body;
         *
         */

        $tblBusiness = new App_Model_Db_Table_Business();
        $row = $tblBusiness->fetchAll();

        $businessType = "<select name=\"businessType\" id=\"businessType\">\n";
        if ($businessTypeId) {
            $rowBusinessType = $tblBusiness->find($businessTypeId)->current();
            $businessType .= "<option value='$rowBusinessType->businessTypeId' selected>$rowBusinessType->description</option>";
            $businessType .= "<option value=''>Choose:</option>";
        } else {
            $businessType .= "<option value='' selected>Choose:</option>";
        }
        foreach ($row as $rowset) {
            if (($businessTypeId) and ($rowset->businessTypeId == $rowBusinessType->businessTypeId)) {
                continue;
            } else {
                $businessType .= "<option value='$rowset->businessTypeId'>$rowset->description</option>";
            }
        }
        $businessType .= "</select>\n\n";

        return $businessType;
    }
}
