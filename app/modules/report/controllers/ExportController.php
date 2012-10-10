<?php
/**
 * @author	2012-2013 Nihki Prihadi <nihki@madaniyah.com>
 * @version $Id: ExportController.php 1 2012-10-09 20:18Z $
 */

class Report_ExportController extends Zend_Controller_Action  
{
	protected $_user;
	
    public function init()
    {
    	$excelConfig = array(
    		'excel' => array(
    			'suffix' 	=> 'excel',
    			'headers'	=> array(
    				'Content-Type'	=> 'application/vnd.ms-excel',
    				'Content-Disposition'	=> "attachment; filename=".date('Ymd').".xls",
    				'Pragma'		=> 'no-cache',
    				'Expires'		=> '0'
    			)
    		),
			'json' => array(
            	'suffix'    => 'json',
                'headers'   => array('Content-Type' => 'application/json'),
                'callbacks' => array(
                	'init' => 'initJsonContext',
                	'post' => 'postJsonContext'
          		)
        	)    		
    	);
    	
    	$contextSwitch = $this->_helper->contextSwitch();
    	
    	$contextSwitch->setContexts($excelConfig);
    	
        $contextSwitch->addActionContext('dc', 'excel')
                      ->initContext();
    }
    
    function preDispatch()
    {
        $auth = Zend_Auth::getInstance();
        
        $identity = Pandamp_Application::getResource('identity');

        $loginUrl = $identity->loginUrl;
        
        $sReturn = "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
        $sReturn = base64_encode($sReturn);

        if (!$auth->hasIdentity()) {
			
			$this->_redirect($loginUrl.'?returnUrl='.$sReturn);     
        }
        else
        {
            $this->_user = $auth->getIdentity();

            $zl = Zend_Registry::get("Zend_Locale");
            
            $acl = Pandamp_Acl::manager();
            if (!$acl->checkAcl("site",'all','user', $this->_user->username, false,false))
            {
                //$this->_redirect(ROOT_URL.'/'.$zl->getLanguage().'/error/restricted');
                $this->_forward('restricted','error','admin',array('lang'=>$zl->getLanguage()));
            }
            
			// [TODO] else: check if user has access to admin page and status website is online
			$tblSetting = new App_Model_Db_Table_Setting();
			$rowset = $tblSetting->find(1)->current();
			
			if ($rowset)
			{
				if (($rowset->status == 1 && $zl->getLanguage() == 'id') || ($rowset->status == 2 && $zl->getLanguage() == 'en') || ($rowset->status == 3))
				{
					// it means that user offline other than admin
					$aReturn = App_Model_Show_AroGroup::show()->getUserGroup($this->_user->packageId);
					
					if (isset($aReturn['name']))
					{
						//if (($aReturn[1] !== "admin"))
						if (($aReturn['name'] !== "Master") && ($aReturn['name'] !== "Super Admin"))
						{
							$this->_forward('temporary','error','admin'); 
						}
					}
				}
			}
        }
    }
    
	public function dcAction()
	{
		$request = $this->getRequest();
		
		$data = explode(',',$request->getParam('guid'));
		
		$this->view->assign('data',$data);
	}
}