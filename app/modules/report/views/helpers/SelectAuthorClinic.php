<?php
/**
 * @author	2012-2013 Nihki Prihadi
 * @version $Id: SelectAuthorClinic.php 1 2013-01-07 11:35Z $
 */

class Report_View_Helper_SelectAuthorClinic
{
	const EOL = "\n";
	
	public function selectAuthorClinic($attributes = array())
	{
		$selectedId = isset($attributes['selected']) ? $attributes['selected'] : null;
		$disableId  = isset($attributes['disable']) ? $attributes['disable'] : null;
		
		$output = "<select onchange='javascript:document.peraturan.submit();' class='$attributes[class]' name='$attributes[name]' id='$attributes[id]'>" . self::EOL
				. '<option value="">All</option>' . self::EOL;
		
		$ie = Pandamp_Search::manager();
				
		$query = "profile:author";
		$hits = $ie->find($query);
		$solrNumFound 	= count($hits->response->docs);
		
		for ($i=0;$i<$solrNumFound;$i++)
		{
			$row = $hits->response->docs[$i];
			$selected = ($selectedId == null || $selectedId != $row->id) ? '' : ' selected="selected"';
			$disable  = ($disableId == null || $disableId != $row->id) ? '' : ' disabled'; 
			$output  .= sprintf('<option value="%s"%s%s>%s</option>', $row->id, $selected, $disable, $row->title) . self::EOL;
		}
		
		$output .= '</select>' . self::EOL;
		
		return $output;
	}
}
