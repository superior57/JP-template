<?php
class billLogic{

	static function getCloseDate(){
		return Conf::getData("charges","closing_date");
	}

	static function getBillDate(){
		return Conf::getData("charges","billing_date");
	}

	static function setInfoProc($colName,&$rec){
		$db = GMList::getDB(self::getType());

		switch($colName){
			case "pay_flg":
				if($db->getData($rec, $colName) !== SystemUtil::convertBool($_POST[$colName])){
					if($_POST[$colName] == "TRUE"){
						MailLogic::NoticeAcceptPayment($rec);
					} else{
						MailLogic::NoticeCancelPayment($rec);
					}
				}
				break;
			default:
				break;
		}
	}

	static function existsUnsettled($userID){
		$db = GMList::getDB("bill");
		$table = $db->getTable();
		$table = $db->searchTable($table,"owner","=",$userID);
		$table = $db->searchTable($table,"pay_flg","=",false);
		return $db->existsRow($table);
	}

	function searchDemand($db,$table,$param){
		if(empty($param["demand_y"])) return $table;
		$close = Conf::getData("charges","closing_date");

		if(empty($param["demand_m"])){
			$fdu = new fiscalDateUtil($close);
			$range_s = $fdu->getRange($param["demand_y"],1);
			$range_e = $fdu->getRange($param["demand_y"],12);

			$table = $db->searchTable($table,"demand_s","b",$range_s["s"],$range_e["e"]);
		}else{
			$fdu = new fiscalDateUtil($close);
			$range = $fdu->getRange($param["demand_y"],$param["demand_m"]);
			$table = $db->searchTable($table,"demand_s","b",$range["s"],$range["e"]);
		}

		return $table;
	}

	function getType(){
		return "bill";
	}
}