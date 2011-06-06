<?php
class Dms_Menu_ViewFolder
{
    private $_node;
    public $view;

    public function __construct($node='root')
    {
        $this->view = new Zend_View();
        $this->view->setScriptPath(dirname(__FILE__));

        $this->view->addHelperPath(ROOT_DIR.'/library/Pandamp/Controller/Action/Helper', 'Pandamp_Controller_Action_Helper');
        $this->_node = $node;

        $this->viewFolderKu();
    }

    public function render()
    {
        return $this->view->render(strtolower('viewfolder.phtml'));
    }

    function viewFolderKu()
    {
        $time_start = microtime(true);

        $parentGuid = $this->_node;

        $columns = 4;

        $tblFolder = new App_Model_Db_Table_Folder();
        $rowsetFolder = App_Model_Show_Folder::show()->fetchChildren($parentGuid);

        $num_rows = count($rowsetFolder);
        $rows = ceil($num_rows / $columns);

        if($num_rows < $columns)
                $columns = $num_rows;
        if($num_rows==0)
        {
        }

        $in = 0;
        $data = array();
        foreach ($rowsetFolder as $rowFolder)
        {
            $data[$in][0] = $rowFolder['title'];
            $data[$in][1] = $rowFolder['description'];
            $data[$in][2] = $rowFolder['guid'];
            $data[$in][3] = '';
            $in++;
        }

        $this->view->rows = $rows;
        $this->view->columns = $columns;
        $this->view->data = $data;
        $this->view->numberOfFolders = $num_rows;
        $this->view->node = $parentGuid;

        if($parentGuid!='root')
        {
            $rowCurrentNode = $tblFolder->find($parentGuid)->current();
            $this->view->currentNodeTitle = $rowCurrentNode->title;
        }
        else
        {
            $this->view->currentNodeTitle = 'ROOT';
        }

        $time_end = microtime(true);
        $time = $time_end - $time_start;
    }
}