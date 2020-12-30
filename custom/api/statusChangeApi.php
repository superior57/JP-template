<?php
//管理者用各検索ページの一括変更処理
class mod_statusChangeApi extends apiClass{

	//bill 決済フラグ変更
	function changePayment($param){
		global $loginUserType;
		if($loginUserType != "admin") return;
		$status = array("payOK"=>true,"payNG"=>false);

		$json["result"] = "success";
		$json["message"] = "";

		$type = $param["type"];
		$id = $param["id"];

		$db = GMList::getDB($type);
		$rec = $db->selectRecord($id);
		$notice = $db->getData($rec,"notice");
		$pay_flg = $db->getData($rec,"pay_flg");

		//案件が存在しない、または入金通知:未かつ入金確認:済への変更を弾く為の判定
		$noChange = (!$notice && !$pay_flg && SystemUtil::convertBool($status[$param["val"]]));

		if( !$noChange && $pay_flg !== SystemUtil::convertBool($status[$param["val"]])){
				if($status[$param["val"]] == true){
					MailLogic::NoticeAcceptPayment($rec);
				}else{
					MailLogic::NoticeCancelPayment($rec);
				}
			if($status[$param["val"]] == true){
				$db->setData($rec, "pay_flg", $status[$param["val"]]);
				$db->setData($rec, "pay_time", time());
			}else{
				$db->setData($rec, "pay_flg", $status[$param["val"]]);
				$db->setData($rec, "pay_time", 0);
			}
			$db->updateRecord($rec);
		}else{
			$this->changeFaled($json,"noChange","ステータスの変更はされませんでした。");
		}

		print json_encode($json);
	}

	//bill 通知変更
	function changePaymentNotice($param){
		global $loginUserType;
		if($loginUserType != "admin") return;
		$status = array("payNoticeOK"=>true,"payNoticeNG"=>false);

		$json["result"] = "success";
		$json["message"] = "";

		$type = $param["type"];
		$id = $param["id"];

		$db = GMList::getDB($type);
		$rec = $db->selectRecord($id);
		$notice = $db->getData($rec,"notice");
		$pay_flg = $db->getData($rec,"pay_flg");

		//入金通知:未かつ入金確認:済への変更を弾く為の判定
		$noCange = $notice && $pay_flg && !SystemUtil::convertBool($status[$param["val"]]);

		if(!$noCange && $notice !== SystemUtil::convertBool($status[$param["val"]])){
			$db->setData($rec,"notice",$status[$param["val"]]);
			$db->updateRecord($rec);
		}else{
			$this->changeFaled($json,"noChange","ステータスの変更はされませんでした。");
		}
		print json_encode($json);
	}

	//inquiry 対応フラグ変更
	function changeSupported($param){
		global $loginUserType;
		if($loginUserType != "admin") return;
		$status = array("supportedOK"=>true,"supportedNG"=>false);

		$json["result"] = "success";
		$json["message"] = "";

		$type = $param["type"];
		$id = explode("/", $param["id"]);

		$db = GMList::getDB($type);
		$rec = $db->selectRecord($id);
		$supported = $db->getData($rec,"supported");

		if($supported != $status[$param["val"]]){
			$db->setData($rec,"supported",$status[$param["val"]]);
			$db->updateRecord($rec);
		}else{
			$this->changeFaled($json,"noChange","ステータスの変更はされませんでした。");
		}

		print json_encode($json);
	}

	function changeFaled(&$json,$result,$message){
		$json["result"] = $result;
		$json["message"] = $message;
	}
	//entry 進捗変更
	function changeProgress(&$param){
		global $loginUserType;
		global $LOGIN_ID;

		$type = $param["type"];
		$id = explode("/", $param["id"]);

		$json["result"] = "success";
		$json["message"] = "";

		$db = GMList::getDB($type);
		$rec = $db->selectRecord($id);
		$status = $db->getData($rec,"status");
		$items_owner = $db->getData($rec,"items_owner");

		if($loginUserType == "cUser" && $items_owner != $LOGIN_ID){
			$this->changeFaled($json,"BadRequest", "不正なリクエストです。");
			print json_encode($json);
			return;
		}

		if($status == "SUCCESS" || $status == "FAILE"){
			$this->changeFaled($json,"noChange","ステータスは変更されませんでした。");
			print json_encode($json);
			return;
		}

		switch($param["val"]){
			case "START":
			case "FAILE":
			case "EP001":
			case "EP002":
			case "SUCCESS":
				if($db->getData($rec,"status") != $param["val"]){
					$db->setData($rec, "status", $param["val"]);
					$db->updateRecord($rec);
					MailLogic::sendEntryStatusChenge($rec);
					if($param["val"] == "SUCCESS"){
						pay_jobLogic::addEmploymentLog($rec);	//採用課金
					}
				}else{
					$this->changeFaled($json,"noChange","ステータスは変更されませんでした。");
				}
			break;
		}
		print json_encode($json);
	}

	//common アクティベート変更
	function changeActivate($param){
		global $ACTIVE_NONE;
		global $ACTIVE_ACCEPT;
		global $ACTIVE_DENY;
		global $loginUserType;
		global $loginUserRank;
		global $gm;
		$status = array("Unconfirmed"=>$ACTIVE_NONE,"allowed"=>$ACTIVE_ACCEPT,"notallowed"=>$ACTIVE_DENY);
		if(empty($status[$param["val"]])) return;
		if($loginUserType != "admin") return;

		$json["result"] = "success";
		$json["message"] = "";

		$type = $param["type"];
		$id = explode("/", $param["id"]);

		$db = GMList::getDB($type);
		$rec = $db->selectRecord($id);

		switch($param["type"]){
			case "cUser":
			case "nUser":
				if($db->getData($rec,"activate") != $ACTIVE_ACCEPT && $status[$param["val"]] == $ACTIVE_ACCEPT)
					MailLogic::userRegistComp( $rec, $type ,"statusChange");
				break;
			case "mid":
			case "fresh":
				if($db->getData($rec,"activate") != $ACTIVE_ACCEPT && $status[$param["val"]] == $ACTIVE_ACCEPT)
					MailLogic::noticeProjectActivate($type, $rec);
				break;
			case "interview":
				if($db->getData($rec,"activate") != $ACTIVE_ACCEPT && $status[$param["val"]] == $ACTIVE_ACCEPT)
					MailLogic::noticeInterviewActivate($rec);
				break;
		}

		if($db->getData($rec,"activate") != $status[$param["val"]]){
			$rec_old = $rec;

			$db->setData($rec, "activate", $status[$param["val"]]);
			$db->updateRecord($rec);
			if($param['type'] == 'gift'){	// お祝い金
				$sys = SystemUtil::getSystem($param["type"]);
				$get_bak = $_GET;
				$_GET['type'] = $param["type"];
				// メール通知は editComp()で
				$sys->editComp($gm, $rec, $rec_old, $loginUserType, $loginUserRank);
				$_GET = $get_bak;
			}
		}else{
			$this->changeFaled($json,"noChange","ステータスは変更されませんでした。");
		}

		print json_encode($json);
	}

	//common データ削除
	function delete($param){
		global $gm;
		global $loginUserType;
		global $ACTIVE_DENY;
		if($loginUserType != "admin") return;

		$json["result"] = "success";
		$json["message"] = "";

		$type = $param["type"];
		$id = explode("/", $param["id"]);

		$db = GMList::getDB($type);
		$rec = $db->selectRecord($id);
		$id = $db->getData($rec,"id");

		switch($type){
			case "nUser":
				resumeLogic::delete($id);
				$db->deleteRecord($rec);
				break;
			case "cUser":
				$mdb = $gm['mid']->getDB();
				$table	 = $mdb->searchTable(  $mdb->getTable(), 'owner', '=', $id  );
				$mdb->setTableDataUpdate($table,"delete_flg",true);
				$mdb->setTableDataUpdate($table,"delete_date",time());

				$fdb = $gm['fresh']->getDB();
				$table	 = $fdb->searchTable(  $fdb->getTable(), 'owner', '=', $id  );
				$fdb->setTableDataUpdate($table,"delete_flg",true);
				$fdb->setTableDataUpdate($table,"delete_date",time());

				$db->deleteRecord($rec);
				break;
			case "mid":
			case "fresh":
				if($db->getData($rec,"delete_flg")){
					$this->changeFaled($json,"noChange","ステータスは変更されませんでした。");
				}elseif(entryLogic::existsApply($id)){
					$this->changeFaled($json,"noChange","ステータスは変更されませんでした。");
				}else{
					$db->setData($rec,"delete_flg",true);
					$db->setData($rec,"delete_date",time());
					$db->updateRecord($rec);
				}
				break;
			default:
				$db->deleteRecord($rec);
				break;
		}

		print json_encode($json);
	}
}