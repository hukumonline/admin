<?php

/**
 * Description of Gender
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Pandamp_Controller_Action_Helper_Gender
{
    public function gender($tgender=NULL)
    {
        $arrayGender = array(0 => "Select Sex:","Male", "Female");

        $gender = "<select name=\"gender\" id=\"gender\">\n";
        foreach ($arrayGender as $key => $val) {
            $sel = (isset($tgender) && ($key == abs($tgender))) ? " selected" : "";
            $gender .= '<option value='.$key . $sel.'>'.$val.'</option>';
        }

        $gender .= "</select>\n\n";
        return $gender;
    }
}
