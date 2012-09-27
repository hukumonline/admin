<?php
/**
 * @author	2011-2012 Nihki Prihadi
 * @version $Id: HelperLoader.php 1 2012-01-06 15:13Z $
 */

class Pandamp_Controller_Action_Helper_HelperLoader extends Zend_View_Helper_Abstract
{
	/**
	 * Add helper path
	 * 
	 * @param string $module Module name
	 * @return Zend_View_Abstract The view instance
	 */
	public function helperLoader($module)
	{
		$module = strtolower($module);
		$this->view->addHelperPath(ROOT_DIR . DS . 'modules' . DS . $module . DS . 'views' . DS . 'helpers', ucfirst($module) . '_View_Helper_');
		$this->view->addScriptPath(ROOT_DIR . DS . 'modules' . DS . $module . DS . 'views' . DS . 'scripts');
		return $this->view;
	}
}
