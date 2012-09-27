<?php
/**
 * @author	2012-2013 Nihki Prihadi
 * @version $Id: SelectYear.php 1 2012-09-26 18:12Z $
 */

class Report_View_Helper_SelectYear
{
	const EOL = "\n";
	
	public function selectYear($attributes = array())
	{
		$selectedId = isset($attributes['selected']) ? $attributes['selected'] : null;
		$disableId  = isset($attributes['disable']) ? $attributes['disable'] : null;
		
		$output = "<select onchange='javascript:document.report.submit();' class='$attributes[class]' name='$attributes[name]' id='$attributes[id]'>" . self::EOL
				. '<option value="">---</option>' . self::EOL;
		
		$year = App_Model_Show_Catalog::show()->getYear();
		
		foreach ($year as $p) {
			$selected = ($selectedId == null || $selectedId != $p['cd']) ? '' : ' selected="selected"';
			$disable  = ($disableId == null || $disableId != $p['cd']) ? '' : ' disabled';
			$output  .= sprintf('<option value="%s"%s%s>%s</option>', $p['cd'], $selected, $disable, $p['cd']) . self::EOL;
		}
		
		$output .= '</select>' . self::EOL;
		
		return $output;
	}
}