<?php

/**
 * Description of ManagerController
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Customer_ManagerController extends Zend_Controller_Action
{
    protected $_conn;

    function  preDispatch() 
    {
        $this->_conn = Zend_Db::factory('Pdo_Mysql', array(
             'host'     => 'localhost'
            ,'username' => 'root'
            ,'password' => ''
            ,'dbname'   => 'hid'
        ));
        
    }
    function checkusernameAction()
    {
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $username = ($this->_getParam('username'))? $this->_getParam('username') : '';

        $this->_conn->setFetchMode(Zend_Db::FETCH_OBJ);

        $query = "SELECT username FROM KutuUser WHERE username='$username'";
        $rowset = $this->_conn->fetchRow($query);
        
        if($rowset)
            $valid = 'false';
        else
            $valid = 'true';
        
        echo $valid;
        die();
    }
    function checkcompanyAction()
    {
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $company = ($this->_getParam('company'))? $this->_getParam('company') : '';

        $this->_conn->setFetchMode(Zend_Db::FETCH_OBJ);

        $query = "SELECT company FROM KutuUser WHERE company='$company'";
        $rowset = $this->_conn->fetchRow($query);

        if($rowset)
            $valid = 'false';
        else
            $valid = 'true';

        echo $valid;
        die();
    }
}
