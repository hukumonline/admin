<?php

/**
 * Description of Day
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Pandamp_Controller_Action_Helper_Day
{
    public function day($tday=null)
    {
        $day = "<select name=\"day\" id=\"day\">\n";
        if ($tday) {
            $day .= "<option value=\"" . $tday . "\" selected>$tday</option>\n";
            $day .= "<option value=''>Day</option>";
        } else {
            $day .= "<option value='' selected>Day:</option>";
        }
        for($i=1;$i <= 31; $i++) {
            if (($tday) and ($i == $tday)) {
                continue;
            } else {
                $day .= " <option value=\"" . $i ."\">$i</option>\n";
            }
        }

        $day .= "</select>\n\n";
        return $day;
    }
}
