<?php
/**
 * @author	2011-2018 Nihki Prihadi
 * @version $Id: CategoryCheckbox.php 1 2013-04-09 18:09Z $
 */

class Pandamp_Controller_Action_Helper_CategoryCheckbox
{
	const EOL = "\n";
	
	public function categoryCheckbox($attributes = array())
	{
		$zl = Zend_Registry::get('Zend_Locale');
		$lang = $zl->getLanguage();
		
		$auth = Zend_Auth::getInstance();
		$group = $auth->getIdentity()->name;
		$group = strtolower(str_replace(" ", "", $group));
		
		$cache = Pandamp_Cache::getInstance();
		
		if (isset($attributes['selected'])) {
			$sel = join("_", $attributes['selected']);
			$cacheKey = 'categoryCheckbox_'.$sel;
		}
		else
		{
			$cacheKey = 'categoryCheckbox';
		}
		
		$cacheKey = $cacheKey.'_'.$lang.'_'.$group;
		
		if ($cache != null) {
			$cache->setLifetime("86400");
		}
		
		if (!($fromCache = $cache->load($cacheKey))) {
			$output = sprintf("<div id='%s' viewHelperClass='%s' viewHelperAttributes='%s'>", $attributes['id'], get_class($this), Zend_Json::encode($attributes)) . self::EOL;
			$output .= $this->_traverseFolder('root','', 0, $attributes);
			$output .= '</div>' . self::EOL;
		
			$cache->save($output, $cacheKey);
			$fromCache = $cache->load($cacheKey);
				
		}
		
		return $fromCache;
	}
	
	/**
	 * Get Tree
	 *
	 * @param string $folderGuid
	 * @param string $sGuid
	 * @param int $level
	 * @return void
	 */
	protected function _traverseFolder($folderGuid, $sGuid, $level, array $attributes)
	{
		$acl = Pandamp_Acl::manager();
		
		$auth = Zend_Auth::getInstance();
		$group = $auth->getIdentity()->name;
		
		$rowSet = App_Model_Show_Folder::show()->fetchChildren($folderGuid);
		$sGuid = '';
		
		foreach($rowSet as $row)
		{
			if (($group == "Master") || ($group == "Super Admin"))
				$content = 'all-access';
			else
				$content = $row['type'];
			
			if ($acl->getPermissionsOnContent('', $group, $content)) {
					
				$selected = (isset($attributes['selected']) && in_array($row['guid'], $attributes['selected'])) ? ' checked="checked"' : '';
				
				$checkBox= '<div>' . str_repeat('-----', $level). ' <input type="checkbox" name="' . $attributes['name'] .'" value="' . $row['guid'] . '"' . $selected. ' />' . $row['title'] . '</div>' . self::EOL;
				
				
				$sGuid .= $checkBox . $this->_traverseFolder($row['guid'], '', $level+1, $attributes);
			}
			
		}
		return $sGuid;
	}
}
