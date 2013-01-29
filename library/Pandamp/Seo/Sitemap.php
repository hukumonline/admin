<?php
/**
 * @author	2013-2014 Nihki Prihadi <nihki@madaniyah.com>
 * @version $Id: Sitemap.php 1 2013-01-21 15:03Z $
 */

class Pandamp_Seo_Sitemap
{
	/**
	 * Get sitemap items from file
	 *
	 * @param string $file File Name
	 */
	public static function getItems($file)
	{
		$items = array();
		if (file_exists($file)) {
			$xml = simplexml_load_file($file);
			Pandamp_Debug::manager($xml);
			foreach ($xml->url as $url) {	
				$items[] = new Pandamp_Seo_Sitemap_Item(
					(string)$url->loc,
					(string)$url->pubDate
				);
			}
		}
		
		return $items;
	}
	
	/**
	 * Add sitemap item to file
	 */
	public static function addToSitemap($file, $item)
	{
		$items = self::getItems($file);
		$items[] = $item;
		
		return self::save($file, $items);
	}
	
	/**
	 * Remove sitemap item from file
	 */
	public static function removeFromSitemap($file, $item)
	{
		$items = self::getItems($file);
		$found = false;
		foreach ($items as $index => $value) {
			if ($value['loc'] = $item->getLoc()) {
				$found = true;
				unset($items[$index]);
			}
		}
		
		if ($found) {
			self::save($file, $items);
		}
		
		return true;
	}
	
	
	/**
	 * Save sitemap to file
	 */
	public static function save($file,$items)
	{
		$output = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL
				. '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">' . PHP_EOL;
		
		foreach ($items as $item)
		{
			$output .= $item->toString();
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