<?php
class Pandamp_Controller_Action_Helper_GetHistoryNew
{
	public function getHistoryNew($catalogGuid)
	{
		$bpm = new Pandamp_Core_Hol_Relation();
		$history = $bpm->getHistory2($catalogGuid);
		
		return $history;
	}
}