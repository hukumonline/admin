<?php
/**
 * @author	2011-2018 Nihki Prihadi <nihki@madaniyah.com>
 * @version $Id: GetCatalogAttribute.php 1 2015-03-05 11:26Z $
 */

class Pandamp_Controller_Action_Helper_GetPollingHits
{
    public function getPollingHits($catalogGuid)
    {
    	$queryAttr="SELECT SUM(hits) AS hits FROM options WHERE pollGuid ='".$catalogGuid."'";
    	$db = Zend_Db_Table::getDefaultAdapter()->query($queryAttr);
    	$rowset = $db->fetchAll(Zend_Db::FETCH_OBJ);
    	/*$total_votes = 0;
    	foreach ($rowset as $row) {
    		$total_votes = $total_votes + $row->hits;
    	}*/
    	return $rowset[0]->hits;
    }
    
}
