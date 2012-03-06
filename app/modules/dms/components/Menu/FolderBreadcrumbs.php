<?php

/**
 * Description of FolderBreadcrumbs
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Dms_Menu_FolderBreadcrumbs
{
    public $view;
    public $folderGuid;
    public $rootGuid;

    public function __construct($folderGuid, $rootGuid='')
    {
        $this->view = new Zend_View();
        $this->view->setScriptPath(dirname(__FILE__));

        $this->folderGuid = $folderGuid;
        $this->rootGuid = $rootGuid;

        $this->view();
    }
    public function view()
    {
        $zl = Zend_Registry::get("Zend_Locale");

        $browserUrl = ROOT_URL . '/'.$zl->getLanguage().'/dms/explorer/browse/node';

        $folderGuid = ($this->folderGuid)? $this->folderGuid : 'root';

        $tblFolder = new App_Model_Db_Table_Folder();

        $aPath = array();

        if($folderGuid == 'root')
        {
            $aPath[0]['title'] = 'Root';
            $aPath[0]['url'] = $browserUrl;
        }
        else
        {
            $rowFolder = $tblFolder->find($folderGuid)->current();
            if(!empty($rowFolder->path))
            {
                $aFolderGuid = explode("/", $rowFolder->path);
                $sPath = 'root >';
                $aPath[0]['title'] = 'Root';
                $aPath[0]['url'] = $browserUrl;
                $i = 1;
                if(count($aFolderGuid))
                {
                    $sPath1 = '';
                    foreach ($aFolderGuid as $guid)
                    {
                        if(!empty($guid))
                        {
                            $rowFolder1 = $tblFolder->find($guid)->current();
                            $sPath1 .= $rowFolder1->title . ' > ';
                            $aPath[$i]['title'] = $rowFolder1->title;
                            $aPath[$i]['url'] = $browserUrl.'/'.$rowFolder1->guid;
                            $i++;
                        }
                    }

                    $aPath[$i]['title'] = $rowFolder->title;
                    
                    if ($rowFolder->title == 'Published') {
                    	$aPath[$i]['url'] = ROOT_URL.'/'.$zl->getLanguage().'/dms/clinic/browse/status/99/node/'.$rowFolder->guid;
                    }
                    else if ($rowFolder->title == 'Selected') {
                    	$aPath[$i]['url'] = ROOT_URL.'/'.$zl->getLanguage().'/dms/clinic/browse/status/9/node/'.$rowFolder->guid;
                    }
                    else if ($rowFolder->title == 'NA') {
                    	$aPath[$i]['url'] = ROOT_URL.'/'.$zl->getLanguage().'/dms/clinic/browse/status/2/node/'.$rowFolder->guid;
                    }
                    else if ($rowFolder->title == 'Draft') {
                    	$aPath[$i]['url'] = ROOT_URL.'/'.$zl->getLanguage().'/dms/clinic/browse/status/0/node/'.$rowFolder->guid;
                    }
                    else if ($rowFolder->title == 'Approved') {
                    	$aPath[$i]['url'] = ROOT_URL.'/'.$zl->getLanguage().'/dms/clinic/browse/status/1/node/'.$rowFolder->guid;
                    }
                    else 
                    {
                    	$aPath[$i]['url'] = $browserUrl.'/'.$rowFolder->guid;
                    }
                }

            }
            else
            {
                $aPath[0]['title'] = 'Root';
                $aPath[0]['url'] = $browserUrl;
                $aPath[1]['title'] = $rowFolder->title;
                $aPath[1]['url'] = $browserUrl.'/'.$rowFolder->guid;
            }
    }
    $this->view->aPath = $aPath;
    }
    public function render()
    {
        return $this->view->render(str_replace('.php','.phtml',strtolower(basename(__FILE__))));
    }
}
