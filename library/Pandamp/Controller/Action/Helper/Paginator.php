<?php
/**
 * @author	2011-2012 Nihki Prihadi
 * @version $Id: Paginator.php 1 2012-01-05 13:55Z $
 */

class Pandamp_Controller_Action_Helper_Paginator extends Zend_View_Helper_Abstract 
{
	/**
	 * Get this view helper instance
	 * 
	 * @return Core_View_Helper_Paginator
	 */
	public function paginator()
	{
		return $this;
	}
	
	/**
	 * Show slide paginator
	 * 
	 * @param Zend_Paginator $paginator
	 * @param array $options
	 * @return string
	 */
	public function slide($paginator, $options = array())
	{
		/**
		 * Don't show paginator if there's only one page
		 */
		if ($paginator['pageCount'] == 1) {
			return '';
		}
		
		$this->view->addScriptPath(APPLICATION_PATH . DS . 'modules' . DS . 'core' . DS . 'views' . DS . 'scripts');
		
		$this->view->assign('paginator', $paginator);
		$this->view->assign('paginatorOptions', $options);
		
		return $this->view->render('_partial' . DS . '_pagination.phtml');
	}
	
	/**
	 * Generate link to item
	 * 
	 * @param int $pageIndex Page index of item
	 * @param string $label Label of link
	 * @param array $options Array consist of two options:
	 * - path
	 * - itemLink 
	 * @return string
	 */
	public function buildLink($pageIndex, $label, $options = array())
	{
		$url = $options['path'];
		$str = str_replace('%d', $pageIndex, $options['itemLink']);
		
		/**
		 * 10 is length of "javascript" (without ")
		 */
		if (0 == strncasecmp($options['itemLink'], 'javascript', 10)) {
			$url = $str;
		} else {
			$url = rtrim($url, '/') . '/' . $str;
		}
		return sprintf('<a href="%s">%s</a>', $url, $label);
	}
}
