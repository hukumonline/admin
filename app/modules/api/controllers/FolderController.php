<?php

/**
 * Description of FolderController
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Api_FolderController extends Zend_Controller_Action
{
	protected $failCatalog;
	
	public function getchildreninjson2Action()
	{
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		 
		// Make sure nothing is cached
		header("Cache-Control: must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
		header("Expires: ".gmdate("D, d M Y H:i:s", mktime(date("H")-2, date("i"), date("s"), date("m"), date("d"), date("Y")))." GMT");
		header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
		header('Content-Type: application/json');
	
		sleep(1);
	
		$acl = Pandamp_Acl::manager();
	
		$auth = Zend_Auth::getInstance();
		if (!$auth->hasIdentity()) {
			echo "You aren't login";
		}
	
		$identity = $auth->getIdentity();
	
		$username = $identity->username;
	
		$id = ($_REQUEST["id"]=="#")?"root":$_REQUEST["id"];
	
		$aJson = array();
	
		$folder = new App_Model_Db_Table_Folder();
		$rowset = $folder->fetchChildren($id);
	
		$i=0;
		foreach ($rowset as $row) {
			if (($identity->name == "Master") || ($identity->name == "Super Admin"))
				$content = 'all-access';
			else
				$content = $row->type;
			 
			if ($acl->getPermissionsOnContent('', $identity->name, $content))
			{
				if ($row->title == "Kategori" || $row->title == "Peraturan" || $row->title == "Putusan")
				{
					$title = "<font color=red><b>".$row->title."</b></font>";
				}
				else
				{
					$title = $row->title;
				}
				 
				$aJson[$i]['id'] = $row->guid;
				$aJson[$i]['text'] = $title;
				$checkLeaf = $folder->fetchAll("path like '%$row->guid/%'");
				if ($checkLeaf->count() > 0)
				{
					$aJson[$i]['children'] = true;
				}
				else
				{
					$aJson[$i]['children'] = false;
				}
	
			}
			else
			{
				continue;
			}
			 
			$i++;
		}
		 
		$this->_response->setBody(Zend_Json::encode($aJson));
	}
	
    public function getchildreninjsonAction()
    {
        // Make sure nothing is cached
        header("Cache-Control: must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        header("Expires: ".gmdate("D, d M Y H:i:s", mktime(date("H")-2, date("i"), date("s"), date("m"), date("d"), date("Y")))." GMT");
        header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");

        // So that the loading indicator is visible
        sleep(1);
        
        $acl = Pandamp_Acl::manager();

        $auth = Zend_Auth::getInstance();
        if (!$auth->hasIdentity()) {
            echo "You aren't login";
        }
        
        $identity = $auth->getIdentity();
        
        $packageId = $identity->packageId;
        
        $username = $identity->username;

		$aReturn = App_Model_Show_AroGroup::show()->getUserGroup($packageId);
		
        // The id of the node being opened
        $id = $_REQUEST["id"];

        if($id == "0") {

            $rowset = App_Model_Show_Folder::show()->fetchChildren('root');
            echo '['."\n";
            for($i=0;$i<count($rowset);$i++)
            {
				if (($aReturn['name'] == "Master") || ($aReturn['name'] == "Super Admin"))
					$content = 'all-access';
				else 
					$content = $rowset[$i]['type'];

				if ($acl->getPermissionsOnContent('', $aReturn['name'], $content))
				{																
					if ($rowset[$i]['title'] == "Kategori" || $rowset[$i]['title'] == "Peraturan" || $rowset[$i]['title'] == "Putusan")
					{
						$title = "<font color=red><b>".$rowset[$i]['title']."</b></font>";
					}
					else 
					{
						$title = $rowset[$i]['title'];
					}
					
	                if($i==(count($rowset)-1))
	                {
	                    $tree = "\t".'{ attributes: { id : "'.$rowset[$i]['guid'].'" }, state: "closed", data: "'.$title.'" }'."\n";
	                } else {
	                    $tree = "\t".'{ attributes: { id : "'.$rowset[$i]['guid'].'" }, state: "closed", data: "'.$title.'" },'."\n";
	                }
	                
					echo $tree;
				}
				else 
				{
					continue;
				}
            }
            echo ']'."\n";
        }
        else {

            $rowset = App_Model_Show_Folder::show()->fetchChildren($id);
            echo '['."\n";
            for($i=0;$i<count($rowset);$i++)
            {
                if($i==(count($rowset)-1))
                        echo "\t".'{ attributes: { id : "'.$rowset[$i]['guid'].'" }, state: "closed", data: "'.$rowset[$i]['title'].'" }'."\n";
                    else
                        echo "\t".'{ attributes: { id : "'.$rowset[$i]['guid'].'" }, state: "closed", data: "'.$rowset[$i]['title'].'" },'."\n";

            }
            echo ']'."\n";
        }
        exit();
    }

    public function downloadfileAction()
    {
    	$this->_helper->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender();
    	 
    	$request = $this->getRequest();
    	 
    	$folderGuid = $request->getParam('folderGuid');
    	 
    	$space="&nbsp;&nbsp;&nbsp;";
    	$source="";
    	$target=ROOT_DIR.'/temp';
    	$tgtPath=$target."/DownloadFile";
    	if (!is_dir($tgtPath)) {
    		if (!mkdir($tgtPath, 0777, true)) {
    			die('Failed to create folders...'.$tgtPath);
    		}
    	}
    
    	$this->fetchFolder($folderGuid,$space,$source,$tgtPath);
    	 
    	echo '<pre>';
    	print_r($this->failCatalog);
    	echo '</pre>';
    }
    
    private function fetchFolder($folderGuid,$space,$source,$target)
    {
    	$folderDb = new App_Model_Db_Table_Folder();
    	$folder = $folderDb->fetchRow("guid LIKE \"".$folderGuid."\"");
    	$message=$space." - ".$folder->title." ( ".$folder->guid." ) <br>";
    	$this->printMessage($message,"red","bold");
    	 
    	$tgtPath=$target."/".$folder->title;
    	$tgtPath=rtrim($tgtPath);
    	if (!is_dir($tgtPath)) {
    		if (!mkdir($tgtPath, 0777, true)) {
    			die('Failed to create folders...['.$tgtPath."]");
    		}
    	}
    	 
    	$this->copycatalog($folderGuid,$space."&nbsp;&nbsp;&nbsp;&nbsp;",$source,$tgtPath);
    	 
    	$folders = $folderDb->fetchChildren($folderGuid);
    	if(count($folders)>0)
    	{
    		foreach ($folders as $data )
    		{
    			echo "<br>";
    			$this->fetchFolder($data->guid, $space."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;",$source,$tgtPath);
    		}
    	}
    }
    
    private function copycatalog($folderGuid,$space,$source,$target)
    {
    	$source="http://files.hukumonline.com/uploads/files";
    	 
    	$catalogDb = new App_Model_Db_Table_Catalog();
    	$catalogs = $catalogDb->fetchCatalogInFolder($folderGuid);
    	$count=1;
    	foreach ($catalogs as $data)
    	{
    		$catalogs0 = $catalogDb->getCatalogByGuid($data->guid);
    		$rowsetCatalogAttribute0 = $catalogs0->findDependentRowsetCatalogAttribute();
    		$fixedTitle0 = $rowsetCatalogAttribute0->findByAttributeGuid('fixedTitle');
    		$message= $space.$count." > ".$data->guid." ( ".$fixedTitle0['value']." )<br>";
    		$this->printMessage($message,"","");
    
    		$count++;
    
    		$relatedItemDb = new App_Model_Db_Table_RelatedItem();
    		$filesCatalog = $relatedItemDb->fetchDocumentById($data->guid, "RELATED_FILE");
    
    		$count1=1;
    		foreach ($filesCatalog as $data1)
    		{
    			$catalogs1 = $catalogDb->getCatalogByGuid($data1->itemGuid);
    			$rowsetCatalogAttribute = $catalogs1->findDependentRowsetCatalogAttribute();
    			 
    			$docSystemName = $rowsetCatalogAttribute->findByAttributeGuid('docSystemName');
    			$docSystemName=$docSystemName['value'];
    			 
    			$docOriginalName = $rowsetCatalogAttribute->findByAttributeGuid('docOriginalName');
    			$docOriginalName=$docOriginalName['value'];
    			 
    			$success=false;
    			 
    			$sDir1=$source."/".$docSystemName;
    			$sDir2=$source."/".$data->guid."/".$docSystemName;
    			$sDir3=$source."/".$docOriginalName;
    			$sDir4=$source."/".$data->guid."/".$docOriginalName;
    			 
    			if($this->url_exists($sDir1))
    			{
    				$url = $sDir1;
    				$outputfile = $target.'/'.$docOriginalName;
    
    				$success=true;
    				echo $space."&nbsp;&nbsp;&nbsp;96 -> sDir1 : ".$sDir1."<br>";
    				echo $space."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\$docOriginalName : ".$docOriginalName."<br>";
    				//exec('wget -O '.$docOriginalName.' -P '.$sDir1);
    				$cmd = "/usr/bin/wget -O \"$outputfile\" \"$url\"";
    				exec($cmd);
    			}
    			else if($this->url_exists($sDir2))
    			{
    				$url = $sDir2;
    				$outputfile = $target.'/'.$docOriginalName;
    				//$cmd = "wget -O - -P \"$url\" > $outputfile";
    				$cmd = "/usr/bin/wget -O \"$outputfile\" \"$url\"";
    
    
    
    				/*set_time_limit(0);
    				 $file = file_get_contents($url);
    				file_put_contents($outputfile, $file);*/
    
    
    
    				//exec('wget -O '.$docOriginalName.' -P '.$sDir2);
    				//exec('wget -O - -P '.$sDir2.' > '.$docOriginalName);
    				exec($cmd);
    				$success=true;echo $space."&nbsp;&nbsp;&nbsp;96 -> sDir2 : ".$sDir2."<br>";
    				echo $space."\$docOriginalName : ".$docOriginalName."<br>";
    			}
    			else if($this->url_exists($sDir3))
    			{
    				$url = $sDir3;
    				$outputfile = $target.'/'.$docOriginalName;
    				$cmd = "/usr/bin/wget -O \"$outputfile\" \"$url\"";
    
    				$success=true;
    				echo $space."&nbsp;&nbsp;&nbsp;96 -> sDir3 : ".$sDir3."<br>";
    				//exec('wget -O '.$docOriginalName.' -P '.$sDir3);
    				exec($cmd);
    				echo $space."\$docOriginalName : ".$docOriginalName."<br>";
    			}
    			else if($this->url_exists($sDir4))
    			{
    				$url = $sDir4;
    				$outputfile = $target.'/'.$docOriginalName;
    				$cmd = "/usr/bin/wget -O \"$outputfile\" \"$url\"";
    
    				$success=true;
    				echo $space."&nbsp;&nbsp;&nbsp;96 -> sDir4 : ".$sDir4."<br>";
    				//exec('wget -O '.$docOriginalName.' -P '.$sDir4);
    				exec($cmd);
    			}
    			else
    			{
    				$this->failCatalog[]=$data->guid." ( ".$fixedTitle0['value']." )";
    			}
    							 
    			$count1++;
    		}
    
    	}
     
    }
    
	private function printMessage($message,$font,$weight)
	{
   		$ffont="";
   		if($font!=NULL) $ffont="color:".$font.";";
    
		$fweight="";
		if($weight!=NULL) $fweight="font-weight:".$weight.";";
    
		echo'<font style="'.$ffont.$fweight.'">'.$message.'</font>';
	}
	
	private function url_exists($url)
	{
		if (@fopen($url,"r"))
			return true ;
		else
			return false;
	}
}
