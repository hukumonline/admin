<?php
/**
 * manage Form Template Attribute for application
 * 
 * @author Himawan Anindya Putra <putra@langit.biz>
 * @author Nihki Prihadi <nihki@madaniyah.com>
 * @package Kutu
 * 
 */

class Pandamp_Form_Attribute_Renderer
{
	var $tblGuid;
	var $name;
	var $value;
	var $type;
	var $attribs;
	var $profileGuid;
	var $other;
	
	public function __construct($attributeGuid=null, $value=null, $type=null, $attribs=null, $profileGuid=null, $desc=null, $other=null)
	{
		$this->name = $attributeGuid;
		$this->value = $value;
		$this->type = $type;
		$this->attribs = $attribs;
		$this->profileGuid = $profileGuid;
		$this->label = $desc;
		$this->other = $other;
	}
	
	public function render()
	{
		$sReturn = '';
		
		switch($this->type) 
		{
			default:
	        case 0:     // field type = single line
	        	$view = new Zend_View();
				$view->name = $this->name;
				$view->value = $this->value;
				if(isset($this->attribs))
					$view->attribs = $this->attribs;
				else
					$view->attribs = array('rows' => 1, 'cols' =>50);
				$view->setScriptPath(dirname(__FILE__));
				return $view->render('TextArea.phtml');
	            break;
	
	        case 1:     // field type = textarea paragraph
	        	$view = new Zend_View();
	        	$view->label = $this->label;
				$view->name = $this->name;
				$view->value = $this->value;
				if(isset($this->attribs))
					$view->attribs = $this->attribs;
				else
					$view->attribs = array('rows' => 5, 'cols' =>50, 'class' => 'form-control');
				$view->setScriptPath(dirname(__FILE__));
				return $view->render('TextArea.phtml');
	            
	            break;
	
	        case 2:     // field type = html paragraph
//				require_once('FCKeditor/fckeditor.php');
//				$oFCKeditor = new FCKeditor($this->name) ;
//				$oFCKeditor->BasePath = ROOT_URL.'/library/FCKeditor/';
//				$oFCKeditor->Value = $this->value;
//				$oFCKeditor->Width  = '100%' ;
//				$oFCKeditor->Height = '400' ;

				
	
//				$sReturn = $oFCKeditor->CreateHtml() ;
	            
//	            return $sReturn;

				$view = new Zend_View();
				$view->label = $this->label;
				$view->name = $this->name;
				$view->value = $this->value;
				$view->setScriptPath(dirname(__FILE__));
				return $view->render('TextArea2.phtml');

	            break;
	        
//	        	$view = new Zend_View();
//				$view->name = $this->name;
//				$view->value = $this->value;
//				
//				$config = Pandamp_Config::getConfig();
//				$view->cdn = $config->cdn->js;
//				if(isset($this->attribs))
//					$view->attribs = $this->attribs;
//				else
//					$view->attribs = array('class' => 'tinymce', 'style' => 'width: 440px; height: 1000px;');
//				$view->setScriptPath(dirname(__FILE__));
//				return $view->render('TextAreaContent.phtml');
//				
//				break;
				
	        case 79:     // field type = html paragraph
				
	        	
				require_once('FCKeditor/fckeditor.php');
				$oFCKeditor = new FCKeditor($this->name) ;
				$oFCKeditor->BasePath = ROOT_URL.'/library/FCKeditor/';
				$oFCKeditor->Value = $this->value;
				$oFCKeditor->Width  = '100%' ;
				$oFCKeditor->Height = '400' ;

				
	
				$sReturn = $oFCKeditor->CreateHtml() ;
	            
	            return $sReturn;
	            
	        	
//	        	$view = new Zend_View();
//				$view->name = $this->name;
//				$view->value = $this->value;
//				if(isset($this->attribs))
//					$view->attribs = $this->attribs;
//				else
//					$view->attribs = array('class' => 'tinymce', 'style' => 'width: 440px; height: 200px;');
//				$view->setScriptPath(dirname(__FILE__));
//				return $view->render('TextAreaDescription.phtml');
				
	        	

	            break;
	            
	        case 80:

				$view = new Zend_View();
				$view->name = $this->name;
				$view->value = $this->value;
				$view->setScriptPath(dirname(__FILE__));
				return $view->render('TextArea3.phtml');
	        	break;         
	            
	        case 3:     // field type = hidden
	            $n = "<input type='hidden' name='$this->name' value='$this->value'>";
	            return $n;
	            break;
	            
	        case -400:
		        //$value = $this->convertDate($fieldValue);
		        $value = $fieldValue;
				echo '<script language="Javascript" src="calendar/calendar.js"></script>';
				echo '<input type="text" name="' . $attributeId . '" value="' . $value . '">';
				$fieldTblGuid = $attributeId.'_guid';
	            echo "<input type='hidden' name='$fieldTblGuid' value='$tblGuid'>";
				echo '&nbsp;<a href="javascript: void(0);" onclick="return getCalendar(document.forms[0].'.$attributeId.');" onChange="AddCurrentTime(document.forms[0].'.$attributeId.');">Pilih Tanggal</a>';
				
				break;
			
	        case 4:	
				$view = new Zend_View();
				$view->label = $this->label;
				$view->name = $this->name;
				$view->value = $this->value;
				
				$view->setScriptPath(dirname(__FILE__));
				$view->addHelperPath(APPLICATION_PATH . '/../library/Pandamp/Controller/Action/Helper', 'Pandamp_Controller_Action_Helper');
				return $view->render('datetime.phtml');
	        break;
	        
			//datetime field	
			case 5:
				/*echo 
				'<link rel="stylesheet" type="text/css" media="all" href="calendar2/calendar-mos.css" title="green" />	
				<script type="text/javascript" src="calendar2/calendar.js"></script>
				<script type="text/javascript" src="calendar2/lang/calendar-en.js"></script>
				<script language="javascript" src="calendar2/mambojavascript.js"></script>';*/
				
				$view = new Zend_View();
				$view->name = $this->name;
				$view->value = $this->value;
				
				$view->setScriptPath(dirname(__FILE__));
				$view->addHelperPath(APPLICATION_PATH . '/../library/Pandamp/Controller/Action/Helper', 'Pandamp_Controller_Action_Helper');
				return $view->render('datetime.phtml');
				
				/*$fieldTblGuid = $attributeId.'_guid';
				echo "<input type='hidden' name='$fieldTblGuid' value='$tblGuid'>";
				echo '<input class="inputbox" type="text" name="'.$attributeId.'" id="'.$attributeId.'" size="25" maxlength="25" value="'.$fieldValue.'" />';
	          	
				echo '<input type="reset" class="button" value="..." onClick="return showCalendar'."('$attributeId', 'dd/mm/Y')".';">';*/
				break;
			
			// updated Nov 1, 2015	
			case 6:
				$view = new Zend_View();
				$view->label = $this->label;
				$view->name = $this->name;
				$view->value = $this->value;
				if (isset($this->attribs))
					$view->attribs = $this->attribs;
				else 
					$view->attribs = array('class' => 'form-control');
				$view->setScriptPath(dirname(__FILE__));
				return $view->render('Text.phtml');
				break;
				
			case -5:     // field type = Image Area
	            $frm = new FormInputImageAreaUc();
				$frm->fieldName=$attributeId;
				$frm->fieldValue=$fieldValue;
				$frm->renderMe();
				$fieldTblGuid = $attributeId.'_guid';
	            echo "<input type='hidden' name='$fieldTblGuid' value='$tblGuid'>";
	            break;
	        
	        case -6:     // field type = LABEL
	         	echo $fieldValue;
	            echo "<input type='hidden' name='$attributeId' value='$fieldValue'>";
	            $fieldTblGuid = $attributeId.'_guid';
	            echo "<input type='hidden' name='$fieldTblGuid' value='$tblGuid'>";
	            break;
	            
	        case 7:     // field type = MULTI VALUE (SELECT:OPTIONS)
	        
	        	/*$oAttGenerator = new UiFormInputAttributeGenerator();
	        	$s = $oAttGenerator->generateFormInputAttributeByDmsProfileGuidAndAttributeGuid($this->dmsProfileGuid,$attributeId,$attributeId,$fieldValue);
	        	
	        	echo $s;
	            //echo "<textarea name='$attributeId' rows='0' cols='50'>$fieldValue</textarea>";
	            
	            $fieldTblGuid = $attributeId.'_guid';
	            echo "<input type='hidden' name='$fieldTblGuid' value='$tblGuid'>";
	            break;*/
	        	
	        	$tblProAtt = new App_Model_Db_Table_ProfileAttribute();
	        	$rowset = $tblProAtt->fetchAll("profileGuid='$this->profileGuid' AND attributeGuid='$this->name'");
	        	$defaultValues = array();
	        	if(count($rowset)==1)
	        	{
	        		$row = $rowset->current();
	        		$defaultValues =  Zend_Json::decode($row->defaultValues);
	        		
	        		if(is_array($defaultValues))
	        		{
	        			//var_dump($defaultValues);
	        		}
	        		else 
	        		{
	        			$defaultValues = array();
	        		}
	        	}
	        	
	        	$view = new Zend_View();
	        	$view->label = $this->label;
				$view->name = $this->name;
				$view->value = $this->value;
				$view->defaultValues = $defaultValues;
				/*if(isset($this->attribs))
					$view->attribs = $this->attribs;
				else
					$view->attribs = array('rows' => 5, 'cols' =>50);*/
				$view->setScriptPath(dirname(__FILE__));
				return $view->render('select.phtml');
	            
	            break;
			
	        case 8 :
	        	
				$tblCatalog = new App_Model_Db_Table_Catalog();
				$rowset = $tblCatalog->fetchAll("profileGuid='$this->other'",'shortTitle asc');
				$i = 0;
				$a = array();
				$data = array();
				foreach ($rowset as $row)
				{
					$rowsetCatalogAttribute = $row->findDependentRowsetCatalogAttribute();
					$rowCatalogAttribute = $rowsetCatalogAttribute->findByAttributeGuid('fixedTitle');
					$a[$i]['label']= ((is_object($rowCatalogAttribute)) ? $rowCatalogAttribute->value : '');
					$a[$i]['value']= "$row->guid";
					$a[$i]['selected']= ($i==0)? "true" : "false";
					$i++;
				}
				
				$data =  Zend_Json::decode(Zend_Json::encode($a));      	
				
	        	$view = new Zend_View();
	        	$view->label = $this->label;
				$view->name = $this->name;
				$view->value = $this->value;
				$view->defaultValues = $data;
				$view->setScriptPath(dirname(__FILE__));
				return $view->render('select.phtml');
				
	        break;
	        case 9 :	        
	            $n = "<input type='text' class='txt' name='$this->name' value='$this->value' size='5'>";
	            return $n;
	        break;
	        case 10 :
	        	$n = '<textarea id="html" name="jTagEditor" class="jTagEditor">'.$this->value.'</textarea>';
	        	return $n;
	        break;
	        case 11:
	        	if ($this->value == 1)
	        	{
	        		$check='checked';
	        	}
	        	else 
	        	{
	        		$check='';
	        	}
	        	$n = "<input type='checkbox' name='$this->name' value='1' $check>";
	        	return $n;
	        break;
	        case 12:
	        	
	        	if ($this->profileGuid == 'talks')
	        		$this->profileGuid = 'narsum';
	        	
				$tblCatalog = new App_Model_Db_Table_Catalog();
				$rowset = $tblCatalog->fetchAll("profileGuid='$this->profileGuid'",'shortTitle asc');
				
				$i = 0;
				$a = array();
				$data = array();
				
				foreach ($rowset as $row)
				{
					$rowsetCatalogAttribute = $row->findDependentRowsetCatalogAttribute();
					$rowCatalogAttribute = $rowsetCatalogAttribute->findByAttributeGuid('fixedTitle');
					$a[$i]['label']= ((is_object($rowCatalogAttribute)) ? trim($rowCatalogAttribute->value) : '');
					$a[$i]['value']= "$row->guid";
					$a[$i]['selected']= ($i==0)? "true" : "false";
					$i++;
				}
				
				$data =  Zend_Json::decode(Zend_Json::encode($a));      	
				
	        	$view = new Zend_View();
	        	$view->label = $this->label;
				$view->name = $this->name;
				$view->value = $this->value;
				$view->defaultValues = $data;
				$view->setScriptPath(dirname(__FILE__));
				return $view->render('select.phtml');
	        
	        break;
	        
	        /*
	         * @TODO category clinic
	         */
	        
	        case 13:
	        	
				$tblCatalog = new App_Model_Db_Table_Catalog();
				$rowset = $tblCatalog->fetchAll("profileGuid='kategoriklinik'",'shortTitle asc');
				
				$i = 0;
				$a = array();
				$data = array();
				
				foreach ($rowset as $row)
				{
					$rowsetCatalogAttribute = $row->findDependentRowsetCatalogAttribute();
					$rowCatalogAttribute = $rowsetCatalogAttribute->findByAttributeGuid('fixedTitle');
					$a[$i]['label']= ((is_object($rowCatalogAttribute)) ? $rowCatalogAttribute->value : '');
					$a[$i]['value']= "$row->guid";
					$a[$i]['selected']= ($i==0)? "true" : "false";
					$i++;
				}
				
				$data =  Zend_Json::decode(Zend_Json::encode($a));      	
				
	        	$view = new Zend_View();
	        	$view->label = $this->label;
				$view->name = $this->name;
				$view->value = $this->value;
				$view->defaultValues = $data;
				$view->setScriptPath(dirname(__FILE__));
				return $view->render('select.phtml');
	        
	        break;
	        
	        case 14: // tags
	        	$view = new Zend_View();
	        	$view->label = $this->label;
	        	$view->name = $this->name;
	        	$view->value = $this->value;
	        	if(isset($this->attribs))
	        		$view->attribs = $this->attribs;
	        	else
	        		$view->attribs = array('rows' => 3, 'class' =>'form-control');
	        	$view->setScriptPath(dirname(__FILE__));
	        	return $view->render('tags.phtml');
	        	 
	        break;
	        
			case 101: //publishing status
				require_once(CONFIG_PATH.'/master-status.php');
				$aStatus = MasterStatus::getPublishingStatus();
				
				$view = new Zend_View();
				$view->name = $this->name;
				$view->value = $this->value;
				
				$auth = Zend_Auth::getInstance();
				$identity = $auth->getIdentity();
				$username = $identity->username;
				// get group information
				$acl = Pandamp_Acl::manager();
				$aReturn = $acl->getUserGroupIds($username);
				
				if (isset($aReturn[1]))
				{
					if (($aReturn[1] == "klinik_editor"))
					{
						unset($aStatus[99]);
					}
				}
				
				$view->defaultValues = $aStatus;
				$view->setScriptPath(dirname(__FILE__));
				return $view->render('select2.phtml');
        }
	}
}

?>