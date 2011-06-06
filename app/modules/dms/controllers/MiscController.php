<?php

/**
 * Description of MiscController
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Dms_MiscController extends Zend_Controller_Action
{
    function rightdownAction()
    {
        $sReturn = "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
        $this->view->urlReferer = $sReturn;

        $r = $this->getRequest();
        $node = ($r->getParam('node')?$r->getParam('node'):'root');
        $this->view->currentNode = $node;
    }
}
