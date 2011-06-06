<?php

class Pandamp_Core_Hol_Poll
{
	function save($aData)
	{
            $title = ($aData['title'])? $aData['title'] : '';

            $tblPolling = new App_Model_Db_Table_Poll();
            $tblOption = new App_Model_Db_Table_PollOption();

            $newRow = $tblPolling->fetchNew();
            $newRow->title = $title;
            $newRow->checkedTime = date("Y-m-d H:i:s");

            $guid = $newRow->save();

            $uid = ($aData['id'])? $aData['id'] : '';

            $id = 1 + ($uid - 1);

            for ($j=1; $j <= $id; $j++) {
                $pollingopt = ($aData['polloption'.$j])? $aData['polloption'.$j] : '';
                if (empty($pollingopt))
                {
                    continue;
                }
                else
                {
                    $dataNewRow = $tblOption->fetchNew();
                    $dataNewRow->pollGuid = $guid;
                    $dataNewRow->text = $pollingopt;
                    $dataNewRow->hits = 0;
                    $dataNewRow->save();
                }
            }
	}
	function delete($pGuid)
	{
            $tblPolling = new App_Model_Db_Table_Poll();
            $rowset = $tblPolling->find($pGuid);
            if(count($rowset))
            {
                $row = $rowset->current();
                try {
                    $row->delete();
                }
                catch (Exception $e)
                {
                    throw new Zend_Exception($e->getMessage());
                }
            }
	}
	function poll($aData)
	{
            $voteid = (isset($aData['poll']))? $aData['poll'] : '';
            $id = ($aData['id'])? $aData['id'] : '';

            $tblPolliP = new App_Model_Db_Table_PollIp();
            $ip_result = $tblPolliP->fetchRow("ip='".Pandamp_Lib_Formater::getRealIpAddr()."' AND pollGuid='".$id."'");

            if (!isset($ip_result))
            {
                $rowIp = $tblPolliP->fetchNew();
                $rowIp->dateOfPoll 	= date("Y-m-d H:i:s");
                $rowIp->ip		= Pandamp_Lib_Formater::getRealIpAddr();
                $rowIp->voteId		= $voteid;
                $rowIp->pollGuid	= $id;
                $rowIp->save();

                if ($voteid)
                {
                    $tblPoll = new App_Model_Db_Table_Poll();
                    $rowPoll = $tblPoll->find($id)->current();

                    if ($rowPoll)
                    {
                        $rowPoll->voters = $rowPoll->voters + 1;
                        $rowPoll->save();
                    }

                    $tblOption = new App_Model_Db_Table_PollOption();
                    $rowOption = $tblOption->fetchRow("guid='$voteid' AND pollGuid='$id'");

                    if ($rowOption)
                    {
                        $rowOption->hits = $rowOption->hits + 1;
                        $rowOption->save();
                    }
                }
            }
	}
}

?>