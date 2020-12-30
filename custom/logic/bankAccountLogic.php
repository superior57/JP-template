<?php
class bankAccountLogic{

	function userRegistInit($data){
		$db = GMList::getDB(self::getType());
		$rec = $db->getNewRecord();
		foreach($data as $key => $datum){
			$db->setData($rec,$key,$datum);
		}
		$db->addRecordEx($rec,"shadow");
	}

	function userDeleteInit($userID){
		$db = GMList::getDB(self::getType());
		$rec = $db->selectRecord($userID);
		$db->deleteRecord($rec);
	}

	function existsBankAccount($userID)
	{
		$db = GMList::getDB(self::getType());
		$table = $db->getTable();
		$table = $db->searchTable($table, 'id', '=', $userID);

		if ($db->getRow($table) == 0) {
			return;
		}

		$rec = $db->getRecord($table, 0);

		$hasBankName    = $db->getData($rec,"bank_name")    !== "";
		$hasBranchName  = $db->getData($rec,"branch_name")  !== "";
		$hasAccountType = $db->getData($rec,"account_type") !== "0";
		$hasAccountCd   = $db->getData($rec,"account_cd")   !== "";
		$hasAccountName = $db->getData($rec,"account_name") !== "";
		$isEdited       = $db->getData($rec,"edit")         !== "0";

		if ($hasBankName && $hasBranchName && $hasAccountType && $hasAccountCd && $hasAccountName && $isEdited) {
		    return true;
		} else {
		    return false;
		}
	}

	function getType(){
		return "bank_account";
	}
}