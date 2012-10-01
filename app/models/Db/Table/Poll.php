<?php

/**
 * Description of PaymentConfirmation
 *
 * @author nihki <nihki@madaniyah.com>
 */
class App_Model_Db_Table_Poll extends Zend_Db_Table_Abstract
{
    protected $_name = 'polls';
    protected $_rowClass = 'App_Model_Db_Table_Row_Poll';
    
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
