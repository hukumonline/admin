<?php

/**
 * Description of FileuploaderController
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Api_FileuploaderController extends Zend_Controller_Action
{
    function saveAction()
    {
    	$this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);
        $r = $this->getRequest();
        $relatedGuid = $r->getParam('relatedGuid');
        if(empty($relatedGuid))
                throw new Zend_Exception("relatedGuid can not be empty!");

        if ($r->isPost())
        {
            $this->_save();
            echo "File successfully uploaded";
        }
    }
    function editAction()
    {
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(TRUE);
    	
		$r = $this->getRequest();
		$relatedGuid = $r->getParam('relatedGuid');
		
		if(empty($relatedGuid))
			throw new Zend_Exception("relatedGuid can not be empty!");
			
		if($r->isPost())
		{
			try {
				$aData = $r->getParams();
				$hol = new Pandamp_Core_Hol_Catalog();
				$hol->changeUploadFile($aData, $relatedGuid);
				echo "\nUpdate successfully";
			}
			catch (Exception $e)
			{
				echo $e->getMessage();
			}
		}
    }
    private function _save()
    {
        $hol = new Pandamp_Core_Hol_Catalog();
        $r = $this->getRequest();
        $aData = $r->getParams();

        $hol->uploadFile($aData, $aData['relatedGuid']);
    }
}
