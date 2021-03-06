<?php
/**
 * @author	2011-2018 Nihki Prihadi <nihki@madaniyah.com>
 * @version $Id: GetCountCatalogDesktop.php 1 2013-12-02 15:06Z $
 */

class Pandamp_Controller_Action_Helper_GetShareCount
{
	public function getShareCount($catalogGuid, $social)
	{
		$viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
		if (null === $viewRenderer->view) {
			$viewRenderer->initView();
		}
		$view = $viewRenderer->view;
		
		$config = Pandamp_Config::getConfig();
		
		$catalogs = App_Model_Show_Catalog::show()->getCatalogByGuid($catalogGuid);
		
		if (($catalogs) && (!in_array($catalogs['profileGuid'], array('partner','author','kategoriklinik')))) {
if (in_array($catalogs['profileGuid'],['article','isuhangat'])) {			
				$uri = "berita/baca/".$catalogs['guid']."/".$catalogs['shortTitle'];
			}
			else if ($catalogs['profileGuid']=='klinik') {
				$uri = "klinik/detail/".$catalogs['guid']."/".$catalogs['shortTitle'];
			}
			/*else if ($catalogs['profileGuid']=='author') {
				$mitraGuid = $view->getCatalogAttribute($catalogs['guid'],'fixedSelectMitra');
				$uri = "klinik/penjawab/$catalogs['guid']/mitra/$mitraGuid";
			}*/
			else if (in_array($catalogs['profileGuid'], array('kutu_putusan','kutu_peraturan','kutu_rancangan_peraturan','kutu_peraturan_kolonial')))
			{
				$node = $view->getNode($catalogs['guid']);
				$lnode = $view->getLabelNode($node);
				$uri = "pusatdata/detail/".$catalogs['guid']."/".$lnode."/".$node."/".$catalogs['shortTitle'];
			}
				
			$url = $config->web->url->base . DS . $uri;
			
			$sharecount = new Pandamp_Lib_ShareCount($url);
			
			switch ($social) {
				case 'facebook':
					return $sharecount->get_fb();
					break;
				case 'twitter':
					return $sharecount->get_tweets();
					break;
			}
			
		}
		
		return;
	}
}
