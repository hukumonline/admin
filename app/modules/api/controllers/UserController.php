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
	public function __getalluserAction()
	{
		$this->_helper->layout()->disableLayout();

		$aColumns = array( 'kopel', 'username', 'company', 'packageId', 'periodeId', 'action' );
		$pColumns = array( 'kopel', 'username', 'company', 'packageId', 'periodeId' );
		            
		$r = $this->getRequest();
		
		$sEcho = ($r->getParam('sEcho'))? $r->getParam('sEcho') : 1;
		$start = ($r->getParam('iDisplayStart'))? $r->getParam('iDisplayStart') : 0;
		$limit = ($r->getParam('iDisplayLength'))? $r->getParam('iDisplayLength'): 0;
		$orderBy = ($r->getParam('orderBy'))? $r->getParam('sortBy') : 'firstname';
		$sortOrder = ($r->getParam('sortOrder'))? $r->getParam('sortOrder') : ' asc';
		
		$sWhere = "";
		if ($r->getParam('sSearch'))
		{
			for ($i=0;$i<count($pColumns);$i++)
			{
				$sWhere .= $pColumns[$i]." LIKE '%".mysql_real_escape_string($r->getParam('sSearch'))."%' OR ";
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
                				$btn .= "<a href=\"javascript:;\" onclick=\"javascript: window.location.href='".ROOT_URL.'/'.$this->_zl->getLanguage().'/customer/user/edit/id/'.$row->kopel."';\">edit</a>&nbsp";
                				$btn .= "<a href=\"$row->kopel\" id=\"delete\">delete</a>&nbsp";
                				$btn .= "<a href=\"$row->kopel\" id=\"reset\">reset</a>";
                			}
                			else 
                			{
                				$btn .= "Edit&nbsp;";
                				$btn .= "Delete&nbsp;";
                				$btn .= "Reset";
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
	public function getalluserAction()
	{
		$this->_helper->layout()->disableLayout();
		//params: $folderGuid,$start,$limit,orderBy
		
		$r = $this->getRequest();
		//$q = ($r->getParam('q'))? base64_decode($r->getParam('q')) : "1=1";
		
		$pColumns = array( 'ku.kopel', 'ku.username', 'ku.company', 'gag.value', 'kus.status' );
		
		$sWhere = "";
		if ($r->getParam('q'))
		{
			$q = base64_decode($r->getParam('q'));
			for ($i=0;$i<count($pColumns);$i++)
			{
				$sWhere .= $pColumns[$i]." LIKE '%".mysql_real_escape_string($q)."%' OR ";
			}
			
			$sWhere = substr_replace($sWhere,"",-3);
			
		}
		else 
		{
			$sWhere = "1=1";
		}
		
		$start = ($r->getParam('start'))? $r->getParam('start') : 0;
		$limit = ($r->getParam('limit'))? $r->getParam('limit'): 0;
		$orderBy = ($r->getParam('orderBy'))? $r->getParam('sortBy') : 'firstname';
		$sortOrder = ($r->getParam('sortOrder'))? $r->getParam('sortOrder') : ' asc';
		
		$a = array();
		
		$obj = new Pandamp_Crypt_Password();
		
		$tblUser = new App_Model_Db_Table_User();
		//echo $q;die();
		//$rowset = $tblUser->fetchAll($sWhere, 'kopel ASC', $limit, $start);
		$rowset = App_Model_Show_User::show()->fetchUser($sWhere, $start, $limit);
		
		if(count($rowset)==0)
		{
			$a['users'][0]['kopel']= 'XXX';
			$a['users'][0]['username']= "No Data";
			$a['users'][0]['company']= "";
			$a['users'][0]['group']= '';
			$a['users'][0]['status']= '';
		}
		else 
		{
			$ii=0;
			foreach ($rowset as $row) 
			{
				$a['users'][$ii]['checkbox'] = "<input type='checkbox' name='kopel[]' id='kopel' value='$row->kopel' class='check_me'>";
				$a['users'][$ii]['kopel']= $row->kopel;
				$a['users'][$ii]['username']= $row->username;
				$a['users'][$ii]['company']= $row->company; 
				//$a['users'][$ii]['group']= Pandamp_Controller_Action_Helper_UserGroup::userGroup($row->packageId);
				$a['users'][$ii]['group']= $row->value;
				//$a['users'][$ii]['status']= Pandamp_Controller_Action_Helper_UserStatus::userStatus($row->periodeId);
				$a['users'][$ii]['status']= $row->status;
				
        		$btn="";
        		$passwd="";
        		$gEx = Pandamp_Controller_Action_Helper_GroupException::groupException(11);
        		if ((in_array($row->username, $gEx)) && (Pandamp_Controller_Action_Helper_UserGroup::userGroup($this->_user->packageId) !== "Master")) { 
					$btn .= '-';            			
					$passwd .= '';					
        		}
        		else 
        		{
        			if (Pandamp_Controller_Action_Helper_IsAllowed::isAllowed('membership','all')) {
        				$btn .= "<a href='".ROOT_URL.'/'.$this->_zl->getLanguage().'/customer/user/edit/id/'.$row->kopel."'>edit</a>&nbsp";
        				$btn .= "<a class=\"deleteAction\" rel=\"$row->kopel\" href=\"javascript: void(0);\">delete</a>&nbsp";
        				$btn .= "<a class=\"resetAction\" rel=\"$row->kopel\" href=\"javascript: void(0);\">reset</a>";
        				
//        				$btn .= "<input type=\"button\" name=\"edit\" value=\"Edit\" onclick=\"javascript: window.location.href='".ROOT_URL.'/'.$this->_zl->getLanguage().'/customer/user/edit/id/'.$row->kopel."'\" class=\"form-button\">&nbsp";
//        				$btn .= "<input type=\"button\" name=\"delete\" value=\"Delete\" id=\"$row->kopel\" class=\"form-button\" />&nbsp";
//        				$btn .= "<input type=\"button\" name=\"reset\" value=\"Reset\" id=\"$row->kopel\" class=\"form-button\" />";
						
						if (Pandamp_Controller_Action_Helper_UserGroup::userGroup($this->_user->packageId) == "Master") {

							if (is_sha1($row->password)) {
								$password = "TYPE: SHA1";
							}
							else 
							{
								$password = $obj->decryptPassword($row->password);	
							}
							
							
							$passwd .= "<tr><td>&nbsp;</td><td colspan='6' style='color:green;'>password:<a href='".ROOT_URL.'/'.$this->_zl->getLanguage().'/customer/user/edit/id/'.$row->kopel."'>".$password."</a></td></tr>";
							
						} else {
							
							$passwd .= "";
							
						}	
						
        			}
        			else 
        			{
        				$btn .= "Edit&nbsp;";
        				$btn .= "Delete&nbsp;";
        				$btn .= "Reset";
        				
        				$passwd .= "+";
        			}
        		}
				
				$a['users'][$ii]['action']= $btn."<br><div id='kopel_$row->kopel'></div>";
				$a['users'][$ii]['passwd']= $passwd;
				$ii++;
			}
		}
		
		echo Zend_Json::encode($a);
		die();
	}
	public function countuserbyqueryAction()
	{
		//$mainQuery = "SELECT count(*) as count from KutuUser where ";
		
		$r = $this->getRequest();
		//$q = ($r->getParam('q'))? $r->getParam('q') : '';
		//$q = base64_decode($q);
		
		$pColumns = array( 'ku.kopel', 'ku.username', 'ku.company', 'gag.value', 'kus.status' );
		
		$sWhere = "";
		if ($r->getParam('q'))
		{
			$q = base64_decode($r->getParam('q'));
			for ($i=0;$i<count($pColumns);$i++)
			{
				$sWhere .= $pColumns[$i]." LIKE '%".mysql_real_escape_string($q)."%' OR ";
			}
			
			$sWhere = substr_replace($sWhere,"",-3);
			
		}
		else 
		{
			$sWhere = "1=1";
		}
		
		//$finalQuery = $mainQuery.$sWhere;
		//$db = Zend_Registry::get('db2');
		//$query = $db->query($finalQuery);
		//$db = Zend_Db_Table::getDefaultAdapter()->query($finalQuery);
		
		//$row = $query->fetchAll(Zend_Db::FETCH_ASSOC);
		//$row = $db->fetchAll(Zend_Db::FETCH_OBJ);
		//echo $row[0]['count'];
		
		$tblUser = new App_Model_Db_Table_User();
		$row = App_Model_Show_User::show()->countUser($sWhere);
		echo $row; 
		die();
	}
	
	public function countuserappbyqueryAction()
	{
		$mainQuery = "SELECT count(*) as count from KutuUser where isActive = 0 AND periodeId IN (1,2)";
		
		$r = $this->getRequest();
		
		$finalQuery = $mainQuery;
		$db = Zend_Registry::get('db2');
		$query = $db->query($finalQuery);
		
		$row = $query->fetchAll(Zend_Db::FETCH_ASSOC);
		echo $row[0]['count'];
		die();
	}
	
	public function getappuserAction()
	{
		$this->_helper->layout()->disableLayout();
		
		$r = $this->getRequest();
		
		$start = ($r->getParam('start'))? $r->getParam('start') : 0;
		$limit = ($r->getParam('limit'))? $r->getParam('limit'): 0;
		$orderBy = ($r->getParam('orderBy'))? $r->getParam('sortBy') : 'firstname';
		$sortOrder = ($r->getParam('sortOrder'))? $r->getParam('sortOrder') : ' asc';
		
		$a = array();
		
		$tblUser = new App_Model_Db_Table_User();
		$rowset = $tblUser->fetchAll("isActive = 0 AND periodeId IN (1,2)", 'kopel DESC', $limit, $start);
		
		if(count($rowset)==0)
		{
			$a['users'][0]['kopel']= 'XXX';
			$a['users'][0]['username']= "No Data";
			$a['users'][0]['company']= "";
			$a['users'][0]['group']= '';
			$a['users'][0]['status']= '';
		}
		else 
		{
			$ii=0;
			foreach ($rowset as $row) 
			{
				$a['users'][$ii]['kopel']= $row->kopel;
				$a['users'][$ii]['username']= $row->username;
				$a['users'][$ii]['company']= $row->company; 
				$a['users'][$ii]['group']= Pandamp_Controller_Action_Helper_UserGroup::userGroup($row->packageId);
				$a['users'][$ii]['status']= Pandamp_Controller_Action_Helper_UserStatus::userStatus($row->periodeId);
				$a['users'][$ii]['checkbox']= "<input type='checkbox' name='kopel[]' id='kopel' value='$row->kopel' class='check_me'>";
				
				$ii++;
			}
		}
		
		echo Zend_Json::encode($a);
		die();
		
	}
	function is_sha1($str) {
	    return (bool) preg_match('/^[0-9a-f]{40}$/i', $str);
	}
}