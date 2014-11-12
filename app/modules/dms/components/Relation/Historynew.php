<?php
class Dms_Relation_Historynew
{
	public $view;
	public $catalogGuid;

	public function __construct($catalogGuid, $folderGuid)
	{
		$this->view = new Zend_View();
		$this->view->setScriptPath(dirname(__FILE__));

		$this->catalogGuid = $catalogGuid;

		$this->view->addHelperPath(ROOT_DIR.'/library/Pandamp/Controller/Action/Helper', 'Pandamp_Controller_Action_Helper');

		$bpm = new Pandamp_Core_Hol_Relation();
		$this->view->aData = $bpm->getHistorynew($catalogGuid,$folderGuid);

		$this->view->catalogGuid = $catalogGuid;
		$this->view->folderGuid = $folderGuid;
	}
	public function render()
	{
		$aName = explode('_', basename(__FILE__));

		return $this->view->render(str_replace('.php','.phtml',strtolower(basename(__FILE__))));
	}

}