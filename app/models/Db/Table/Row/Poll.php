<?php

class App_Model_Db_Table_Row_Poll extends Zend_Db_Table_Row_Abstract
{
	protected function _postDelete()
	{
		$tblPollIp = new App_Model_Db_Table_PollIp();
		$tblPollIp->delete("pollGuid='".$this->guid."'");
		
		$tblPollOpt = new App_Model_Db_Table_PollOption();
		$tblPollOpt->delete("pollGuid='".$this->guid."'");
	}
}

?>