<?php

// http://code.google.com/p/jquery-datatables-editable/
// http://jquery-datatables-editable.googlecode.com/svn/trunk/delete-record.html

class Api_UserController extends Zend_Controller_Action
{
	public function getalluserAction()
	{
		$this->_helper->layout()->disableLayout();
		
		$r = $this->getRequest();
		$q = ($r->getParam('q'))? base64_decode($r->getParam('q')) : "1=1";
		
		$start = ($r->getParam('iDisplayStart'))? $r->getParam('iDisplayStart') : 0;
		$limit = ($r->getParam('iDisplayLength'))? $r->getParam('iDisplayLength'): 0;
		$orderBy = ($r->getParam('orderBy'))? $r->getParam('sortBy') : 'firstname';
		$sortOrder = ($r->getParam('sortOrder'))? $r->getParam('sortOrder') : ' asc';
		
		$tblUser = new App_Model_Db_Table_User();
		//echo $q;die();
		$rowset = $tblUser->fetchAll($q, 'kopel ASC', $limit, $start);
		$rowset1 = $tblUser->fetchAll($q, 'kopel ASC');

                $nr = count($rowset1);

		$a = array(
                    'sEcho'=>1,
                    'iTotalRecords'=>$nr,
                    'iTotalDisplayRecords'=>$nr,
                    "aaData" => array()
                );

		if(count($rowset)==0)
		{
			$a['aaData'][0]['guid']= 'XXX';
			$a['aaData'][0]['title']= "No Data";
			$a['aaData'][0]['subTitle']= "";
			$a['aaData'][0]['createdDate']= '';
			$a['aaData'][0]['modifiedDate']= '';
		}
		else 
		{
                        $aColumns = array( 'kopel', 'username', 'company', 'createdDate', 'modifiedDate' );
			$ii=0;
                        
			foreach ($rowset as $row) 
			{
                            $b = array();
                            for ( $i=0 ; $i<count($aColumns) ; $i++ )
                            {
				$b[]= $row[ $aColumns[$i] ];
                            }

                            $a['aaData'][] = $b;
				
			}
		}
		
		echo Zend_Json::encode($a);
		die();
	}
	public function countuserbyqueryAction()
	{
		$mainQuery = "SELECT count(*) as count from KutuUser where ";
		
		$r = $this->getRequest();
		$q = ($r->getParam('q'))? $r->getParam('q') : '';
		$q = base64_decode($q);
		
		$finalQuery = $mainQuery.$q;
		$db = Zend_Registry::get('db2');
		$query = $db->query($finalQuery);
		//$db = Zend_Db_Table::getDefaultAdapter()->query($finalQuery);
		
		$row = $query->fetchAll(Zend_Db::FETCH_ASSOC);
		//$row = $db->fetchAll(Zend_Db::FETCH_OBJ);
		echo $row[0]['count'];
		die();
	}	
}