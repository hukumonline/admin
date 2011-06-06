<?php

/**
 * Description of Education
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Pandamp_Controller_Action_Helper_Education
{
    public function education($edu=NULL)
    {
        /*
        list($ret, $body) = Pandamp_Lib_Remote::serverCmd('selectUserEducation', array('edu'=>$edu));
        return $body;
         *
         */

        $tblEducation = new App_Model_Db_Table_Education();
        $row = $tblEducation->fetchAll();

        $education = "<select name=\"education\" id=\"education\">\n";
        if ($edu) {
            $rowEducation = $tblEducation->find($edu)->current();
            $education .= "<option value='$rowEducation->educationId' selected>$rowEducation->description</option>";
            $education .= "<option value =''>Choose:</option>";
        } else
        {
            $education .= "<option value ='' selected>Choose:</option>";
        }

        foreach ($row as $rowset) {
            if (($edu) and ($rowset->educationId == $rowEducation->educationId)) {
                continue;
            } else {
                $education .= "<option value='$rowset->educationId'>$rowset->description</option>";
            }
        }

        $education .= "</select>\n\n";
        return $education;
    }
}
