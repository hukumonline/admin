<?php
/**
 * @author	2011-2018 Nihki Prihadi <nihki@madaniyah.com>
 * @version $Id: GetOthers.php 1 2013-12-06 10:07Z $
 */

class Pandamp_Controller_Action_Helper_GetOthers
{
	public function getOthers($catalogGuid)
	{
		$viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
		if (null === $viewRenderer->view) {
			$viewRenderer->initView();
		}
		$view = $viewRenderer->view;
		
		$dvhg = $view->getHelper('GetHistory');
		$others = $dvhg->getRelatedItem($catalogGuid, 'RELATED_ITEM');
		
		$content = 0;
		$data = array();
		
		foreach ($others as $ot) 
		{
			if ($ot['node'] == $catalogGuid) continue;
			
			$data[$content]['node'] = $ot['node'];
			$data[$content]['title'] = $ot['title'];
			$data[$content]['subTitle'] = $ot['subTitle'];
			$data[$content]['description'] = $ot['description'];
			$data[$content]['fixedDate'] = $ot['fixedDate'];
			
			$content++;
		}
		
		if (count($data) > 0) return $data;
		
		return;
	}
}