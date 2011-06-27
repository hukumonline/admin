<?php

// http://code.google.com/p/jquery-datatables-editable/
// http://jquery-datatables-editable.googlecode.com/svn/trunk/delete-record.html

class Api_UserController extends Zend_Controller_Action
{
    protected $_user;
    protected $_zl;
    
    function  preDispatch()
    {
        $auth = Zend_Auth::getInstance();

        $this->_zl = Zend_Registry::get("Zend_Locale");
        
        if ($auth->hasIdentity()) {
            $this->_user = $auth->getIdentity();
        }
    }
	public function getalluserAction()
	{
		$this->_helper->layout()->disableLayout();

		$aColumns = array( 'kopel', 'username', 'company', 'packageId', 'periodeId', 'action' );
		            
		$r = $this->getRequest();
		
		$sEcho = ($r->getParam('sEcho'))? $r->getParam('sEcho') : 1;
		$start = ($r->getParam('iDisplayStart'))? $r->getParam('iDisplayStart') : 0;
		$limit = ($r->getParam('iDisplayLength'))? $r->getParam('iDisplayLength'): 0;
		$orderBy = ($r->getParam('orderBy'))? $r->getParam('sortBy') : 'firstname';
		$sortOrder = ($r->getParam('sortOrder'))? $r->getParam('sortOrder') : ' asc';
		
		$sWhere = "";
		if ($r->getParam('sSearch'))
		{
			for ($i=0;$i<count($aColumns);$i++)
			{
				$sWhere .= $aColumns[$i]." LIKE '%".mysql_real_escape_string($r->getParam('sSearch'))."%' OR ";
			}
			
			$sWhere = substr_replace($sWhere,"",-3);
			
		}
		else 
		{
			$sWhere = "1=1";
		}
		
		$tblUser = new App_Model_Db_Table_User();
		
		$rowset = $tblUser->fetchAll($sWhere, 'kopel ASC', $limit, $start);
		$rowset1 = $tblUser->fetchAll($sWhere);

       	$nr = count($rowset1);

		$a = array(
                    'sEcho'=>$sEcho,
                    'iTotalRecords'=>$nr,
                    'iTotalDisplayRecords'=>$nr,
                    "aaData" => array()
                );

		if($nr==0)
		{
			
		}
		else 
		{
                        
			foreach ($rowset as $row) 
			{
                $b = array();
                for ( $i=0 ; $i<count($aColumns) ; $i++ )
                {
                	if ($aColumns[$i] == 'packageId')
                	{
                		$b[] = Pandamp_Controller_Action_Helper_UserGroup::userGroup($row[ $aColumns[$i] ]);
                	}
                	elseif ($aColumns[$i] == 'periodeId')
                	{
                		$b[] = Pandamp_Controller_Action_Helper_UserStatus::userStatus($row[ $aColumns[$i] ]);
                	}
                	elseif ($aColumns[$i] == 'action')
                	{
                		$btn="";
                		$gEx = Pandamp_Controller_Action_Helper_GroupException::groupException(11);
                		if ((in_array($row->username, $gEx)) && (Pandamp_Controller_Action_Helper_UserGroup::userGroup($this->_user->packageId) !== "Master")) { 
							$btn .= '-';            			
                		}
                		else 
                		{
                			if (Pandamp_Controller_Action_Helper_IsAllowed::isAllowed('membership','all')) {
                				$btn .= "<input type=\"button\" name=\"edit\" value=\"Edit\" onclick=\"javascript: window.location.href='".ROOT_URL.'/'.$this->_zl->getLanguage().'/customer/user/edit/id/'.$row->kopel."';\" class=\"form-button\" />&nbsp";
                				$btn .= "<input type=\"button\" name=\"delete\" value=\"Delete\" id=\"$row->kopel\" class=\"form-button\" />&nbsp";
                				$btn .= "<input type=\"button\" name=\"reset\" value=\"Reset\" id=\"$row->kopel\" class=\"form-button\" />";
                			}
                			else 
                			{
                				$btn .= "<input type=\"button\" name=\"edit\" value=\"Edit\" disabled class=\"form-button\" />&nbsp;";
                				$btn .= "<input type=\"button\" name=\"delete\" value=\"Delete\" disabled class=\"form-button\" />&nbsp;";
                				$btn .= "<input type=\"button\" name=\"reset\" value=\"Reset\" disabled class=\"form-button\" />";
                			}
                		}
                		
                		$b[] = $btn;
                	}
                	else 
                	{
						$b[]= $row[ $aColumns[$i] ];
                	}
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