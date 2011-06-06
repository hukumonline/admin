<?php
class Pandamp_Session_SaveHandler_DirectDb extends Zend_Session_SaveHandler_DbTable 
{
	public function __construct($adapter, $dbParams)
    {
		$db = Zend_Db::factory($adapter, $dbParams);
    	
    	$config = array(
		    'name'           => 'KutuSession',
		    'primary'        => 'sessionId',
		    'modifiedColumn' => 'sessionModified',
		    'dataColumn'     => 'sessionData',
		    'lifetimeColumn' => 'sessionLifetime',
    		'db' => $db
		);

        parent::__construct($config);
    }
}
?>