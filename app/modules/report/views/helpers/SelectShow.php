<?php
/**
 * @author	2012-2013 Nihki Prihadi
 * @version $Id: SelectShow.php 1 2012-09-27 16:15Z $
 */

class Report_View_Helper_SelectShow
{
	const EOL = "\n";
	
	public function selectShow($attributes = array())
	{
		$selectedId = isset($attributes['selected']) ? $attributes['selected'] : null;
		$disableId  = isset($attributes['disable']) ? $attributes['disable'] : null;
		
		$output = "<select onchange='javascript:document.report.submit();' class='$attributes[class]' name='$attributes[name]' id='$attributes[id]'>" . self::EOL;
		
		$show = array('Table','Graph');
		for ($i=0;$i<2;$i++) {
			$selected = ($selectedId == null || $selectedId != $i) ? '' : ' selected="selected"';
			$disable  = ($disableId == null || $disableId != $i) ? '' : ' disabled';
			$output  .= sprintf('<option value="%s"%s%s>%s</option>', $i, $selected, $disable, $show[$i]) . self::EOL;
		}
		
		$output .= '</select>' . self::EOL;
		
		return $output;
	}
}