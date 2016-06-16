<?php

/**
 * Description of DmsController
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Search_DmsController extends Zend_Controller_Action
{
    protected $_user;

    function  preDispatch()
    {
        //$this->_helper->layout->setLayout('layout-paging-search');
        $this->_helper->layout->setLayout('layout-paging-newsearch');

        $auth = Zend_Auth::getInstance();
        
        $identity = Pandamp_Application::getResource('identity');

        $loginUrl = $identity->loginUrl;
        
        /*
		$multidb = Pandamp_Application::getResource('multidb');
		$multidb->init();
		
		$db = $multidb->getDb('db2');
		*/
		
        $sReturn = "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
        $sReturn = base64_encode($sReturn);

        //$sso = new Pandamp_Session_Remote();
        //$user = $sso->getInfo();

        if (!$auth->hasIdentity()) {
            //$this->_forward('login','account','admin');
        	if ($this->getRequest()->isXmlHttpRequest()) {
        		//$this->getResponse()->setHttpResponseCode(401);
        		$this->getResponse()->setRawHeader('HTTP/1.1 401 Not Found');
        		$return = array('message' => 'You are not allowed to access this page because you do not have permission to view it.<br />Please contact the administrator.');
        		$this->getResponse()->setBody(Zend_Json::encode($return));
        		$this->getResponse()->setBody(
       				"<script>alert('f');</script>"        				
        		);die;
        	}
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
			/*
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
			*/
        }
    }
    
    public function browseAction()
    {
    	$time_start = microtime(true);
    	
    	$request = $this->getRequest();
    	$query = $xq = $request->getParam('q');
    	$category = $request->getParam('category');
    	$kategoriklinik = $request->getParam('kategoriklinik');
    	$clinic_selected = $request->getParam('clinic_selected');
    	$regulationType = $request->getParam('regulationType');
    	$regulationSelected = $request->getParam('regulationSelected');
    	$putusanSelected = $request->getParam('putusanSelected');
    	$createdBy = $request->getParam('createdBy');
    	$status = $request->getParam('status');
    	$sort = $request->getParam('sort','publishedDate');
    	$order = $request->getParam('order','desc');
    	$pageIndex = $request->getParam('page',1);
    	$perpage = $request->getParam('showperpage',25);
    	
    	$offset = ($pageIndex > 0) ? ($pageIndex - 1) * $perpage : 0;
    	
    	$modDir = $this->getFrontController()->getModuleDirectory('dms');
    	require_once($modDir.'/components/Menu/FolderBreadcrumbs2.php');
    	$w = new Dms_Menu_FolderBreadcrumbs2('root');
    	$this->view->assign('breadcrumbs', $w);
    	
    	$indexingEngine = Pandamp_Search::manager();
    	
    	if ($query == '*' || $query == '') {
    		$query = '*:*';
    	}
    	
    	if (isset($status)) {
    		$query = $query." status:$status";
    		
    		// jika status=draft
    		if ($status == 0) {
    			$sort = "createdDate";
    			$order = "desc";
    		}
    	}

    	if ($clinic_selected == 1 and $kategoriklinik!='no_categori')
    		$query = $query." kategori:".$kategoriklinik;
    	
    	if ($regulationSelected == 1 or $putusanSelected == 1)
    		$query = $query." regulationType:".$regulationType;
    	
    	if ($category) 
    		$query = $query.' profile:'.$category;
    	
    	if ($createdBy)
    		$query = $query.' createdBy:'.$createdBy;
    	
    	
    	$hits = $indexingEngine->find($query, $offset, $perpage,$sort." ".$order);
    	
    	$solrNumFound = count($hits->response->docs);
    	
    	$data = array();
    	
    	if($solrNumFound>$perpage)
    		$numRowset = $perpage ;
    	else
    		$numRowset = $solrNumFound;
    	
    	for ($i=0;$i<$numRowset;$i++) {
    		$row = $hits->response->docs[$i];
    		$data[$i]['id'] = $row->id;
    		
    		if (isset($hits->highlighting->{$row->id}->title[0]))
    			$data[$i]['title'] = $hits->highlighting->{$row->id}->title[0];
    		else
    			$data[$i]['title'] = $row->title;
    		
    		if (is_array($row->kategoriId))
				$kid = $row->kategoriId[0];
			else
				$kid = $row->kategoriId;
    		
    		$data[$i]['node'] = $kid;
    		
    		$profile = str_replace(array('kutu_'), "", $row->profile);
    		$profile = str_replace(array('peraturan_kolonial'), "kolonial", $profile);
    		$data[$i]['profile'] = ucfirst($profile);
    		
    		if (isset($hits->highlighting->{$row->id}->subTitle[0]))
    			$subTitle = $hits->highlighting->{$row->id}->subTitle[0];
    		else
    			$subTitle = $row->subTitle;
    		
    		$data[$i]['subTitle'] = $subTitle;
    		$data[$i]['description'] = $row->description;
    		
    		/*$array_hari = array(1=>"Senin","Selasa","Rabu","Kamis","Jumat","Sabtu","Minggu");
    		$pdate = $indexingEngine->translateSolrDate($row->publishedDate);
    		$hari = $array_hari[date("N",strtotime($pdate))];
    		$cdate = $indexingEngine->translateSolrDate($row->createdDate);
    		$chari = $array_hari[date("N",strtotime($cdate))];*/
    		
    		$data[$i]['publishedDate'] = $row->publishedDate;
    		$data[$i]['createdDate'] = $row->createdDate;
    		$data[$i]['modifiedDate'] = $row->modifiedDate;
    		$data[$i]['deletedDate'] = $row->deletedDate;
    		$data[$i]['createdBy'] = $row->createdBy;
    		$data[$i]['modifiedBy'] = $row->modifiedBy;
    	}
    	
    	$num_rows = $hits->response->numFound;
    	
    	$this_url = $this->getRequest()->getRequestUri();
    	$this_url = str_replace("&page=$pageIndex", "", $this_url);
    	$this_url = str_replace("&showperpage=$perpage", "", $this_url);
    	$this_url = str_replace("&status=$status", "", $this_url);
    	$this_url = str_replace("&sort=$sort", "", $this_url);
    	$this_url = str_replace("&order=$order", "", $this_url);
    	$this->view->assign('this_url',$this_url);
    	
    	$bysorting = explode("[", $query);
    	if (isset($bysorting[1])) {
    		$bysorting = str_replace("]", "", $bysorting[1]);
    		$this->view->assign('bysortings',$bysorting);
    	}
    	
    	$paginator = Zend_Paginator::factory($num_rows);
    	$paginator->setCurrentPageNumber($pageIndex);
    	$paginator->setItemCountPerPage($perpage);
    	$paginator = get_object_vars($paginator->getPages('Sliding'));
    	
    	$this->view->assign('paginator',$paginator);
    	$this->view->assign('data',$data);
    	$this->view->assign('numberOfRows',$numRowset);
    	$this->view->assign('totalItems',$num_rows);
    	$this->view->assign('query',$query);
    	$this->view->assign('sort',$sort);
    	$this->view->assign('order',$order);
    	$this->view->assign('tsort',($sort=='score')? 'Relevance' : $sort);
    	$this->view->assign('torder',($order=='desc')? 'Ascending' : 'Descending');
    	$this->view->assign('sorder',($order=='desc')? 'asc' : 'desc');
    	
    	$this->_helper->layout()->searchQuery = $xq;
    	$this->_helper->layout()->kategoriklinik = $kategoriklinik;
    	$this->_helper->layout()->createdBy = $createdBy;
    	$this->_helper->layout()->category = $category;
    	$this->_helper->layout()->clinicSelected = $clinic_selected;
    	$this->_helper->layout()->putusanSelected = $putusanSelected;
    	$this->_helper->layout()->regulationType = $regulationType;
    	$this->_helper->layout()->showperpage = $perpage;
    	$this->_helper->layout()->status = $status;
    	
    	$time_end = microtime(true);
    	$time = $time_end - $time_start;
    	
    	$this->view->assign('time',round($time,2));
    }
    
    public function facetauthorAction()
    {
    	$request = $this->getRequest();
    	$q = $wq = $request->getParam('q');
    	$createdBy = $request->getParam('createdBy');
    	if ($q) {
    		if(!preg_match("/(id:|shortTitle:|profile:|publishedDate:|expiredDate:|createdDate:|modifiedDate:|createdBy:|modifiedBy:|status:|author:|fixedDate:|regulationType:|regulationOrder:|kategori:|kategoriklinik:|kontributor:|sumber:|year:|number:|title:)/i", $q))
    		{
    			require_once( 'Apache/Solr/Service.php' );
    			$q = Apache_Solr_Service::escape($q);
    		}
    	
    		$query = $q;
    	}
    	else
    	{
    		$query = "";
    	}
    	 
    	$query = str_replace('\\', '', $query);
    	 
    	$indexingEngine = Pandamp_Search::manager();
    	 
    	$hits = $indexingEngine->find($query,0,1);
    	 
    	if (isset($hits->response->docs[0])) {
    		$content = 0;
    		$data = array();
    	
    		foreach ($hits->facet_counts->facet_fields->createdBy as $facet => $count)
    		{
    			if ($count == 0 || in_array($facet, array('comment','partner','kategoriklinik','kutu_signup')))
    			{
    				continue;
    			}
    			else
    			{
    				$f = str_replace(array('kutu_'), "", $facet);
    				$f = str_replace("peraturan_kolonial","peraturan kolonial",$f);
    				$f = str_replace("rancangan_peraturan","rancangan peraturan",$f);
    				$f = str_replace("about_us","tentang kami",$f);
    				$f = str_replace("kotik","kode etik jurnalis",$f);
    				$f = str_replace("kategoriklinik","kategori klinik",$f);
    				$f = str_replace("mitra","mitra hukumonline",$f);
    				$f = str_replace("partner","mitra klinik",$f);
    				$f = str_replace("author","penjawab klinik",$f);
    				//$f = str_replace("article","artikel",$f);
    				$f = str_replace("comment","komentar",$f);
    				$f = str_replace("doc","pelengkap",$f);
    				$f = str_replace("signup","pendaftaran",$f);
    				$f = str_replace("financial_services","financial services",$f);
    				$f = str_replace("general_corporate","general corporate",$f);
    				$f = str_replace("oil_and_gas","oil and gas",$f);
    				$f = str_replace("executive_alert","executive alert",$f);
    				$f = str_replace("manufacturing_&_industry","manufacturing & industry",$f);
    				$f = str_replace("consumer_goods","consumer goods",$f);
    				$f = str_replace("telecommunications_and_media","telecommunications and media",$f);
    				$f = str_replace("executive_summary","executive summary",$f);
    				$f = str_replace("hot_news","hot news",$f);
    				$f = str_replace("hot_issue_ile","hot issue ILE",$f);
    				$f = str_replace("hot_issue_ild","hot issue ILD",$f);
    				$f = str_replace("hot_issue_ilb","hot issue ILB",$f);
    	
    				$data[$content]['facet'] = $f;
    				$data[$content]['profile'] = $facet;
    				$data[$content]['count'] = $count;
    			}
    			 
    			$content++;
    		}
    	
    		$this->view->assign('aData', $data);
    		$this->view->assign('createdBy', $createdBy);
    		$this->view->assign('query', urlencode($wq));
    	}
    }
    
    public function facetjpAction()
    {
    	$request = $this->getRequest();
    	$q = $wq = $request->getParam('q');
    	$regulationType = $request->getParam('regulationType');
    	$zl = Zend_Registry::get('Zend_Locale');
    	if (($q) && ($zl->getLanguage() !== 'ha')) {
    		if(!preg_match("/(id:|shortTitle:|profile:|publishedDate:|expiredDate:|createdDate:|modifiedDate:|createdBy:|modifiedBy:|status:|author:|fixedDate:|regulationType:|regulationOrder:|kategori:|kategoriklinik:|kontributor:|sumber:|year:|number:|title:)/i", $q))
    		{
    			require_once( 'Apache/Solr/Service.php' );
    			$q = Apache_Solr_Service::escape($q);
    		}
    		
    		if ($zl->getLanguage() == 'id'){
    			$query = $q . ' profile:(kutu_peraturan_kolonial OR kutu_rancangan_peraturan OR kutu_peraturan) -profile:kutu_putusan -profile:kutu_contact -profile:kutu_doc -profile:comment -profile:isuhangat -profile:partner -profile:author -profile:about_us -profile:kategoriklinik -profile:kutu_email -profile:kutu_kotik -profile:kutu_mitra profile:[" " TO *] title:[" " TO *]';
    		}
    		else
    		{
    			$query = $q . ' -profile:kategoriklinik -profile:kutu_doc -profile:trial_periods -profile:about_us -profile:signin -profile:manual -profile:contact -profile:career -profile:kutu_contentjp -profile:comments -profile:hot_issue_ile -profile:hot_issue_ilb -profile:hot_issue_ild -profile:executive_alert -profile:executive_alert -profile:banner -profile:products -profile:partner -profile:hot_news profile:[" " TO *] title:[" " TO *]';
    		}
    	}
    	else
    	{
    		$query = "";
    	}
    	$query = str_replace('\\', '', $query);
    	 
    	$indexingEngine = Pandamp_Search::manager();
    	 
    	$hits = $indexingEngine->find($query,0,1,"regulationType asc");
    	 
    	if (isset($hits->response->docs[0])) {
    		$content = 0;
    		$data = array();
    	
    		foreach ($hits->facet_counts->facet_fields->regulationType as $facet => $count)
    		{
    			if ($count == 0 || in_array($facet, array('0','_empty_')))
    			{
    				continue;
    			}
    			else
    			{
    				$data[$content]['profile'] = $facet;
    				$data[$content]['count'] = $count;
    			}
    	
    			$content++;
    		}
    	
    		$this->view->assign('aData', $data);
    		$this->view->assign('query', urlencode($wq));
    		$this->view->assign('regulationType', $regulationType);
    	}
    }
    
    public function facetlpAction()
    {
    	$request = $this->getRequest();
    	$q = $wq = $request->getParam('q');
    	$regulationType = $request->getParam('regulationType');
    	$zl = Zend_Registry::get('Zend_Locale');
    	if (($q) && ($zl->getLanguage() !== 'ha')) {
    		if(!preg_match("/(id:|shortTitle:|profile:|publishedDate:|expiredDate:|createdDate:|modifiedDate:|createdBy:|modifiedBy:|status:|author:|fixedDate:|regulationType:|regulationOrder:|kategori:|kategoriklinik:|kontributor:|sumber:|year:|number:|title:)/i", $q))
    		{
    			require_once( 'Apache/Solr/Service.php' );
    			$q = Apache_Solr_Service::escape($q);
    		}
    	
	    	if ($zl->getLanguage() == 'id'){
				$query = $q . ' profile:kutu_putusan -profile:(kutu_peraturan_kolonial OR kutu_rancangan_peraturan OR kutu_peraturan) -profile:kutu_contact -profile:kutu_doc -profile:comment -profile:isuhangat -profile:partner -profile:author -profile:about_us -profile:kategoriklinik -profile:kutu_email -profile:kutu_kotik -profile:kutu_mitra profile:[" " TO *] title:[" " TO *]';	
			}
			else
			{
				$query = $q . ' -profile:kategoriklinik -profile:kutu_doc -profile:trial_periods -profile:about_us -profile:signin -profile:manual -profile:contact -profile:career -profile:kutu_contentjp -profile:comments -profile:hot_issue_ile -profile:hot_issue_ilb -profile:hot_issue_ild -profile:executive_alert -profile:executive_alert -profile:banner -profile:products -profile:partner -profile:hot_news profile:[" " TO *] title:[" " TO *]';
			}
    	}
    	else
    	{
    		$query = "";
    	}
    	$query = str_replace('\\', '', $query);
    	
    	$indexingEngine = Pandamp_Search::manager();
    	
    	$hits = $indexingEngine->find($query,0,1);
    	
    	if (isset($hits->response->docs[0])) {
    		$content = 0;
    		$data = array();
    		
    		foreach ($hits->facet_counts->facet_fields->regulationType as $facet => $count)
    		{
    			if ($count == 0 || in_array($facet, array('0')))
    			{
    				continue;
    			}
    			else
    			{
    				$data[$content]['profile'] = $facet;
    				$data[$content]['count'] = $count;
    			}
    		
    			$content++;
    		}
    		
    		$this->view->assign('aData', $data);
    		$this->view->assign('query', urlencode($wq));
    		$this->view->assign('regulationType', $regulationType);
    	}
    }
    
    public function facetcatclinicAction()
    {
    	$request = $this->getRequest();
    	$q = $wq = $request->getParam('q');
    	$kategoriklinik = $request->getParam('kategoriklinik');
    	$zl = Zend_Registry::get('Zend_Locale');
    	if (($q) && ($zl->getLanguage() !== 'ha')) {
    		if(!preg_match("/(id:|shortTitle:|profile:|publishedDate:|expiredDate:|createdDate:|modifiedDate:|createdBy:|modifiedBy:|status:|author:|fixedDate:|regulationType:|regulationOrder:|kategori:|kategoriklinik:|kontributor:|sumber:|year:|number:|title:)/i", $q))
    		{
    			require_once( 'Apache/Solr/Service.php' );
    			$q = Apache_Solr_Service::escape($q);
    		}
    		
    		$query = $q;
    	}
    	else
    	{
    		$query = "";
    	}
    	
    	$query = str_replace('\\', '', $query);
    	
    	$indexingEngine = Pandamp_Search::manager();
    	
    	$hits = $indexingEngine->find($query,0,1);
    	
    	if (isset($hits->response->docs[0])) {
    		$content = 0;
    		$data = array();
    		
    		foreach ($hits->facet_counts->facet_fields->kategoriklinik as $facet => $count)
    		{
    			if ($count == 0)
    			{
    				continue;
    			}
    			else
    			{
    				$data[$content]['profile'] = $facet;
    				$data[$content]['count'] = $count;
    			}
    		
    			$content++;
    		}
    		
    		$this->view->assign('aData', $data);
    		$this->view->assign('query', urlencode($wq));
    		$this->view->assign('kategoriklinik', $kategoriklinik);
    	}
    }
    
    public function facetprofileAction()
    {
    	$request = $this->getRequest();
    	$q = $wq = $request->getParam('q');
    	$category = $request->getParam('category');
    	$clinic_selected = $request->getParam('clinic_selected');
    	$putusanselected = $request->getParam('putusanSelected');
    	
    	if ($q) {
    		if(!preg_match("/(id:|shortTitle:|profile:|publishedDate:|expiredDate:|createdDate:|modifiedDate:|createdBy:|modifiedBy:|status:|author:|fixedDate:|regulationType:|regulationOrder:|kategori:|kategoriklinik:|kontributor:|sumber:|year:|number:|title:)/i", $q))
    		{
    			require_once( 'Apache/Solr/Service.php' );
    			$q = Apache_Solr_Service::escape($q);
    		}
    		
    		$query = $q;
    	}
    	else
    	{
    		$query = "";
    	}
    	
    	$query = str_replace('\\', '', $query);
    	
    	$indexingEngine = Pandamp_Search::manager();
    	
    	$hits = $indexingEngine->find($query,0,1);
    	
    	if (isset($hits->response->docs[0])) {
    		$content = 0;
    		$data = array();
    		
    		foreach ($hits->facet_counts->facet_fields->profile as $facet => $count)
    		{
    			if ($count == 0 || in_array($facet, array('comment','partner','kategoriklinik','kutu_signup')))
    			{
    				continue;
    			}
    			else
    			{
    				$f = str_replace(array('kutu_'), "", $facet);
    				$f = str_replace("peraturan_kolonial","peraturan kolonial",$f);
    				$f = str_replace("rancangan_peraturan","rancangan peraturan",$f);
    				$f = str_replace("about_us","tentang kami",$f);
    				$f = str_replace("kotik","kode etik jurnalis",$f);
    				$f = str_replace("kategoriklinik","kategori klinik",$f);
    				$f = str_replace("mitra","mitra hukumonline",$f);
    				$f = str_replace("partner","mitra klinik",$f);
    				$f = str_replace("author","penjawab klinik",$f);
    				//$f = str_replace("article","artikel",$f);
    				$f = str_replace("comment","komentar",$f);
    				$f = str_replace("doc","pelengkap",$f);
    				$f = str_replace("signup","pendaftaran",$f);
    				$f = str_replace("financial_services","financial services",$f);
    				$f = str_replace("general_corporate","general corporate",$f);
    				$f = str_replace("oil_and_gas","oil and gas",$f);
    				$f = str_replace("executive_alert","executive alert",$f);
    				$f = str_replace("manufacturing_&_industry","manufacturing & industry",$f);
    				$f = str_replace("consumer_goods","consumer goods",$f);
    				$f = str_replace("telecommunications_and_media","telecommunications and media",$f);
    				$f = str_replace("executive_summary","executive summary",$f);
    				$f = str_replace("hot_news","hot news",$f);
    				$f = str_replace("hot_issue_ile","hot issue ILE",$f);
    				$f = str_replace("hot_issue_ild","hot issue ILD",$f);
    				$f = str_replace("hot_issue_ilb","hot issue ILB",$f);
    				
    				$data[$content]['facet'] = $f;
    				$data[$content]['profile'] = $facet;
    				$data[$content]['count'] = $count;
    			}
    			
    			$content++;
    		}
    		
    		$this->view->assign('aData', $data);
    		$this->view->assign('category', $category);
    		$this->view->assign('clinic_selected', $clinic_selected);
    		$this->view->assign('putusanSelected', $putusanselected);
    		$this->view->assign('query', urlencode($wq));
    	}
    }
    
    function _browseAction()
    {
        $r = $this->getRequest();
        $sOffset = ($r->getParam('sOffset'))? $r->getParam('sOffset') : 0;
        $this->view->sOffset = $sOffset;
        $sLimit = ($r->getParam('sLimit'))? $r->getParam('sLimit') : 0;
        $this->view->sLimit = $sLimit;
        
        $category = ($r->getParam('category'))? $r->getParam('category') : '';
        
		if ($category=="all")
		$category="";
		

        $query = ($r->getParam('q'))? $r->getParam('q') : '';

        $indexingEngine = Pandamp_Search::manager();
        if(empty($query)) {
            //$hits = $indexingEngine->find("*:*;publishedDate desc",$sOffset, $sLimit);
            $hits = $indexingEngine->find("*:*",$sOffset, $sLimit);
        } else {
        	
        	if ($category)
        	{
        		$querySolr = $query . ' profile:'.$category;
        	}
        	else 
        	{
	            //$hits = $indexingEngine->find($query." -profile:kutu_doc;publishedDate desc",$sOffset, $sLimit);
	            $querySolr = $query." -profile:kutu_doc";
        	}
        	
        	$hits = $indexingEngine->find($querySolr, $sOffset, $sLimit);
        }
        
        $solrNumFound = count($hits->response->docs);

        $content = 0;
        $data = array();

        for($ii=0;$ii<$solrNumFound;$ii++) {
                $row = $hits->response->docs[$ii];
                $data[$content][0] = $row->id;
                $data[$content][1] = (isset($row->title))? $row->title : '';

                $tblCatalogFolder = new App_Model_Db_Table_CatalogFolder();
                $rowsetCatalogFolder = $tblCatalogFolder->fetchRow("catalogGuid='$row->id'");
                if ($rowsetCatalogFolder)
                    $parentGuid= $rowsetCatalogFolder->folderGuid;
                else
                    $parentGuid='';

                $data[$content][2] = $parentGuid;
                $profileSolr = str_replace("kutu_", "", $row->profile);
                $data[$content][3] = $profileSolr;
                $data[$content][4] = $row->createdDate;
                $data[$content][5] = $row->createdBy;
                $data[$content][6] = $row->modifiedDate;
                $data[$content][7] = $row->modifiedBy;
                $data[$content][8] = $row->publishedDate;
                $data[$content][9] = $row->status;

                $content++;
        }

        switch ($category)
        {
			case "kutu_peraturan":
            case "kutu_rancangan_peraturan":
            case "kutu_peraturan_kolonial":
				$ct = "(kutu_peraturan_kolonial OR kutu_rancangan_peraturan OR kutu_peraturan)";  
				break;
			case "":
                $ct = "all";
                break;	
            default :
                $ct = $category;
                break;
        }

		$this->_helper->layout()->categorySearchQuery = $ct;

        $num_rows = $solrNumFound;

        $this->view->query = $query;
        
        $this->view->totalItems = $hits->response->numFound;
        $this->view->numberOfRows = $num_rows;
        $this->view->data = $data;
    }
    
    /**
     * Find images
     */
    public function findimageAction()
    {
    	$request   = $this->getRequest();
    	$pageIndex = $request->getParam('pageIndex', 1);
    	$perPage   = $request->getParam('perpage');
    	$perPage   = ($perPage)? $perPage : 20;
    	$offset    = ($pageIndex - 1) * $perPage;
    
    	$params = null;
    	$exp 	= array();
    
    	$params = $request->getParam('q');
    	if (null != $params) {
    		$exp = rawurldecode(base64_decode($params));
    		$exp = Zend_Json::decode($exp);
    	}
    	else
    	{
    		$params = rawurlencode(base64_encode(Zend_Json::encode($exp)));
    	}
    
    	$indexingEngine = Pandamp_Search::manager();
    
    	if ($exp['keyword'] == '*' || $exp['keyword'] == '') {
    		$exp['keyword'] = '*:*';
    	}
    
    	$hits = $indexingEngine->find($exp['keyword'].' mimeType:image* profile:kutu_doc', $offset, $perPage,'createdDate desc');
    	$solrNumFound = count($hits->response->docs);
    
    	$num_rows = $hits->response->numFound;
    
    	$paginator = Zend_Paginator::factory($num_rows);
    	$cache = Pandamp_Cache::getInstance();
    	if ($cache) {
    		Zend_Paginator::setCache($cache);
    	}
    	$paginator->setCacheEnabled(true);
    
    	$paginator->setCurrentPageNumber($pageIndex);
    	$paginator->setItemCountPerPage($perPage);
    
    	$paginator = get_object_vars($paginator->getPages('Sliding'));
    
    	$paginatorOptions = array(
   			'path' 	   => $this->view->url(array('lang'=>$this->view->getLanguage()), 'search_catalog_findimage'),
   			'itemLink' => (null == $params) ? 'page-%d' : 'page-%d?perpage='.$perPage.'&q=' . $params,
    	);
    
    	/**
    	 * Support searching from other page
    	 * For example, search files at adding set page
    	*/
    	if (isset($exp['format']) && $exp['format'] == 'JSON') {
    		$this->_helper->getHelper('viewRenderer')->setNoRender();
    		$this->_helper->getHelper('layout')->disableLayout();
    			
    		$config = Pandamp_Application::getOption('cdn');
    			
    		if($solrNumFound>$perPage)
    			$numRowset = $perPage ;
    		else
    			$numRowset = $solrNumFound;
    			
    		$res = array(
   				'files' 	=> array(),
   				'paginator' => $this->view->paginator()->slide($paginator, $paginatorOptions),
    		);
    		for ($i=0;$i<$numRowset;$i++) {
    			$row = $hits->response->docs[$i];
    			$fs = 'thumbnail_';
    			//$filename = $row->systemName; <-- metode ini kadang suka kosong
    			$filename = $this->view->getCatalogAttribute($row->id,'docSystemName');
				$fn = pathinfo($filename,PATHINFO_FILENAME);
   				$ext = pathinfo($filename,PATHINFO_EXTENSION);
   				//$ext = strtolower($ext);
   				if (substr($fn,0,2) !== 'lt') {
   					$fn = $row->id;
   					$fs = 'tn_';
   					$filename = $fn.'.'.strtolower($ext);
   				}
   				$title = $this->view->getCatalogAttribute($fn,'fixedTitle');
   				$relDb = new App_Model_Db_Table_RelatedItem();
   				$rel = $relDb->fetchRow("itemGuid='".$fn."' AND relateAs='RELATED_IMAGE'");
   				$relGuid = (isset($rel->relatedGuid))?$rel->relatedGuid:'';
   				if (is_array(@getimagesize($config['static']['url']['images'].'/'.$relGuid.'/'.$fs.$filename)))
   					$url = $config['static']['url']['images'].'/'.$relGuid.'/'.$fs.$filename;
   				elseif (is_array(@getimagesize($config['static']['url']['images'].'/'.$fs.$filename)))
   					$url = $config['static']['url']['images'].'/'.$fs.$filename;
   				else 
   					$url = 'http://static.hukumonline.com/frontend/default/images/kaze/karticle-img.jpg';
   				   				
   				//$url = $config['static']['url']['images'].'/'.$rel->relatedGuid.'/'.$rel->itemGuid.'.'.strtolower($ext);
   				//$url = $config['static']['url']['images'].'/upload/'.$pd1.'/'.$pd2.'/'.$pd3.'/'.$pd4.'/'.$fn.'_square'.'.'.$ext;
   				$res['files'][] = array(
					'id' 			=> (isset($fn))?$fn:'',
					'relatedGuid' 	=> (isset($relGuid))?$relGuid:'',
					'title' 		=> (isset($title))?$title:'',
					'url'   		=> (isset($url))?$url:''
 				);
    
    				
    
    		}
    			
    		$this->getResponse()->setBody(Zend_Json::encode($res));
    	}
    
    }
}
