<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Document
 *
 * @author user
 */
class Dms_Relation_Dci
{
    public $view;
    public $catalogGuid;

    public function __construct($catalogGuid, $folderGuid, $start=0, $end=0)
    {
        $this->view = new Zend_View();
        $this->view->setScriptPath(dirname(__FILE__));

        $this->catalogGuid = $catalogGuid;


        $this->view->addHelperPath(ROOT_DIR.'/library/Pandamp/Controller/Action/Helper', 'Pandamp_Controller_Action_Helper');

        $settings['allowed_extensions'] = array();
        $settings['allowed_extensions'][] = 'jpg';
        $settings['allowed_extensions'][] = 'jpeg';
        $settings['allowed_extensions'][] = 'png';
        $settings['allowed_extensions'][] = 'gif';

        $settings['hidden_files'] = array();
        $settings['hidden_files'][] = 'Thumbs.db';
        $settings['hidden_files'][] = '.DS_Store';

	    $registry = Zend_Registry::getInstance();
	    $config = $registry->get(Pandamp_Keys::REGISTRY_APP_OBJECT);
	    $cdn = $config->getOption('cdn');
	    $dir = $cdn['static']['dir']['images'].DIRECTORY_SEPARATOR.$catalogGuid;
	    $dirUrl = $cdn['static']['url']['images'].DIRECTORY_SEPARATOR.$catalogGuid;
	    
	    $this->view->sdir = $dirUrl;
	    
        //$dir = ROOT_DIR."/uploads/images/$catalogGuid";

        if (is_dir($dir))
        {
            // open directory and parse file list
            if ($dh = opendir($dir)) {
                // iterate over file list & output all filenames
                while (($filename = readdir($dh)) !== false) {
                    $pinfo = pathinfo($filename);
                    if ((strpos($filename,"_") !== 0)
                    && (strpos($filename,".") !== 0)
                    && (strpos($filename,"lt") !== 0)
                    && (!in_array($filename, $settings['hidden_files']))
                    && (in_array(strToLower($pinfo["extension"]),$settings['allowed_extensions']))
                    ) {
                        $all_thumbs[] = $filename;
                    }
                }
                // close directory
                closedir($dh);
            }

            Zend_Session::sessionExists('cfg') ? Zend_Session::namespaceUnset('cfg') : '';
            
            $configGallery = new Zend_Session_Namespace("cfg");
            $configGallery->allThumbs = (isset($all_thumbs))? $all_thumbs : '';
            $configGallery->perPage = $start;

            $this->view->allThumbs = (isset($all_thumbs))? $all_thumbs : '';
            $this->view->page = $end;
            $this->view->perPage = $start;
            
        }

//        $bpm = new Pandamp_Core_Hol_Relation();
//        $fileImage = $bpm->getFilesImg($catalogGuid);
//
//        Zend_Controller_Action_HelperBroker::addPrefix('Pandamp_Controller_Action_Helper');
//        $docType = $this->view->getHelper('GetCatalogDocType');
//        $catalogTitle = $this->view->getHelper('getCatalogTitle');
//        $docSize = $this->view->getHelper('GetCatalogDocSize');
//
//        $columns = 4;
//        $content = 0;
//        $data = array();
//
//        foreach ($fileImage as $img)
//        {
//            $data[$content][0] = $docType->GetCatalogDocType($img->itemGuid, $img->relatedGuid);
//            $data[$content][1] = $catalogTitle->getCatalogTitle($img->itemGuid,'fixedTitle');
//            $data[$content][2] = $docSize->GetCatalogDocSize($img->itemGuid);
//            $data[$content][3] = $img->itemGuid;
//            $data[$content][4] = $img->relatedGuid;
//            $content++;
//        }
//
//        $num_rows = count($fileImage);
//        $rows = ceil($num_rows/$columns);
//
//        if ($num_rows < 2) {
//            $columns = $num_rows;
//        }
//        if ($num_rows == 0) {}
//
//        $this->view->numberOfRows = $num_rows;
//        $this->view->aData = $data;
//        $this->view->columns = $columns;
//        $this->view->rows = $rows;

        $this->view->catalogGuid = $catalogGuid;
        $this->view->folderGuid = $folderGuid;
    }
    public function render()
    {
        $aName = explode('_', basename(__FILE__));

        return $this->view->render(str_replace('.php','.phtml',strtolower(basename(__FILE__))));
    }
}
