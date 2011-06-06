<?php

/**
 * Description of FolderController
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Api_FolderController extends Zend_Controller_Action
{
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

        // The id of the node being opened
        $id = $_REQUEST["id"];

        if($id == "0") {

            $rowset = App_Model_Show_Folder::show()->fetchChildren('root');
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
