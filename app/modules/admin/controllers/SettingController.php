<?php

/**
 * Description of SettingController
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Admin_SettingController extends Zend_Controller_Action
{
    const CONTEXT_JSON = 'json';
    public function init()
    {
    	$contextSwitch = $this->_helper->contextSwitch();

        $contextSwitch->addActionContext('change-status',self::CONTEXT_JSON)
                        ->addActionContext('cekstatus',self::CONTEXT_JSON)
                        ->initContext();
    }
    function cekstatusAction()
    {
        $tblSetting = new App_Model_Db_Table_Setting();
        $rowset = $tblSetting->find(1)->current();
        if ($rowset)
        {
            if ($rowset->status == 0) 
                $this->view->success = true;
            else
                $this->view->success = false;
        }
        else
        {
            $this->view->success = false;
        }
    }

    function changeStatusAction()
    {
        $status = ($this->_getParam('status'))? $this->_getParam('status') : '';

        switch ($status)
        {
            case 'online':
                $status = 0;
            break;
            case 'offline':
                $status = 1;
            break;
        }

        $tblSetting = new App_Model_Db_Table_Setting();
        $rowset = $tblSetting->find(1)->current();
        if ($rowset)
        {
            $rowset->status = $status;
            $rowset->save();
            $this->view->success = true;
        }
        else
        {
            $this->view->success = false;
        }
    }
}
