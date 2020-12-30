<?php
class GiftLogic{

	static function regist($owner,$entryID,$money){
		$db = GMList::getDB(self::getType());
		$rec = $db->getNewRecord();
		$db->setData($rec,"owner",$owner);
		$db->setData($rec,"entry_id",$entryID);
		$db->setData($rec,"money",$money);
		$db->setData($rec,"activate",0);
		$db->addRecord($rec);
	}

	static function getID($entryID,$owner){
		$db = GMList::getDB(self::getType());
		$table = $db->getTable();
		$table = $db->searchTable($table,"entry_id","=",$entryID);
		$table = $db->searchTable($table,"owner","=",$owner);
//		$table = $db->searchTable($table,"activate","=",0);
		if($db->existsRow($table)){
			$rec = $db->getFirstRecord($table);
			return $db->getData($rec,"id");
		}
		return false;
	}

	function getType(){
		return "gift";
	}
}