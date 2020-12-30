<?php
class messageLogic{

	//企業がユーザーへメールを送信しても良いかどうか
	static function sendableMessage($cID,$userID){
		$result = self::existsInquiryMail($cID,$userID);

		return !is_null(Entry::getApplyItemsID($cID,$userID)) || $result;
	}

	function existsInquiryMail($cID,$userID){
		$db = GMList::getDB(self::getType());
		$table = $db->getTable();
		$table = $db->searchTable($table,"destination","=",$cID);
		$table = $db->searchTable($table,"owner","=",$userID);
		$table = $db->searchTable($table,"mailtype","in",array("inquiry","reply"));

		return $db->existsRow($table);
	}

	static function getScoutCnt($itemsOwnerID,$entryUserID ,$jobID){
		$threadID = threadLogic::getThreadID($itemsOwnerID, $entryUserID);

		if(is_null($threadID))return null;

		$db = GMList::getDB(self::getType());
		$table = $db->getTable();
		$table = $db->searchTable($table,"thread_id","=",$threadID);
		$table = $db->searchTable($table,"mailtype","=","scout");
		$table = $db->searchTable($table,"file","=","%".$jobID."%");

		return $db->getRow($table);
	}

	static function checkEntry($thread_id){
		$db = GMList::getDB(self::getType());
		$table = $db->getTable();
		$table = $db->searchtable($table,"thread_id","=",$thread_id);
		$table = $db->searchTable($table,"mailtype","=","entry");

		return $db->existsRow($table);
	}

	static function regist($thread_id ,$mailtype,$sub = null ,$message = null ,$file = null,$ownerID = null){
		global $LOGIN_ID;
		global $loginUserType;
		$data = threadLogic::getData($thread_id);

		if(is_null($ownerID)){ $ownerID = $LOGIN_ID;}

		switch($loginUserType){
			case "cUser":
				$destination = $data["nUser"];
				break;
			case "nUser":
			case "nobody":
				$destination = $data["cUser"];
				break;
		}
		if($loginUserType == "cUser")
			$destination = $data["nUser"];
		else
			$destination = $data["cUser"];

		$db = GMList::getDB(self::getType());
		$rec = $db->getNewRecord();
		$db->setData($rec,"thread_id",$thread_id);
		$db->setData($rec,"owner",$ownerID);
		$db->setData($rec,"owner_type",$loginUserType);
		$db->setData($rec,"destination",$destination);
		$db->setData($rec,"file",$file);
		$db->setData($rec,"sub",$sub);
		$db->setData($rec,"message",$message);
		$db->setData($rec,"read_flg",false);
		$db->setData($rec,"sender_del",false);
		$db->setData($rec,"receiver_del",false);
		$db->setData($rec,"mailtype",$mailtype);
		$db->setData($rec,"regist",time());

		$db->addRecord($rec);
		if($mailtype != "entry")
			MailLogic::noticeReceiveMessage($rec);
		return $db->getData($rec,"id");
	}

	function setReadFlg($thread_id,$destination){
		$db = GMList::getDB(self::getType());
		$table = $db->getTable();
		$table = $db->searchTable($table,"thread_id","=",$thread_id);
		$table = $db->searchTable($table,"destination","=",$destination);
		$table = $db->searchTable($table,"read_flg","=",false);
		if($db->existsRow($table)){
			$db->setTableDataUpdate($table,"read_flg",true);
		}
	}

	function existsMessage($items_id){
		$db = GMList::getDB(self::getType());
		$table = $db->getTable();
		$table = $db->searchTable($table,"items_id","=",$items_id);
		return $db->existsRow($table);
	}

	public static function searchFreeword($db, $table, $param)
	{
		// フリーワードが指定されている場合
		if (strlen($param['free'])) {
			$freeList = explode(' ', str_replace('　', ' ', $param['free']));
			foreach ($freeList as $free) {
				$free = '%' . $free . '%';

				$table1 = $db->searchTable($table, 'sub', '=', $free);
				$table2 = $db->searchTable($table, 'message', '=', $free);

				$tmpTable[] = $db->orTable($table1, $table2);
			}
			while (count($tmpTable) > 0) {
				$table = $db->andTable($table, array_shift($tmpTable));
			}
		}
		return $table;
	}

	function getType(){
		return "message";
	}
}