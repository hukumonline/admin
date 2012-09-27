<?php
/**
 * @author	2011-2012 Nihki Prihadi
 * @version $Id: SelectProfile.php 1 2012-01-26 19:25Z $
 */

class Report_View_Helper_SelectProfile
{
	const EOL = "\n";
	
	public function selectProfile($attributes = array())
	{
		$selectedId = isset($attributes['selected']) ? $attributes['selected'] : null;
		$disableId  = isset($attributes['disable']) ? $attributes['disable'] : null;
		
		$output = "<select class='$attributes[class]' name='$attributes[name]' id='$attributes[id]'>" . self::EOL
				. '<option value="">---</option>' . self::EOL;
		
		$profile = App_Model_Show_Catalog::show()->getProfile();
		
		foreach ($profile as $p) {
			$selected = ($selectedId == null || $selectedId != $p['guid']) ? '' : ' selected="selected"';
			$disable  = ($disableId == null || $disableId != $p['guid']) ? '' : ' disabled';
			$output  .= sprintf('<option value="%s"%s%s>%s</option>', $p['guid'], $selected, $disable, $p['guid']) . self::EOL;
		}
		
		$output .= '</select>' . self::EOL;
		
		return $output;
	}
}