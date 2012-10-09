<?php
/**
 * @author	2012-2013 Nihki Prihadi
 * @version $Id: SelectBy.php 1 2012-10-09 17:15Z $
 */

class Report_View_Helper_SelectBy
{
	const EOL = "\n";
	
	public function selectBy($attributes = array())
	{
		$selectedId = isset($attributes['selected']) ? $attributes['selected'] : null;
		$disableId  = isset($attributes['disable']) ? $attributes['disable'] : null;
		
		$output = "<select onchange='javascript:document.peraturan.submit();' class='$attributes[class]' name='$attributes[name]' id='$attributes[id]'>" . self::EOL
		. '<option value="">All</option>' . self::EOL;
		
		$rowset = App_Model_Show_Catalog::show()->getCatalogBy($attributes['profile']);
		
		foreach ($rowset as $v) {
			if (empty($v['createdBy'])) continue;
			$selected = ($selectedId == null || $selectedId != $v['createdBy']) ? '' : ' selected="selected"';
			$disable  = ($disableId == null || $disableId != $v['createdBy']) ? '' : ' disabled';
			$output  .= sprintf('<option value="%s"%s%s>%s</option>', $v['createdBy'], $selected, $disable, $v['createdBy']) . self::EOL;
		}
		
		$output .= '</select>' . self::EOL;
		
		return $output;
	}
}