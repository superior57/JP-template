<?php
class resumeSystem extends System{

	/**
	 * 複製登録条件確認。
	 *
	 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
	 * @param edit 編集なのか、新規追加なのかを真偽値で渡す。
	 * @return 複製登録が可能かを真偽値で返す。
	 */
	function copyCheck( &$gm, $loginUserType, $loginUserRank )
	{
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $LOGIN_ID;
		// **************************************************************************************

		$result = false;
		switch($loginUserType)
		{
		case 'admin':
			$result = true;
			break;
		case 'nUser':
			$tgm = GMList::getGM("resume");
			$db	 = $tgm->getDB();
			$rec = $db->selectRecord($_GET["cp"]);
			if($db->getData($rec,"owner")==$LOGIN_ID){
				$result = true;
			}
			break;
		case 'cUser':
		case 'aUser':
		}

		return $result;
	}

	function drawDeleteCheck( &$gm, &$rec, $loginUserType, $loginUserRank )
	{
		$db = $gm[$_GET["type"]]->getDB();
		$owner = $db->getData($rec,"owner");

		if($loginUserType != "admin")
			Concept::IsFalse(resumeLogic::isLast($owner))->OrThrow("ResumeAllDelete");

		parent::drawDeleteCheck($gm, $rec, $loginUserType, $loginUserRank);
	}

	function drawRegistForm( &$gm, $rec, $loginUserType, $loginUserRank )
	{
		global $LOGIN_ID;
		$db	 = $gm[ $_GET['type'] ]->getDB();
		$db->setData($rec,"owner",$LOGIN_ID);
		parent::drawRegistForm($gm, $rec, $loginUserType, $loginUserRank);
	}

	function infoCheck( &$gm, &$rec, $loginUserType, $loginUserRank )
	{
		global $ACTIVE_NONE;
		global $ACTIVE_DENY;
		global $LOGIN_ID;
		$db	 = $gm[ $_GET['type'] ]->getDB();
		$owner = $db->getData($rec,"owner");
		$activate = SystemUtil::getTableData("nUser",$owner,"activate");

		switch($loginUserType){
			case "admin":
				return true;
			case "cUser":
				if(is_null(SystemUtil::getTableData("nUser",$owner,"id")))
					return false;
				return resumeLogic::isOpen($rec, $LOGIN_ID) && !in_array($activate,array($ACTIVE_NONE,$ACTIVE_DENY));
			case "nUser":
				return $db->getData($rec,"owner") == $LOGIN_ID;
			default:
				return false;
		}

		return true;
	}


	function registProc( &$gm, &$rec, $loginUserType, $loginUserRank ,$check = false)
	{
		global $LOGIN_ID;
		$db	 = $gm[ $_GET['type'] ]->getDB();
		$db->setData($rec,"owner",$LOGIN_ID);
		$db->setData($rec,"adds_id",SystemUtil::getTableData("nUser", $LOGIN_ID, "adds"));

		parent::registProc($gm, $rec, $loginUserType, $loginUserRank, $check);
	}

	function registComp(&$gm, &$rec, $loginUserType, $loginUserRank){
		$db = $gm[$_GET["type"]]->getDB();
		if($db->getData($rec,"publish") == "on"){
			$id = $db->getData($rec,"id");
			$owner = $db->getData($rec,"owner");
			resumeLogic::togglePublish($id, $owner);
			SystemUtil::async( 'FeedApi' , 'update' , array(  'targetType' => "nUser" ));
		}else{
			$owner = $db->getData($rec,"owner");
			if(!resumeLogic::existsResume($owner)){
				$db->setData($rec,"publish","on");
				$db->updateRecord($rec);
			}
		}
		parent::registComp($gm, $rec, $loginUserType, $loginUserRank);
	}

	function editComp( &$gm, &$rec, &$old_rec, $loginUserType, $loginUserRank ){
		$db = $gm[$_GET["type"]]->getDB();
		$id = $db->getData($rec,"id");
		$owner = $db->getData($rec,"owner");
		if($db->getData($rec,"publish") == "on"){
			resumeLogic::togglePublish($id, $owner);
			SystemUtil::async( 'FeedApi' , 'update' , array(  'targetType' => "nUser" ));
		}
		parent::editComp($gm, $rec, $old_rec, $loginUserType, $loginUserRank);
	}

	function deleteComp( &$gm, &$rec, $loginUserType, $loginUserRank )
	{
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $LOGIN_ID;
		$db = $gm[$_GET["type"]]->getDB();
		$publish = $db->getData($rec,"publish");

		if($publish == "on"){
			$owner = $db->getData($rec,"owner");
			$table = $db->gettable();
			$table = $db->searchTable($table,"owner","=",$owner);
			if($db->existsRow($table)){
				$rec = $db->getFirstRecord($table);
				$db->setData($rec,"publish","on");
				$db->updateRecord($rec);
			}
		}

		parent::deleteComp($gm, $rec, $loginUserType, $loginUserRank);
	}


	function searchProc(&$gm, &$table, $loginUserType, $loginUserRank){
		global $LOGIN_ID;
		$db = $gm[$_GET["type"]]->getDB();

		$table = resumeLogic::getTable($db,$table);

		$table = resumeLogic::searchWorkPlace($db, $table, $_GET);
		$table = resumeLogic::searchWorkStyle($db, $table, $_GET);
		$table = resumeLogic::searchJobCategory($db, $table, $_GET);
		$table = resumeLogic::searchAge($db, $table, $_GET);
		$table = resumeLogic::searchSalary($db, $table, $_GET);
	}
}