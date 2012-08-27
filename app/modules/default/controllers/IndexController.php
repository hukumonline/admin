<?php

class IndexController extends Zend_Controller_Action  
{
	function init()
	{
            $this->_helper->viewRenderer->setNoRender(TRUE);         
	}
	public function __call($method, $args)
	{
		/*
            $tw = Zend_Registry::get('twurfl');
            if(!$tw->getDeviceCapability("is_wireless_device")){
                $this->_forward('index','index','admin');
            }
            else
            {
            	echo "MOBILE";
                //$this->_forward('index','index','msite');
            }
		*/
	}
}

