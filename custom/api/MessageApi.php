<?php
class mod_messageApi extends apiClass{

	function delete(&$param){
		global $LOGIN_ID;
		global $loginUserType;

		$threadID = $param["tid"];

		if(!pay_jobLogic::isAvailable($LOGIN_ID, "mid") && !pay_jobLogic::isAvailable($LOGIN_ID, "fresh") && $loginUserType=="cUser"){
			print "contractExpire"; return;
		}
		$data = threadLogic::getData($threadID);
		if(!in_array($LOGIN_ID, $data)){print "notOwner"; return;}

		$db = GMList::getDB($this->getType());
		$table = $db->getTable();
		$table = $db->searchTable($table,"thread_id","=",$threadID);
		$tableOwn = $db->searchTable($table,"owner","=",$LOGIN_ID);
		$tableDest = $db->searchTable($table,"destination","=",$LOGIN_ID);

		$recOwn = $db->getFirstRecord($tableOwn);
		if($db->getData($recOwn,"owner") == $LOGIN_ID)
		{
			$db->setTableDataUpdate($tableOwn, "sender_del", true);
		}

		$recDest = $db->getFirstRecord($tableDest);
		if($db->getData($recDest,"destination") == $LOGIN_ID)
		{
			$db->setTableDataUpdate($tableDest, "receiver_del", true);
		}
	}

	function declinationScout(&$param){
		global $LOGIN_ID;

		$tid = $param["tid"];
		$jid = $param["jobID"];

		$db = GMList::getDB("message");
		$table = $db->getTable();
		$table = $db->searchTable($table,"thread_id","=",$tid);
		$table = $db->searchTable($table,"mailtype","=","scout");
		$table = $db->searchTable($table,"file","=","%{$jid}%");
		$table = $db->searchTable($table,"destination","=",$LOGIN_ID);
		$rec = $db->getFirstRecord($table);

		$db->setTableDataUpdate($table,"declination_scout",true);

		MailLogic::noticeDeclinationScout($rec);
	}

	function getType(){
		return "message";
	}
}