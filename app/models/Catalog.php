<?php
/**
 * @author	2011-2012 Nihki Prihadi
 * @version $Id: Catalog.php 1 2012-01-05 18:03Z $
 */

/**
 * Represents a catalog
 */
class App_Model_Catalog extends Pandamp_Model_Entity 
{
	protected $_properties = array(
		'guid'			=> null,			/** Id catalog */
		'shortTitle'	=> null,			/** shortTitle that represent catalog */
		'profileGuid'	=> null,			/** profile Catalog */
		'publishedDate'	=> null,			/** catalog published date */
		'expiredDate'	=> null,			/** catalog expired date */
		'createdDate'	=> null,			/** created date catalog */
		'modifiedDate'	=> null,			/** modified date catalog */
		'deletedBy'		=> null,			/** deleted by catalog */
		'deletedDate'	=> null,			/** deleted date of catalog */
		'createdBy'		=> null,			/** created by catalog */
		'modifiedBy'	=> null,			/** modified by catalog */
		'price'			=> 0,				/** price catalog */
		'status'		=> 0,				/** status catalog */
	);
	
	public function getProperties()
	{
		$pros = $this->_properties;
		
		/**
		 * Allow user to use {year}, {month}, {day} in article URL
		 */
		$date = $this->_properties['publishedDate'];
		if (null == $date) {
			$pros['year']  = date('Y');
			$pros['month'] = date('m');
			$pros['day']   = date('d');
		} else {
			$timestamp 	   = strtotime($date);
			$pros['year']  = date('Y', $timestamp);
			$pros['month'] = date('m', $timestamp);
			$pros['day']   = date('d', $timestamp);
		}
		
		return $pros;
	}
}
