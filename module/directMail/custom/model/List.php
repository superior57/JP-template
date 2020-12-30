<?php

class DMList{

	static function getUserTable($id){
		$db = GMList::getDB("list");
		$rec = $db->selectRecord($id);
		$user_type 	= $db->getData($rec,"user_type");
		$label 		= $db->getData($rec,"label");

		$uDB = GMList::getDB($user_type);
		$uTable = $uDB->getTable();
		switch($label){
			case "all":
				break;
			default:
				$uTable = $uDB->searchTable($uTable,"list_id","=","%{$id}%");
				break;
		}

		return $uTable;
	}

	static function getUserCnt($id){
		$db = GMList::getDB("list");
		$table = self::getUserTable($id);
		return $db->getRow($table);
	}

	/*
	 * $db		ユーザーのDBオブジェクト
	 * $table	ユーザーのテーブルデータ
	 * $dmID	リストID
	 */
	static function regist($db,$table,$dmID){
		$row = $db->getRow($table);

		for($i=0;$i<$row;$i++){
			$rec = $db->getRecord($table,$i);
			if($db->getData($rec,"list_id")){
				$list = explode("/",$db->getData($rec,"list_id"));
				$list = array_filter($list);
				if(!in_array($dmID,$list)) $list[]=$dmID;
				$db->setData($rec,"list_id",implode("/",$list));
			}else{
				$db->setData($rec,"list_id",$dmID);
			}
			$db->updateRecord($rec);
		}
	}

	/*
	 * $db		ユーザーのDBオブジェクト
	 * $table	ユーザーのテーブルデータ
	 * $dmID	リストID
	 */
	static function delete($db,$table,$dmID){
		$row = $db->getRow($table);

		for($i=0;$i<$row;$i++){
			$rec = $db->getRecord($table,$i);
			$list = explode("/",$db->getData($rec,"list_id"));

			if(in_array($dmID,$list)) unset($list[array_search($dmID,$list)]);
			$db->setData($rec,"list_id",implode("/",$list));
			$db->updateRecord($rec);
		}
	}
}