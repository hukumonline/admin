<?php

/**
 * Description of PollIp
 *
 * @author nihki <nihki@madaniyah.com>
 */
class App_Model_Db_Table_PollIp extends Zend_Db_Table_Abstract
{
    protected $_name = 'pollsIp';
    
	public function insert (array $data)
	{
		if (empty($data['guid']))
		{
			$guidMan = new Pandamp_Core_Guid;
			$data['guid'] = $guidMan->generateGuid();
		}
		
		return parent::insert($data);
		
	}
}
