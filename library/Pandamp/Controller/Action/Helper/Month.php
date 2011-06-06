<?php

/**
 * Description of Month
 * select month
 * @param $montharray
 * @return $month
 * 
 * @author nihki <nihki@madaniyah.com>
 */
class Pandamp_Controller_Action_Helper_Month
{
    public function month($montharray,$month=null)
    {
        $newMonth = null;
        $monthSelect = "\n<select name=\"month\">\n";
        if ($month) {
            $newMonth = $month - 1;
            $monthSelect .= "<option value=\"" . $month . "\" selected>$montharray[$newMonth]</option>\n";
            $monthSelect .= "<option value=''>Month</option>";
        } else {
            $monthSelect .= "<option value='' selected>Month:</option>";
        }
        
        for($j=0; $j < 12; $j++) {
            if (($month) and ($j == $newMonth)) {
                continue;
            } else {
                $monthSelect .= " <option value=\"" . ($j+1) ."\">$montharray[$j]</option>\n";
            }
        }

        $monthSelect .= "</select>\n\n";
        return $monthSelect;
    }
}
