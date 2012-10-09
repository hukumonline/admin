<?php
/**
 * @author	2012-2013 Nihki Prihadi
 * @version $Id: SelectSorting.php 1 2012-10-04 19:53Z $
 */

class Report_View_Helper_SelectSorting
{
	const EOL = "\n";
	
	public function selectSorting($attributes = array())
	{
		$selectedId = isset($attributes['selected']) ? $attributes['selected'] : null;
		$disableId  = isset($attributes['disable']) ? $attributes['disable'] : null;
		
		$output = "<select onchange='javascript:document.peraturan.submit();' class='$attributes[class]' name='$attributes[name]' id='$attributes[id]'>" . self::EOL;
		
		$show 	= array('All','Year A to Z','Year Z TO A','Number A to Z','Number Z to A','Fixed Date A to Z','Fixed Date Z to A','Regulation Type A to Z','Regulation Type Z to A','Created Date A to Z','Created Date Z to A');
		$v 		= array('','year asc','year desc','number asc','number desc','date asc','date desc','regulationOrder asc','regulationOrder desc','createdDate asc','createdDate desc');
		
		for ($i=0;$i<11;$i++) {
			$selected = ($selectedId == null || $selectedId != $v[$i]) ? '' : ' selected="selected"';
			$disable  = ($disableId == null || $disableId != $v[$i]) ? '' : ' disabled';
			$output  .= sprintf('<option value="%s"%s%s>%s</option>', $v[$i], $selected, $disable, $show[$i]) . self::EOL;
		}
		
		$output .= '</select>' . self::EOL;
		
		return $output;
	}
}