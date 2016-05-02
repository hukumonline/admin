<?php

/**
 * Description of FolderController
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Api_FolderController extends Zend_Controller_Action
{
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
}
