<?php

/**
 * Description of User
 *
 * @author nihki <nihki@madaniyah.com>
 */
class App_Model_Db_Table_Invoice extends Zend_Db_Table_Abstract
{
    protected $_name = 'KutuUserInvoice';
    protected $_schema = 'hid';
    protected $_referenceMap = array(
        'User' => array(
            'columns'       => array('uid'),
            'refTableClass' => array('App_Model_Db_Table_User'),
            'refColumns'    => array('kopel')
        )
    );
    protected function  _setupDatabaseAdapter()
    {
        $this->_db = Zend_Registry::get('db2');

        parent::_setupDatabaseAdapter();
    }
    
    public function fetchExpireInvoice()
    {
    	$select = $this->select()
	   			    ->from($this)
    				->where("expirationDate between date(NOW() + INTERVAL 1 DAY) and date(NOW() + INTERVAL 7 DAY)")
    				->order('expirationDate DESC');
    			  
    	/*$sql = $select->__toString();
    	print_r($sql);exit();*/
    	
    	$rows = $this->fetchAll($select);
    	
    	return $rows;
    }
    
    public function getCountExpireInvoice($period)
    {
    	$select = $this->select()
	   			    ->from($this, array(
                        'COUNT(*) as num'
                    ))
    				->where("expirationDate between date(NOW() + INTERVAL 1 DAY) and date(NOW() + INTERVAL ".$period." DAY)");
    			  
    	$row = $this->fetchRow($select);
    	
    	return ($row !== null) ? $row['num'] : 0;
    }
    
    public function fetchUserExpireInvoice($period)
    {
    	$queryString = "SELECT KutuUser . * , KutuUserInvoice . * , gacl_aro_groups.name AS 'PackageName', KutuUserStatus.status
		FROM KutuUser
		LEFT JOIN KutuUserInvoice ON ( KutuUser.kopel = KutuUserInvoice.uid )
		LEFT JOIN gacl_aro_groups ON ( KutuUser.packageId = gacl_aro_groups.id )
		LEFT JOIN KutuUserStatus ON ( KutuUser.periodeId = KutuUserStatus.accountStatusId )
		WHERE KutuUserInvoice.expirationDate
		BETWEEN date( CURRENT_DATE( ) + INTERVAL 0
		DAY )
		AND date( CURRENT_DATE( ) + INTERVAL ".$period." 
		DAY )
		ORDER BY KutuUserInvoice.expirationDate";
    	
	    $db = $this->_db->query($queryString);
	    $dataFetch = $db->fetchAll(Zend_Db::FETCH_ASSOC);
	    
    	$data  = array(
            'table'    => $this,
            'data'     => $dataFetch,
            'rowClass' => $this->_rowClass,
            'stored'   => true
        );

        Zend_Loader::loadClass($this->_rowsetClass);
        
	    if (count($dataFetch) < 1)
	    { 
	    	return false;
	    }
	    else {
	    	return new $this->_rowsetClass($data);
	    }
    }
}
