<?php
/**
 * @author	2011-2018 Nihki Prihadi <nihki@madaniyah.com>
 * @version $Id: CatalogStatus.php 1 2013-11-25 14:58Z $
 */

class Pandamp_Controller_Action_Helper_CatalogStatus
{
	const EOL = "\n";
	
	public function catalogStatus($attributes = array())
	{
		$selectedId = isset($attributes['selected']) ? $attributes['selected'] : null;
		
		$output = "<select class='$attributes[class]' name='$attributes[name]' id='$attributes[id]' onchange='this.form.submit()'>" . self::EOL
				. '<option value="">---</option>' . self::EOL;
		
		require_once CONFIG_PATH.'/status.php';
		$status = MasterStatus::getPublishingStatus();
				
		foreach ($status as $val => $label) {
			$selected = ($selectedId == null || $selectedId != $val) ? '' : ' selected="selected"';
			$output .= sprintf('<option value="%s"%s>%s</option>',$val,$selected,$label) . self::EOL;
		}
		
		$output .= '</select>' . self::EOL;
		
		return $output;
	}
}
