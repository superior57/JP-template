<?php
class PayJob{

	/*
	 * 契約情報の登録
	*/
	static function add($owner,$tType,$tID,$label,$money,$limits){
		$db = GMList::getDB(self::getType());
		$rec = $db->getNewRecord();
		$db->setData($rec,"owner",$owner);
		$db->setData($rec,"target_type",$tType);
		$db->setData($rec,"target_id",$tID);
		$db->setData($rec,"label",$label);
		$db->setData($rec,"money",$money);
		$db->setData($rec,"pay_flg",true);
		$db->setData($rec,"pay_time",time());
		$db->setData($rec,"notice",false);
		$db->setData($rec,"limits",$limits);
		$db->setData($rec,"regist",time());
		$db->addRecord($rec);
		return $rec;
	}


	function getType(){
		return "pay_job";
	}
}