<?php
class pay_jobLogic{

	// 各ユーザーの中途、新卒最新決済ログに利用期間を設定する
	static function setChargesUserLimit($type,$year, $month, $day){
		$pDB = GMList::getDB("pay_job");
		$pTable = pay_jobLogic::getLsatTerm($type);
		$pOwners = $pDB->getDataList($pTable, "owner"); // pay_jobにある企業リスト

		$term = DateUtil::getUnixTime($year, $month, $day, 'end');
		$pTable = $pDB->searchTable( $pTable, 'limits', '<', $term );
		$pTable = $pDB->searchTable( $pTable, 'pay_flg', '=', true );
		$pTable = $pDB->searchTable($pTable,"label","=",$type);

		$row = $pDB->getRow($pTable);

		for($i=0;$i<$row;$i++){
			$rec = $pDB->getRecord($pTable, $i);
			$owner = $pDB->getData($rec, "owner");
			if($type == "mid"){
				PayJob::add($owner, "{$type}_term", "MT000", $type, 0, $term);
			} elseif($type == "fresh"){
				PayJob::add($owner, "{$type}_term", "FT000", $type, 0, $term);
			}
		}

		$cDB = GMList::getDB("cUser");
		$cTable = $cDB->getTable();
		$cRow = $cDB->getRow($cTable);
		for($i=0;$i<$cRow;$i++){
			$rec = $cDB->getRecord($cTable, $i);
			$owner = $cDB->getData($rec, "id");
			
			// pay_jobにない企業なら、無料利用期間を付与
			if (!in_array($owner, (array)$pOwners)) {
				if($type == "mid"){
					PayJob::add($owner, "{$type}_term", "MT000", $type, 0, $term);
				} elseif($type == "fresh"){
					PayJob::add($owner, "{$type}_term", "FT000", $type, 0, $term);
				}
				$row++;
			}
		}
		
		return $row;
	}

	// 中途、新卒に指定の利用期間を設定する
	function setLimitAll($table, $year, $month, $day )
	{
		$db  = GMList::getDB('pay_job');
		$row = $db->getRow($table);

		$term = DateUtil::getUnixTime($year, $month, $day, 'end');
		$db->setTableDataUpdate( $table, 'limits', $term );
		return $row;
	}

	//未請求の課金ログがあるか
	static function existsUnclaimed($userID){
		$db = GMList::getDB(self::getType());
		$table = $db->getTable();
		$table = $db->searchTable($table,"owner","=",$userID);
		$table = $db->searchTable($table,"is_billed","=",false);
		$table = $db->searchTable($table,"money","!",0);
		return $db->existsRow($table);
	}

	/*
	 * 月額利用の最新契約を取得する
	 */
	static function getLsatTerm($type)
	{
		if(!is_array($type)){
			$type = array($type);
		}

		$pDB = GMList::getDB("pay_job");
		$pTable = $pDB->getTable();
		$pTable2 = $pTable;
		$pTable = $pDB->searchTable($pTable, "label", "in", $type);
		$pTable = $pDB->searchTable($pTable, "pay_flg", "=", true);
		$pTable = $pDB->getMaxTable("shadow_id", "owner", $pTable);
		$terms = $pDB->getDataList($pTable, "max");

		if(!is_null($terms)){
			$pTable = $pDB->searchTable($pTable2, "shadow_id", "in", $terms);
		}else{
			$pTable = $pDB->getEmptyTable();
		}
		return $pTable;
	}

	/*
	 * 求人の課金方法を取得する
	 *
	 * $jobType		mid/fresh
	 * $jobID		求人ID
	 * $termType	apply/employment
	 */
	function getJobTermType($jobType,$jobID){
		$db = GMList::getDB($jobType);
		$rec = $db->selectRecord($jobID);
		return $db->getData($rec,"term_type");
	}

	/*
	 * ユーザーの求人契約タイプを取得
	 */
	static function getUserTerm($cUserID,$label){
		if($label != "mid" && $label != "fresh")
			{ return false; }

		$db = GMList::getDB(self::getType());
		$table = $db->getTable();
		$table = $db->searchTable($table,"owner","=",$cUserID);
		$table = $db->searchTable($table,"label","=",$label);
		$table = $db->searchTable($table,"pay_flg","=",true);
		$table = $db->sortTable($table, "regist", "desc");
		$rec = $db->getFirstRecord($table);

		if(empty($rec)) return false;

		if($db->getData($rec,"target_id") == ""){
			return "job";
		}else{
			return "time";
		}
	}

	//課金ログ追加の応募課金、採用課金の共通処理
	function addLogCommon($type,$rec){
		$db = GMList::getDB("entry");
		$id = $db->getData($rec,"id");
		$cUserID = $db->getData($rec,"items_owner");
		$entryUserID = $db->getData($rec,"entry_user");
		$tType = $db->getData($rec,"items_type");
		$tID = $db->getData($rec,"items_id");
		$termType = SystemUtil::getTableData($tType, $tID, "term_type");

		$result = true;
		//システム設定のチェック
		if(Conf::checkData("charges", $type, "off"))
			{ $result = false; }

		$jobTerm = self::getJobTermType($tType, $tID);

		//求人の課金タイプのチェック
		switch($jobTerm){
			case "user_limit":
			case "none":
				$result = false;
				break;
			case "employment":
			case "apply":
				$result = $jobTerm == $type;
				break;
		}

		$ifID = SystemUtil::getTableData($tType, $tID, "work_style");

		$taxRate = SystemUtil::getSystemData("tax");
		$cost = SystemUtil::getTableData("items_form", $ifID, $type);
		$cost = $cost + ceil($cost * $taxRate/100);

		if(Conf::checkData("charges", "gift", $termType)){
			$itemsForm = SystemUtil::getTableData($tType, $tID, "work_style");
			$giftCost = SystemUtil::getTableData("items_form", $itemsForm, "gift");
		}

		if($result){
			return PayJob::add($cUserID, $tType, $tID, $type, $cost, 0);
		}
	}

	/*
	 * 採用課金
	 */
	static function addEmploymentLog($rec){
		$pRec = self::addLogCommon("employment", $rec);
		if(!empty($pRec))
		{
			MailLogic::noticeEmploymentPay($pRec);
		}
	}

	/*
	 * 応募課金
	 */
	static function addApplyLog($rec){
		$pRec = self::addLogCommon("apply", $rec);
		if(!empty($pRec))
		{
			MailLogic::noticeApplyPay($pRec);
		}
	}

	/*
	 * スカウト課金
	 */
	static function addScoutLog($cUserID,$nUserID,$jobID = null,$messageID = null){
		switch(Conf::getData("charges", "scout")){
			case "advance":
				self::scoutAdvance($cUserID,$nUserID,$messageID);
				break;
			case "deferred":
				self::scoutDeferred($cUserID,$nUserID,$jobID);
				break;
			case "read":
				self::scoutRead($cUserID,$nUserID);
				break;
			default:
				return;
		}
	}

	//スカウト時
	function scoutAdvance($cUserID,$nUserID,$messageID){
		$taxRate = SystemUtil::getSystemData("tax");
		$cost = systemUtil::getTableData("scout", "SC001", "cost");
		$cost = $cost + ceil($cost * $taxRate/100);
		$pRec = PayJob::add($cUserID, "nUser", $nUserID, "scout", $cost, 0);
		MailLogic::noticeScoutPay($pRec,$messageID);
	}

	//応募時
	function scoutDeferred($cUserID,$nUserID,$jobID){
		$taxRate = SystemUtil::getSystemData("tax");
		$cost = systemUtil::getTableData("scout", "SC001", "cost");
		$cost = $cost + ceil($cost * $taxRate/100);
		$thread_id = threadLogic::getThreadID($cUserID,$nUserID);

		$db=GMList::getDB("message");
		$table = $db->getTable();
		$table = $db->searchTable($table,"thread_id","=",$thread_id);
		$table = $db->searchTable($table,"mailtype","=","scout");
		$table = $db->searchTable($table,"file","=","%".$jobID."%");
		if(!$db->existsRow($table)) return ;

		$pRec = PayJob::add($cUserID, "nUser", $nUserID, "scout", $cost, 0);
		MailLogic::noticeScoutApplyPay($pRec,$nUserID,$jobID);
	}

	//既読課金
	function scoutRead($cUserID,$nUserID){
		$taxRate = SystemUtil::getSystemData("tax");
		$cost = systemUtil::getTableData("scout", "SC001", "cost");
		$cost = $cost + ceil($cost * $taxRate/100);
		$thread_id = threadLogic::getThreadID($cUserID,$nUserID);

		$db=GMList::getDB("message");
		$table = $db->getTable();
		$table = $db->searchTable($table,"thread_id","=",$thread_id);
		$table = $db->searchTable($table,"mailtype","=","scout");
		$table = $db->searchTable($table,"read_flg","=",false);
		$table = $db->searchTable($table,"declination_scout","=",false); //スカウトが辞退されていた場合課金しない

		if(!$db->existsRow($table)) return;

		$row = $db->getRow($table);

		for($i=0;$i<$row;$i++){
			$pRec = PayJob::add($cUserID, "nUser", $nUserID, "scout", $cost, 0);
			MailLogic::noticeScoutMailReaded($pRec);
		}
	}

	static function isAvailable($userID,$label){
		$result = false;

		$user_limit = Conf::checkData("charges", "user_limit", "off");

		if($user_limit)
			return true;

		if(self::getLimits($userID, $label)>time())
			$result = true;
		else
			$result = false;

		if(!$result)
			cUserLogic::setFlg($userID,$label,false);

		return $result;
	}

	/*
	 * 現在の契約期限を取得
	 *
	 * userID	対象のユーザーID
	 * label	mid/fresh
	 *
	 * (timestamp)return 有効期限
	 */
	static function getLimits($userID ,$label ,$itemsID = null){
		$db = GMList::getDB(self::getType());
		$table = $db->gettable();
		$table = $db->searchTable($table,"owner","=",$userID);
		$table = $db->searchTable($table,"label","=",$label);
		if($label == "attention")
			$table = $db->searchTable($table,"target_id","=",$itemsID);
		$table = $db->searchTable($table,"pay_flg","=",true);
		$table = $db->sortTable($table, "shadow_id", "desc");
		$table = $db->sortTable($table, "limits", "desc","add");
		$table = $db->limitOffset($table, 0, 1);

		if(!$db->existsRow($table))
			return -1;

		$rec = $db->getFirstRecord($table);
		if($db->getData($rec,"target_id")){
			return $db->getData($rec,"limits");
		}else{
			return time()+86400;
		}
	}

	static function getNewLimits(&$rec,$type,$addSec){
		$db = GMList::getDB(self::getType());
		$owner = $db->getData($rec,"owner");
		$itemsID = $db->getData($rec,"target_id");

		$current_limit = self::getLimits($owner, $type ,$itemsID);

		if($current_limit > time()){
			return $current_limit+$addSec;
		}else{
			return time()+$addSec;
		}
	}

	//初回契約かどうか
	static function isFirstPayment($type,$user_id){
		$db = GMList::getDB("pay_job");
		$table = $db->getTable();
		$table = $db->searchTable($table,"target_type","=","{$type}_term");
		$table = $db->searchTable($table,"owner","=",$user_id);
		$table = $db->searchTable($table,"pay_flg","=",true);

		return !$db->existsRow($table);
	}

    static function addLimits(&$rec)
    {
        $db = GMList::getDB(self::getType());
        $label = $db->getData($rec, "label");
        switch($label){
            case "attention":
                $regist = $db->getData($rec, "regist");
                $limits = $db->getData($rec, "limits");
                $addSec = $limits - $regist;
                $type = $db->getData($rec, "target_type");
                $target_id = $db->getData($rec, "target_id");
                if($type == "mid")
                    midLogic::addLimits($target_id, $addSec);
                elseif($type == "fresh")
                    freshLogic::addLimits($target_id, $addSec);
        }
    }

	static function onPaySetting(){
		$user_limit = Conf::checkData("charges", "user_limit", "on");
		$apply = Conf::checkData("charges", "apply", "on");
		$employment = Conf::checkData("charges", "employment", "on");
		$attention = Conf::checkData("charges", "attention", "on");
		$scout = !Conf::checkData("charges", "scout", "off");

		return ($user_limit || $apply || $employment || $attention || $scout);
	}

    private function getType(){
		return "pay_job";
	}
}