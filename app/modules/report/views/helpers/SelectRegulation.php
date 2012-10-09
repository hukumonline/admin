<?php
/**
 * @author	2012-2013 Nihki Prihadi
 * @version $Id: SelectRegulation.php 1 2012-10-09 12:30Z $
 */

class Report_View_Helper_SelectRegulation
{
	const EOL = "\n";
	
	public function selectRegulation($attributes = array())
	{
		$selectedId = isset($attributes['selected']) ? $attributes['selected'] : null;
		$disableId  = isset($attributes['disable']) ? $attributes['disable'] : null;
		
		$output = "<select onchange='javascript:document.peraturan.submit();' class='$attributes[class]' name='$attributes[name]' id='$attributes[id]'>" . self::EOL
		. '<option value="">All</option>' . self::EOL;
		
		$tblProAtt 	= new App_Model_Db_Table_ProfileAttribute();
		$rowset 	= $tblProAtt->fetchAll("profileGuid='$attributes[profile]' AND attributeGuid='$attributes[prtJenis]'");
		
		$defaultValues = array();
		
		$row = $rowset->current();
		$defaultValues =  Zend_Json::decode($row->defaultValues);
		
		foreach ($defaultValues as $v) {
			$selected = ($selectedId == null || $selectedId != $v['value']) ? '' : ' selected="selected"';
			$disable  = ($disableId == null || $disableId != $v['value']) ? '' : ' disabled';
			$output  .= sprintf('<option value="%s"%s%s>%s</option>', $v['value'], $selected, $disable, $v['label']) . self::EOL;
		}
		
		$output .= '</select>' . self::EOL;
		
		return $output;
	}
}