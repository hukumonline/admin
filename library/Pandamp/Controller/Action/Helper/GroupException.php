<?php

/**
 * Description of GroupException
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Pandamp_Controller_Action_Helper_GroupException
{
    public function groupException($where)
    {
        $rowset = App_Model_Show_AroGroupMap::show()->getGroupException($where);
        for($i=0;$i < count($rowset);$i++)
        {
            $aReturn[$i] = $rowset[$i]['value'];
        }
        
        return $aReturn;
    }
}
