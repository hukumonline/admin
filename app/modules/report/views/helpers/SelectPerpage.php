<?php
/**
 * @author	2012-2013 Nihki Prihadi
 * @version $Id: SelectPerpage.php 1 2012-10-08 17:28Z $
 */

class Report_View_Helper_SelectPerpage
{
	const EOL = "\n";
	
	public function selectPerpage($attributes = array())
	{
		$selectedId = isset($attributes['selected']) ? $attributes['selected'] : null;
		$disableId  = isset($attributes['disable']) ? $attributes['disable'] : null;
		
		$output = "<select onchange='javascript:document.peraturan.submit();' class='$attributes[class]' name='$attributes[name]' id='$attributes[id]'>" . self::EOL;
		
		if ($attributes['totalnum'] < 10) {
			$show = array('10');
			$x = 1;
		}
		else if ($attributes['totalnum'] < 25) {
			$show = array('10','25');
			$x = 2;
		}
		else if ($attributes['totalnum'] < 50) {
			$show = array('10','25','50');
			$x = 3;
		}
		else if ($attributes['totalnum'] < 100) {
			$show = array('10','25','50','100');
			$x = 4;
		}
		else if ($attributes['totalnum'] < 250) {
			$show = array('10','25','50','100','250');
			$x = 5;
		}
		else {
			$show = array('10','25','50','100','250','500');
			$x = 6;
		}
		
		for ($i=0;$i<$x;$i++) {
			$selected = ($selectedId == null || $selectedId != $show[$i]) ? '' : ' selected="selected"';
			$disable  = ($disableId == null || $disableId != $show[$i]) ? '' : ' disabled';
			$output  .= sprintf('<option value="%s"%s%s>%s</option>', $show[$i], $selected, $disable, $show[$i]) . self::EOL;
		}
		
		$output .= '</select>' . self::EOL;
		
		return $output;
	}
}