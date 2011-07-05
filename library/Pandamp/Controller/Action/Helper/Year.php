<?php

/**
 * Description of Year
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Pandamp_Controller_Action_Helper_Year
{
    public function year($tyear=null)
    {
        $year = "<select name=\"year\" id=\"year\">\n";
        if ($tyear) {
            $year .= "<option value=\"" . $tyear . "\" selected>$tyear</option>\n";
            $year .= "<option value=''>Year:</option>";
        } else {
            $year .= "<option value='' selected>Year:</option>";
        }
        for ($i = date('Y', time()); $i > date('Y', time()) - 91; $i--) {
            if (($tyear) and ($i == $tyear)) {
                continue;
            } else {
                $year .= " <option value=\"" . $i ."\">$i</option>\n";
            }
        }

        $year .= "</select>\n\n";
        return $year;
    }
}
