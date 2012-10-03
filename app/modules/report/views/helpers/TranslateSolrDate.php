<?php
/**
 * @author	2012-2013 Nihki Prihadi
 * @version $Id: TranslateSolrDate.php 1 2012-10-03 18:27Z $
 */

class Report_View_Helper_TranslateSolrDate
{
	public function translateSolrDate($date)
	{
		$aDateTime = str_replace(array('T','Z'),' ',$date);
		$mysqlDate = date('d.m.y H:i:s',strtotime(trim($aDateTime)));
		
		return $mysqlDate;
	}
}