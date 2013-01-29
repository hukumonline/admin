<?php
/**
 * @author	2013-2014 Nihki Prihadi <nihki@madaniyah.com>
 * @version $Id: SitemapController.php 1 2013-01-22 13:05Z $
 */

class Dms_SitemapController extends Zend_Controller_Action 
{
	/**
	 * Add link to sitemap
	 */
	public function addAction()
	{
		$this->_helper->getHelper('layout')->disableLayout();
		$this->_helper->getHelper('viewRenderer')->setNoRender();
		
		$config = Pandamp_Config::getConfig();
		
		$file = ROOT_DIR . DS . 'sitemap_news.xml';
		
		$output = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL
				. '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">' . PHP_EOL;
		
		//$folderGuid = array('fb16','lt4a0a533e31979','fb29');
		$profileGuid = array('article','klinik','kutu_peraturan');
		
		$rowset = App_Model_Show_Catalog::show()->fetchNewsCatalog($profileGuid,0,50);

		foreach ($rowset as $row)	{
			
			if ($row['profileGuid'] == 'klinik') {
				$clinicTitle = App_Model_Show_CatalogAttribute::show()->getCatalogAttributeValue($row['guid'],'fixedCommentTitle');
				$url = $config->web->url->base . DS . 'klinik' . DS . 'detail' . DS . $row['guid'] . DS . App_Model_Show_CatalogAttribute::show()->getFormatedURLForKlinikTitle($clinicTitle);
			} 
			else if ($row['profileGuid'] == 'kutu_peraturan')
			{
				$peraturanTitle = App_Model_Show_CatalogAttribute::show()->getCatalogAttributeValue($row['guid'],'fixedTitle');
				$peraturanSubTitle = App_Model_Show_CatalogAttribute::show()->getCatalogAttributeValue($row['guid'],'fixedSubTitle');
				$url = $config->web->url->base . DS . 'pusatdata' . DS . 'detail' . DS . $row['guid'] . DS . 'node' . DS . $row['folderGuid'] . DS . App_Model_Show_CatalogAttribute::show()->getFormatedURLForDetalPeraturan($peraturanTitle,$peraturanSubTitle);
			} else { 
				$url = $config->web->url->base . DS . 'berita' . DS . 'baca' . DS . $row['guid'] . DS . $row['shortTitle'];
			}
				
			$loc = $url;
			
			$pubDate = date('D, d M Y h:i:s O',strtotime($row['publishedDate']));
			
			$keywords = App_Model_Show_CatalogAttribute::show()->getCatalogAttributeValue($row['guid'],'fixedKeywords');
			
			//$item = new Pandamp_Seo_Sitemap_Item($loc, $pubDate);
			
			//Pandamp_Seo_Sitemap::addToSitemap($file, $item);
			
			$output .= "\t<url>" . "\n";
			$output .= "\t\t<loc>" . "\n";
			$output .= "\t\t\t$loc" . "\n";
			$output .= "\t\t</loc>" . "\n";
			$output .= "\t\t<news:news>" . "\n";
			$output .= "\t\t\t<news:publication_date>" . "\n";
			$output .= "\t\t\t\t$pubDate" . "\n";
			$output .= "\t\t\t</news:publication_date>" . "\n";
			
			if ($keywords)
			{
				$output .= "\t\t\t<news:keywords>" . "\n";
				$output .= "\t\t\t\t$keywords" . "\n";
				$output .= "\t\t\t</news:keywords>" . "\n";
			}
			
			$output .= "\t\t</news:news>" . "\n";
			$output .= "\t</url>" . "\n";
		}
		
		$output .= '</urlset>';		
		
		/**
		 * Write to file
		 */
		$f = fopen($file,'w');
		fwrite($f, $output);
		fclose($f);
		
		return true;
	}
}