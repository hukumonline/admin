<?php
/**
 * @author	2011-2018 Nihki Prihadi <nihki@madaniyah.com>
 * @version $Id: GetDownloadDocument.php 1 2013-12-05 12:16Z $
 */

class Pandamp_Controller_Action_Helper_GetDownloadDocument
{
	public function getDownloadDocument($catalogGuid)
	{
		$zl = Zend_Registry::get("Zend_Locale");
		if ($zl->getLanguage() == 'id')
			$conn = Zend_Registry::get('db1');
		else
			$conn = Zend_Registry::get('db3');
		
		$query = "SELECT * FROM `KutuCatalogAttribute`, `KutuRelatedItem` t2 WHERE `KutuCatalogAttribute`.catalogGuid=t2.itemGuid AND t2.relateAs IN ('RELATED_FILE','RELATED_IMAGE','RELATED_VIDEO') AND t2.relatedGuid='$catalogGuid' AND `KutuCatalogAttribute`.attributeGuid = 'docViewOrder' ORDER BY `KutuCatalogAttribute`.value ASC";
		$db = $conn->query($query);
		
		$rowsetRelatedItem = $db->fetchAll(Zend_Db::FETCH_OBJ);
		
		if ($rowsetRelatedItem) {
			return $rowsetRelatedItem;
		}
		
		return;
	}
}