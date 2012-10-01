<?php

abstract class Pandamp_Controller_Datatable extends Zend_Controller_Action {
	public function init()
	{
		parent::init();
		
		/**
		 * Setup which actions are ajax actions
		 */
		$ajaxContext = $this->_helper->getHelper('AjaxContext');
		$ajaxContext->addActionContext('datasource', 'json')
					->setAutoJsonSerialization(false)
					->initContext();
					
        /**
         * Add variables needed in view.
         */
        $this->assignOptions2View();
	}
	
	/**
	 * Assign datatable options so we can use them in the view.
	 * 
	 * @return void
	 */
	protected function assignOptions2View($options = null) {
		if (!is_null($options)) {
			$this->view->datatableOptions = $options;
		}
		else 
		{
			$this->view->datatableOptions = $this->getDatatableOptions();
		}
		
		$this->view->controller = $this->getRequest()->getControllerName();
		$this->view->module = $this->getRequest()->getModuleName();
	}
	
	/**
	 * Options to use for datatable instance.
	 */
	public function getDatatableOptions () {
		$controller = $this->getRequest()->getControllerName();
		$options = new Zend_Config_Xml($this->getFrontController()->getModuleDirectory().'/configs/'.$controller.'.xml', APPLICATION_ENV);
		
		return $options->toArray();
	}
	
	public function dcAction()
	{
		die('in');
	}
}