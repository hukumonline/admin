<?php
/**
 * @author	2013-2014 Nihki Prihadi <nihki@madaniyah.com>
 * @version $Id: Item.php 1 2013-01-21 15:15Z $
 */

class Pandamp_Seo_Sitemap_Item
{
	/**
	 * Item URL
	 * 
	 * @var string
	 */
	private $_loc;
	
	/**
	 * Publication date of article
	 * 
	 * @var Date
	 */
	private $_pubDate;
	
	/**
	 * Keywords URL
	 */
	private $_keywords;
	
	//public function __construct($loc, $pubDate, $keywords)
	public function __construct($loc, $pubDate)
	{
		$this->_loc = $loc;
		$this->_pubDate = $pubDate;
		//$this->_keywords = $keywords;
	}
	
	/**
	 * Get item's location
	 * 
	 * @return string
	 */
	public function getLoc()
	{
		return $this->_loc;
	}
	
	/**
	 * Get item publishedDate
	 * 
	 * @return date
	 */
	public function getPubDate()
	{
		return $this->_pubDate;
	}
	
	/**
	 * @return string 
	 */
	public function toString()
	{
		$tab = '    ';
		$endOfLine = PHP_EOL;
		
		$return = $tab . '<url>' . $endOfLine
				. $tab . $tab . '<loc>' . $this->_loc . '</loc>' . $endOfLine
				. $tab . $tab . '<news:news>' . $endOfLine
				. $tab . $tab . $tab . '<news:publication_date>' . $this->_pubDate . '</news:publication_date>' . $endOfLine
				. $tab . $tab . '</news:news>' . $endOfLine;
		
		$return .= $tab . '</url>' . $endOfLine;
		
		return $return;
	}
}