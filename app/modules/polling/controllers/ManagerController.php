<?php

/**
 * Description of ManagerController
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Polling_ManagerController extends Zend_Controller_Action
{
    function viewAction()
    {
        $time = time();
        $date = date("Y-m-d H:i:s", $time);

        $rowPoll = App_Model_Show_Poll::show()->getPollByDate($date);
        $this->view->rowPoll = $rowPoll;

        $rowOpt = App_Model_Show_PollOption::show()->getPollOption($rowPoll['guid']);
        $this->view->rowOpt = $rowOpt;
    }
}
