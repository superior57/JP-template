<?php
class resumeLogic{

	static function isOpen($rec,$user_id){
		$db = GMList::getDB(self::getType());
		$owner = $db->getData($rec,"owner");

		$publish = $db->getData($rec,"publish") == "on";

		$eDB = GMList::getDB("entry");
		$eTable = $eDB->getTable();
		$eTable = $eDB->searchTable($eTable,"items_owner","=",$user_id);
		$eTable = $eDB->searchTable($eTable,"entry_user","=",$owner);

		$entry = $eDB->existsRow($eTable);

		return $publish || $entry;
	}

	static function existsResume($userID){
		$db = GMList::getDB("resume");
		$table = $db->getTable();
		$table = $db->searchtable($table,"owner","=",$userID);
		$table = $db->searchtable($table,"publish","=","on");
		return $db->existsRow($table);
	}

	static function isLast($user_id){
		$db = GMList::getDB(self::getType());
		$table = $db->getTable();
		$table = $db->searchtable($table,"owner","=",$user_id);
		return $db->getRow($table) == 1;
	}

	static function updateAdds($user_id,$adds){
		$rDB = GMList::getDB(self::getType());
		$rTable = $rDB->getTable();
		$rTable = $rDB->searchTable($rTable,"owner","=",$user_id);
		$rDB->setTableDataUpdate($rTable, "adds_id", $adds);
	}

	static function existsOpenResume($user_id){
		$db = GMList::getDB(self::getType());
		$table = $db->getTable();
		$table = $db->searchtable($table,"owner","=",$user_id);
		$table = $db->searchtable($table,"publish","=","on");
		return $db->existsRow($table);
	}

	static function delete($user_id){
		$db = GMList::getDB(self::getType());
		$table = $db->getTable();
		$table = $db->searchtable($table,"owner","=",$user_id);
		if($db->existsRow($table))
			$db->deleteTable($table);
	}

	/*
	 *	指定ユーザーの、指定IDの履歴書を公開設定にしそれ以外の履歴書を一括で非公開にする
	 */
	static function togglePublish($id,$user_id){
		$db = GMList::getDB(self::getType());
		$table = $db->getTable();
		$table = $db->searchTable($table,"owner","=",$user_id);
		$onTable = $db->searchTable($table,"id","=",$id);
		$offTable = $db->searchTable($table,"id","!",$id);
		$db->setTableDataUpdate($offTable, "publish", "off");
		$db->setTableDataUpdate($onTable, "publish", "on");
	}

	static function getTable($db = null,$table = null ,$param = null){
		global $loginUserType;
		global $LOGIN_ID;

		if(is_null($db)) $db = GMList::getDB(self::getType());
		if(is_null($table)) $table = $db->getTable();
		if(is_null($param)) $param = array();

		switch($loginUserType){
			case "admin":
				break;
			case "cUser":
				$table = $db->searchTable($table,"publish","=","on");
				$table = $db->searchTableSubQuery( $table, 'owner', 'in', nUserLogic::getActivateIdTable($loginUserType ,$_GET["type"]) );
				break;
			case "nUser":
			default:
				$table = $db->searchTable($table,"owner","=",$LOGIN_ID);
				break;
		}
		return $table;
	}

	static function searchWorkStyle($db,$table,$param){
		if(empty($param["_hope_work_style"]))return $table;
		foreach($param["_hope_work_style"] as $data)
			{ $tableA[] = $db->searchTable($table,"hope_work_style","=","%{$data}%"); }
		return $db->orTableM($tableA);
	}

	static function searchWorkPlace($db,$table,$param){
		if(empty($param["adds"]) && empty($param["foreign_address"]))return $table;

		if(!empty($param["adds"])){
			$adds = SystemUtil::getTableData("adds",$param["adds"],"name");
			$table = $db->searchTable($table,"hope_work_place_label","=","%{$adds}%");
			if($param["add_sub"]){
				$add_sub = SystemUtil::getTableData("add_sub",$param["add_sub"],"name");
				$table = $db->searchTable($table,"hope_work_place_label","=","%{$add_sub}%");
			}
		}elseif(!empty($param["foreign_address"])){
			$table = $db->searchTable($table,"hope_work_place_label","=","%{$param["foreign_address"]}%");
		}
		return $table;
	}

	static function searchJobCategory($db,$table,$param){
		if(empty($param["_hope_job_category"]))return $table;
		foreach($param["_hope_job_category"] as $data)
		{
			$tableA[] = $db->searchTable($table,"hope_job_category","=","%{$data}%");
		}
		return $db->orTableM($tableA);
	}

	static function searchAge($db,$table,$param){
		if(empty($param["_ageA"]) && empty($param["_ageB"]) )return $table;
		$year = (int)date("Y");

		// 若い＝unixtimeが大きい。年寄り＝unixtimeが小さい
		// ○歳以上＝unixtime以下。○歳以下＝unixtime以上
		// 生年月日範囲 (年齢+1)年前[本日の日付+1日]0:00:00〜(年齢)年前[本日の日付]23:59:59
		if(!empty($param["_ageA"])){
			$start = strtotime('-'.$param["_ageA"].' years');
			$startTime = mktime(23,59,59,date('n',$start),date('j',$start),date('Y',$start));
			$table = $db->searchTable($table,"birth_date","<=",$startTime);
		}
		if(!empty($param["_ageB"])){
			$end = strtotime('-'.($param["_ageB"]+1).' years');
			$endTime = mktime(0,0,0,date('n',$end),date('j',$end)+1,date('Y',$end));
			$table = $db->searchTable($table,"birth_date",">=",$endTime);
		}
		return $table;
	}

	static function searchSalary($db,$table,$param){
		if(!isset($param["_hope_salary"]) || !strlen($param["_hope_salary"]))return $table;
		$table = $db->searchTable($table,"hope_salary","<=",$param["_hope_salary"]);
		return $table;
	}

	function getType(){
		return "resume";
	}
}
