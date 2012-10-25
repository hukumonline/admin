<?php
/**
 * @author	2011-2012 Nihki Prihadi
 * @version $Id: SelectAuthor.php 1 2012-10-10 20:24Z $
 */

class Report_View_Helper_SelectAuthor
{
	const EOL = "\n";
	
	public function selectAuthor($attributes = array())
	{
		$selectedId = isset($attributes['selected']) ? $attributes['selected'] : null;
		$disableId  = isset($attributes['disable']) ? $attributes['disable'] : null;
		
		$config = Pandamp_Config::getConfig();
		
		$cache = Pandamp_Cache::getInstance();
		
		$output = "<select onchange='javascript:document.peraturan.submit();' class='$attributes[class]' name='$attributes[name]' id='$attributes[id]'>" . self::EOL
				. '<option value="">All</option>' . self::EOL;
		
		$author = App_Model_Show_Catalog::show()->getAuthor($attributes['profile']);
		
		for ($i=0;$i<count($author);$i++) {
			if (empty($author[$i]['author'])) continue;
			
			if (isset($author[$i]['author'])) {
			$selected = ($selectedId == null || $selectedId != $author[$i]['author']) ? '' : ' selected="selected"';
			$disable  = ($disableId == null || $disableId != $author[$i]['author']) ? '' : ' disabled';
			$output  .= sprintf('<option value="%s"%s%s>%s</option>', $author[$i]['author'], $selected, $disable, $author[$i]['author']) . self::EOL;
			}
			
		}
		
		$output .= '</select>' . self::EOL;
		
		return $output;
	}
}