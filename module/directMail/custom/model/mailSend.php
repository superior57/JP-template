<?php
class mailSend{


	//未送信ユーザーを取得
	static function getUnSentUserList($id){
		$db = GMList::getDB(self::getType());
		$rec = $db->selectRecord($id);
		$list_id = $db->getData($rec,"list_id");
		$user_type = $db->getData($rec,"user_type");
		$success = $db->getData($rec,"success_id");
		$successUsers = explode("/",$success);

		$through_id = $db->getData($rec,"through_id");
		$throughUsers = explode("/",$through_id);

		$rejectUsers = array_merge($successUsers,$throughUsers);

		$userDB = GMList::getDB($user_type);
		$uTable = DMList::getUserTable($list_id);
		$uTable = $userDB->searchTable($uTable,"id","not in",$rejectUsers);
		return $userDB->getDataList($uTable, "id",null);
	}

	function getType(){
		return "mailSend";
	}
}