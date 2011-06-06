<?php

/**
 * Description of ManagerController
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Comment_ManagerController extends Zend_Controller_Action
{
    function viewAction()
    {
        $r = $this->getRequest();

        $catalogGuid = $r->getParam('guid');
        $folderGuid = $r->getParam('node');

        $rowSumComment = App_Model_Show_Comment::show()->getParentCommentCount($catalogGuid);

        $this->view->sumComment = ($rowSumComment > 1)? $rowSumComment.'&nbsp;comments' : $rowSumComment.'&nbsp;comment';
        $this->view->numOfRows = $rowSumComment;
        $this->view->catalogGuid = $catalogGuid;
        $this->view->node = $folderGuid;

    }
}
