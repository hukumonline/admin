<?php
class Api_UserController extends Zend_Controller_Action
{
	public function getalluserAction()
	{
		$this->_helper->layout()->disableLayout();
		
		$r = $this->getRequest();
		$q = ($r->getParam('q'))? base64_decode($r->getParam('q')) : "1=1";
		
		$start = ($r->getParam('start'))? $r->getParam('start') : 0;
		$limit = ($r->getParam('limit'))? $r->getParam('limit'): 0;
		$orderBy = ($r->getParam('orderBy'))? $r->getParam('sortBy') : 'firstname';
		$sortOrder = ($r->getParam('sortOrder'))? $r->getParam('sortOrder') : ' asc';
		
		$a = array();
		
		$tblUser = new App_Model_Db_Table_User();
		//echo $q;die();
		$rowset = $tblUser->fetchAll($q, 'fullName ASC', $limit, $start);
		
		if(count($rowset)==0)
		{
			$a['catalogs'][0]['guid']= 'XXX';
			$a['catalogs'][0]['title']= "No Data";
			$a['catalogs'][0]['subTitle']= "";
			$a['catalogs'][0]['createdDate']= '';
			$a['catalogs'][0]['modifiedDate']= '';
		}
		else 
		{
			$ii=0;
			foreach ($rowset as $row) 
			{
				$a['catalogs'][$ii]['kopel']= $row->kopel;
				$a['catalogs'][$ii]['title']= $row->fullName;
				$a['catalogs'][$ii]['subTitle']= $row->username; 
				$a['catalogs'][$ii]['createdDate']= $row->createdDate;
				$a['catalogs'][$ii]['modifiedDate']= $row->modifiedDate;
				$ii++;
			}
		}
		
		echo Zend_Json::encode($a);
		die();
	}
	
}