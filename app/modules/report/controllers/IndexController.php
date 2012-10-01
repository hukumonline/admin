<?php
/**
 * @author	2012-2013 Nihki Prihadi <nihki@madaniyah.com>
 * @version $Id: IndexController.php 1 2012-08-29 13:20Z $
 */

class Report_IndexController extends Zend_Controller_Action 
{
    protected $_user;

    function  preDispatch()
    {
        $this->_helper->layout->setLayout('layout-report');

        $auth = Zend_Auth::getInstance();
        
        $identity = Pandamp_Application::getResource('identity');

        $loginUrl = $identity->loginUrl;
        
		$multidb = Pandamp_Application::getResource('multidb');
		$multidb->init();
		
		$db = $multidb->getDb('db2');
		
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
			
			// check session expire
			$timeLeftTillSessionExpires = $_SESSION['__ZF']['Zend_Auth']['ENT'] - time();

			if (Pandamp_Lib_Formater::diff('now', $this->_user->dtime) > $timeLeftTillSessionExpires) {
				$db->update('KutuUser',array('ses'=>'*'),"ses='".Zend_Session::getId()."'");
				$flashMessenger = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
		        $flashMessenger->addMessage('Session Expired');
		        $auth->clearIdentity();
		        
		        $this->_redirect($loginUrl.'?returnUrl='.$sReturn);     
			}
			
			$dat = Pandamp_Lib_Formater::now();
			$db->update('KutuUser',array('dtime'=>$dat),"ses='".Zend_Session::getId()."'");
        }
    }
    
    function indexAction()
    {
    	$request = $this->getRequest();
    	
    	$printmode = $request->getParam('printmode');
    	
    	if ($printmode == 1)
    		$this->_helper->layout->setLayout('layout-report-print');
    	
    	$now = getdate();
    	
    	$y 		= ($request->getParam('year'))? $request->getParam('year') : $now['year'];
    	$show 	= ($request->getParam('show'))? $request->getParam('show') : 0;
    	$prof 	= ($request->getParam('prof'))? $request->getParam('prof') : 0;
    	
    	$uri 	= $request->getRequestUri();
		
		if(strpos($uri,'?'))
			$sAddition = '&';
		else 
			$sAddition = '?';
			
			
    	if ($show == 0)
    		$showname = "Table";
    	else
    		$showname = "Graph";
    	
    		
		$yearper = 0;
		$yearput = 0;
		$yearart = 0;
		$yearkli = 0;
		$year_enews = 0;
		$year_eilb = 0;
		$year_eild = 0;
		$year_user = 0;
		$year_user_individual = 0;
		$year_user_corporate = 0;
		$year_user_ilb = 0;
		$year_user_ild = 0;
		$year_user_complete = 0;
		$year_user_professional = 0;
		$year_user_premium = 0;
		$year_user_executive = 0;
		
		$sumrep = 0;
		
		$sum = array(0,0,0,0,0,0,0,0,0,0,0,0);
		
		$url = "";
		
		$mon = array("$y-01","$y-02","$y-03","$y-04","$y-05","$y-06","$y-07","$y-08","$y-09","$y-10","$y-11","$y-12");
		for($i=0; $i<count($mon); $i++) { 
			$peraturan[] = App_Model_Show_Catalog::show()->getCatalogByMonth('peraturan',$mon[$i]);
			$yearper += $peraturan[$i];
			$putusan[] = App_Model_Show_Catalog::show()->getCatalogByMonth('kutu_putusan',$mon[$i]);
			$yearput += $putusan[$i];
			$article[] = App_Model_Show_Catalog::show()->getCatalogByMonth('article',$mon[$i]);
			$yearart += $article[$i];
			$clinic[] = App_Model_Show_Catalog::show()->getCatalogByMonth('klinik',$mon[$i]);
			$yearkli += $clinic[$i];
			
			$enews[] = App_Model_Show_Catalog::show()->getEnglishCatalogByMonth('news',$mon[$i]);
			$year_enews += $enews[$i];
			$eilb[] = App_Model_Show_Catalog::show()->getEnglishCatalogByMonth('ilb',$mon[$i]);
			$year_eilb += $eilb[$i];
			$eild[] = App_Model_Show_Catalog::show()->getEnglishCatalogByMonth('ild',$mon[$i]);
			$year_eild += $eild[$i];
			
			$user[] = App_Model_Show_User::show()->getUserByMonth(13,$mon[$i]);
			$year_user += $user[$i];
			$user_individual[] = App_Model_Show_User::show()->getUserByMonth(14,$mon[$i]);
			$year_user_individual += $user_individual[$i];
			$user_corporate[] = App_Model_Show_User::show()->getUserByMonth(15,$mon[$i]);
			$year_user_corporate += $user_corporate[$i];
			$user_ilb[] = App_Model_Show_User::show()->getUserByMonth(16,$mon[$i]);
			$year_user_ilb += $user_ilb[$i];
			$user_ild[] = App_Model_Show_User::show()->getUserByMonth(17,$mon[$i]);
			$year_user_ild += $user_ild[$i];
			$user_complete[] = App_Model_Show_User::show()->getUserByMonth(18,$mon[$i]);
			$year_user_complete += $user_complete[$i];
			$user_professional[] = App_Model_Show_User::show()->getUserByMonth(36,$mon[$i]);
			$year_user_professional += $user_professional[$i];
			$user_premium[] = App_Model_Show_User::show()->getUserByMonth(37,$mon[$i]);
			$year_user_premium += $user_premium[$i];
			$user_exec[] = App_Model_Show_User::show()->getUserByMonth(38,$mon[$i]);
			$year_user_executive += $user_exec[$i];
			
			if ($prof == 0) 			// all
				$sum[$i] = $peraturan[$i] + $putusan[$i] + $article[$i] + $clinic[$i] + $enews[$i] + $eilb[$i] + $eild[$i] + $user[$i] + $user_individual[$i] + $user_corporate[$i] + $user_ilb[$i] + $user_ild[$i] + $user_complete[$i] + $user_professional[$i] + $user_premium[$i] + $user_exec[$i];
			else if ($prof == 1) 		// Data Center
				$sum[$i] = $peraturan[$i] + $putusan[$i];
			else if ($prof == 2)		// Redaksi
				$sum[$i] = $article[$i];
			else if ($prof == 3)		// Klinik
				$sum[$i] = $clinic[$i];
			else if ($prof == 4)		// E-News
				$sum[$i] = $enews[$i] + $eilb[$i] + $eild[$i];
			else if ($prof == 5)		// User
				$sum[$i] = $user[$i] + $user_individual[$i] + $user_corporate[$i] + $user_ilb[$i] + $user_ild[$i] + $user_complete[$i] + $user_professional[$i] + $user_premium[$i] + $user_exec[$i];
				
				
			$sumrep += $sum[$i];
			
            $url .= "&x[$i]=".Pandamp_Lib_Calendar::get_month_name($i+1,"%b")
                 . "&y1[$i]=".$sum[$i];
                 
		}
		
		if ($prof == 0) 			
			$profname = "All Profile";
		else if ($prof == 1) 		
			$profname = "Data Center";
		else if ($prof == 2)		
			$profname = "Redaksi";
		else if ($prof == 3)		
			$profname = "Klinik";
		else if ($prof == 4)		
			$profname = "English";
		else if ($prof == 5)		
			$profname = "Layanan";
					
		//$url = "tabtitle1=Division Reports of $y&tabtitle2=Total Records $sumrep&tabtitle3=$profname&y1legend=Value (record)&showvalue=1".$url; // --> JPGraph type 5
		$url = "tabtitle1=Division Reports of $y&tabtitle2=Total Records $sumrep&tabtitle3=$profname&y1legend=Value (record)&novalue=0&decimal=1".$url;
		
		$this->view->assign('y',$y);
		$this->view->assign('sAddition',$sAddition);
		$this->view->assign('show',$show);
		$this->view->assign('showname',$showname);
		$this->view->assign('prof',$prof);
		$this->view->assign('profname',$profname);
		$this->view->assign('p',$peraturan);
		$this->view->assign('yp',$yearper);
		$this->view->assign('put',$putusan);
		$this->view->assign('yput',$yearput);
		$this->view->assign('art',$article);
		$this->view->assign('yart',$yearart);
		$this->view->assign('clinic',$clinic);
		$this->view->assign('ykli',$yearkli);
		$this->view->assign('enews',$enews);
		$this->view->assign('yenews',$year_enews);
		$this->view->assign('eilb',$eilb);
		$this->view->assign('yeilb',$year_eilb);
		$this->view->assign('eild',$eild);
		$this->view->assign('yeild',$year_eild);
		$this->view->assign('user',$user);
		$this->view->assign('year_user',$year_user);
		$this->view->assign('user_individual',$user_individual);
		$this->view->assign('year_user_individual',$year_user_individual);
		$this->view->assign('user_corporate',$user_corporate);
		$this->view->assign('year_user_corporate',$year_user_corporate);
		$this->view->assign('user_ilb',$user_ilb);
		$this->view->assign('year_user_ilb',$year_user_ilb);
		$this->view->assign('user_ild',$user_ild);
		$this->view->assign('year_user_ild',$year_user_ild);
		$this->view->assign('user_complete',$user_complete);
		$this->view->assign('year_user_complete',$year_user_complete);
		$this->view->assign('user_professional',$user_professional);
		$this->view->assign('year_user_professional',$year_user_professional);
		$this->view->assign('user_premium',$user_premium);
		$this->view->assign('year_user_premium',$year_user_premium);
		$this->view->assign('user_exec',$user_exec);
		$this->view->assign('year_user_executive',$year_user_executive);
		
		$this->view->assign('sum',$sum);
		$this->view->assign('sumrep',$sumrep);
		
		$this->view->assign('uri',$uri);
		
		$this->view->assign('printmode',$printmode);
		
		//$this->view->assign('graph_url', ROOT_URL."/data/graph/graph.view.php?type=5&$url");
		$this->view->assign('graph_url', ROOT_URL."/data/graph/graph.view.php?type=1&$url");
		$this->view->assign('graph_alt', "Division Reports");
    }
    
    function headerAction()
    {
        $r = $this->getRequest();
        $sOffset = $r->getParam('sOffset');
        $this->view->sOffset = $sOffset;
        $sLimit = $r->getParam('sLimit');
        $this->view->sLimit = $sLimit;

        $query = ($r->getParam('q'))? $r->getParam('q') : '';
        $this->_helper->layout()->searchQuery = $query;
        $this->view->user = $this->_user;
    }
}