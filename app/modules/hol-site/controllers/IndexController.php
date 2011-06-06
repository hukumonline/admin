<?php
class HolSite_IndexController extends Zend_Controller_Action
{
	public function init()
	{
		$this->_helper->cache(array('index'), array('entries'));
	}	
	function indexAction()
	{
	}
        function attackAction()
        {
            $this->_helper->layout->setLayout('administry');
        }
        function headerAction()
        {
            
        }
        function footerAction()
        {
            
        }
        function sidebarAction()
        {
            $this->_helper->viewRenderer->setNoRender(TRUE);
        }
}
?>