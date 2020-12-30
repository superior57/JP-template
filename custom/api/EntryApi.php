<?php
class mod_entryApi extends mod_statusChangeApi{

	function update(&$param){
		global $LOGIN_ID;
		global $loginUserType;

		$id = $param["id"];
		$status = $param["status"];

		if(empty($id)){throw new Exception();}
		if(!entryLogic::isStatus($status)){
			print "invalid_status";
			return;
		}

		$db = GMList::getDB($this->getType());
		$rec = $db->selectRecord($id);

		$owner = $db->getData($rec,"items_owner");
		switch($loginUserType){
			case "admin":
				break;
			default:
				if($owner != $LOGIN_ID){throw new Exception();}
				break;
		}
		//変更操作が管理者によるものなら続行
		if($db->getData($rec,"status") == "SUCCESS"){
			print "succeeded";
			return;
		}

		$db->setData($rec,"status",$status);
		$db->updateRecord($rec);

		// 進捗ステータス変更通知
		MailLogic::sendEntryStatusChenge($rec);

		if(Conf::checkData("charges", "employment","on")){
			if($db->getData($rec,"status") == "SUCCESS"){
				pay_jobLogic::addEmploymentLog($rec);	//採用課金
			}
		}
	}

	function rejectApply($param){
		$param["status"]= "FAILE";
		$this->update($param);
	}

	function getType(){
		return "entry";
	}
}