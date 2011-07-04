<?php

/**
 * Description of ErrorController
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Admin_ErrorController extends Zend_Controller_Action
{
    function  preDispatch()
    {
        $this->_helper->layout->setLayout('administry');
    }
    function restrictedAction()
    {
    }
    function temporaryAction() {}
}
