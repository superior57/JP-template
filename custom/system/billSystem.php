<?php
class billSystem extends System{

	function infoProc( &$gm, &$rec, $loginUserType, $loginUserRank )
	{
		// 簡易情報変更（情報ページからの内容変更処理）
		if(  isset( $_POST['post'] ) ){
			switch($loginUserType){
				case "admin":
					$db		 = $gm[ $_GET['type'] ]->getDB();
					if($_POST["notice"] == "FALSE" && $_POST["pay_flg"] == "TRUE")
						break;

					for( $i=0; $i<count($db->colName); $i++ ){
						if(isset($_POST[ $db->colName[$i]])){
							billLogic::setInfoProc($db->colName[$i],$rec);
							$db->setData( $rec, $db->colName[$i], $_POST[ $db->colName[$i] ] );
						}
					}
					$db->updateRecord( $rec );
					break;
				case "cUser":
					$db		 = $gm[ $_GET['type'] ]->getDB();
					if(!empty($_POST["notice"])){
						if($_POST["notice"] == "TRUE")
							MailLogic::noticePayment($rec);

						$db->setData( $rec, "notice", $_POST["notice"] );
						$db->updateRecord( $rec );
					}
					break;
			}
		}
	}

	function searchProc(&$gm, &$table, $loginUserType, $loginUserRank){
		global $LOGIN_ID;

		$type = SearchTableStack::getType();

		$db = $gm[ $type ]->getDB();
		switch($loginUserType){
			case"cUser":
				$table = $db->searchTable($table,"owner","=",$LOGIN_ID);
				$table = $db->searchTable($table,"publish","=",true);
				break;
		}

		$table = billLogic::searchDemand($db,$table,$_GET);

	}
}