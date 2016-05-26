<?php
/**
 * @author	2011-2018 Nihki Prihadi <nihki@madaniyah.com>
 * @version $Id: GetCountCatalogDesktop.php 1 2013-12-02 15:06Z $
 */

class Pandamp_Controller_Action_Helper_GetCountCatalog
{
	public function getCountCatalog($catalogGuid, $type)
	{
		$catalogs = App_Model_Show_Catalog::show()->getCatalogByGuid($catalogGuid);
		
		if (($catalogs) && (!in_array($catalogs['profileGuid'], array('partner','author','kategoriklinik')))) {
			switch ($type) {
				case 'desktop':
					if (in_array($catalogs['profileGuid'], array('article','talks','isuhangat','kutu_agenda','video','infografis'))) {
						$valueText = 'TICKER';
					}
					else if ($catalogs['profileGuid']=='klinik') {
						$valueText = 'klinik';
					}
					else if (in_array($catalogs['profileGuid'], array('kutu_peraturan','kutu_rancangan_peraturan','kutu_peraturan_kolonial','kutu_putusan')))
					{
						$valueText = 'pusatdata';
					}
					
					break;
					
				case 'mobile':
					if (in_array($catalogs['profileGuid'], array('article','talks','isuhangat','kutu_agenda','video','infografis'))) {
						$valueText = 'TICKER-MOBILE';
					}
					else if ($catalogs['profileGuid']=='klinik') {
						$valueText = 'klinik-mobile';
					}
					else if (in_array($catalogs['profileGuid'], array('kutu_peraturan','kutu_rancangan_peraturan','kutu_peraturan_kolonial','kutu_putusan')))
					{
						$valueText = 'pusatdata-mobile';
					}
						
					break;
			}
			
			$assetDb = new App_Model_Db_Table_AssetSetting();
			$assets = $assetDb->fetchRow("guid='$catalogGuid' AND valueText='$valueText'");
				
			if ($assets) return $assets->valueInt;
				
		}
		
		return;
	}
}	