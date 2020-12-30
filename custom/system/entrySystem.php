<?php
class entrySystem extends System{

	function drawRegistForm( &$gm, $rec, $loginUserType, $loginUserRank )
	{
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $LOGIN_ID;
		// **************************************************************************************

		$this->setErrorMessage($gm[ $_GET['type'] ]);

		$jobID = empty($_GET["mid_id"])?$_GET["fresh_id"]:$_GET["mid_id"];

		//応募出来るかチェック
		$check = entryLogic::checkApply(SystemUtil::getJobType($jobID),$jobID);
		$label = $check === true ? "REGIST_FORM_PAGE_DESIGN":$check;

		if(!empty($_GET["mid_id"]))	$query["mid_id"] = $_GET["mid_id"];
		if(!empty($_GET["fresh_id"]))	$query["fresh_id"] = $_GET["fresh_id"];

		if( 'normal' == WS_SYSTEM_SYSTEM_FORM_ACTON )
		{
			$action = 'index.php?app_controller=register&type=' . $_GET[ 'type' ]."&".http_build_query((array)$query);
		}
		else if( 'index' == WS_SYSTEM_SYSTEM_FORM_ACTON )
		{
			$action = 'index.php?app_controller=register&type=' . $_GET[ 'type' ]."&".http_build_query((array)$query);
		}
		else
		{ $action = ' ';
		}
		switch(  $_GET['type']  )
		{
			default:
				// 汎用処理
				if($gm[$_GET['type']]->maxStep >= 2)
					Template::drawTemplate( $gm[ $_GET['type'] ] , $rec , $loginUserType , $loginUserRank , $_GET['type'] , $label . $_POST['step'] , $action );
				else
					Template::drawTemplate( $gm[ $_GET['type'] ] , $rec , $loginUserType , $loginUserRank , $_GET['type'] , $label , $action );
		}
	}

	function drawRegistCheck( &$gm, $rec, $loginUserType, $loginUserRank )
	{
		if(!empty($_GET["mid_id"]))	$query["mid_id"] = $_GET["mid_id"];
		if(!empty($_GET["fresh_id"]))	$query["fresh_id"] = $_GET["fresh_id"];

		if( 'normal' == WS_SYSTEM_SYSTEM_FORM_ACTON )
		{
			$action = 'index.php?app_controller=register&type=' . $_GET[ 'type' ]."&".http_build_query((array)$query);
		}
		else if( 'index' == WS_SYSTEM_SYSTEM_FORM_ACTON )
		{
			$action = 'index.php?app_controller=register&type=' . $_GET[ 'type' ]."&".http_build_query((array)$query);
		}
		else
		{ $action = ' ';
		}

		switch(  $_GET['type']  )
		{
			default:
				// 汎用処理
				Template::drawTemplate( $gm[ $_GET['type'] ] , $rec , $loginUserType , $loginUserRank , $_GET['type'] , 'REGIST_CHECK_PAGE_DESIGN' , $action );
		}
	}

	function registCheck( &$gm, $edit, $loginUserType, $loginUserRank )
	{
		global $LOGIN_ID;
		$result = parent::registCheck($gm, $edit, $loginUserType, $loginUserRank);

		$data = self::$checkData->getData();

		if($loginUserType != "admin"){
			$resumeID = $data["resume_id"];
			$owner = SystemUtil::getTableData("resume",$resumeID,"owner");
			if($LOGIN_ID != $owner)
				self::$checkData->addError("resume_id_NOT_MINE");
		}
		self::$checkData->checkNull("resume_id", array());
		$res = self::$checkData->getCheck();

		return $result && $res;
	}

	function registProc( &$gm, &$rec, $loginUserType, $loginUserRank ,$check = false)
	{
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $LOGIN_ID;
		// **************************************************************************************

		parent::registProc( $gm, $rec, $loginUserType, $loginUserRank, $check );

		$db	 = $gm[ $_GET['type'] ]->getDB();
		$job_id = $_GET["mid_id"];
		$job_id = empty($job_id) ? $_GET["fresh_id"] : $job_id;

		$itemsType = SystemUtil::getJobType($job_id);

		$db->setData( $rec, "items_type",$itemsType);
		$db->setData( $rec, 'items_id', $job_id );
		$db->setData( $rec, 'items_owner', SystemUtil::getTableData($itemsType, $job_id, "owner") );
		$db->setData( $rec, 'entry_user', $LOGIN_ID );
		$db->setData( $rec, 'status', "START" );
	}

	function registComp( &$gm, &$rec, $loginUserType, $loginUserRank )
	{
		$db	 = $gm[ $_GET['type'] ]->getDB();
		$type = $db->getData($rec,"items_type");
		$itemsID = $db->getData($rec,"items_id");
		$cUser = $db->getData($rec,"items_owner");
		$entryUserID = $db->getData($rec,"entry_user");

		$thread_id = threadLogic::getThreadID($cUser, $entryUserID);
		$file = "index.php?app_controller=info&type=resume&id=".h($_POST["resume_id"]);

		$message_id = messageLogic::regist($thread_id, "entry",h($_POST["sub"]),h($_POST["message"]),$file);
		$db->setData($rec,"message_id",$message_id);
		$db->updateRecord($rec);

		if(Conf::getData("charges", "scout") == "deferred"){
			pay_jobLogic::addScoutLog($cUser,$entryUserID,$db->getData($rec,"items_id"));
		}
		pay_jobLogic::addApplyLog($rec);
		JobLogic::updateApplyPos($type, $itemsID);

		MailLogic::EntryNotice( "nUser", $rec );

		// お祝い金申請をエントリー時からでもできるように
		$id = $db->getData($rec, "id");
		$tType = $db->getData($rec,"items_type");
		$tID = $db->getData($rec,"items_id");
		$termType = SystemUtil::getTableData($tType, $tID, "term_type");

		if(Conf::checkData("charges", "gift", $termType)){
			$itemsForm = SystemUtil::getTableData($tType, $tID, "work_style");
			$giftCost = SystemUtil::getTableData("items_form", $itemsForm, "gift");

			GiftLogic::regist ($entryUserID, $id, $giftCost);
		}

		// 検討中リストからエントリーした求人を外す
		clipLogic::delete($tType, $tID);
	}

	function searchResultProc( &$gm, &$sr, $loginUserType, $loginUserRank )
	{
		$type = SearchTableStack::getType();
		$db = $gm[ $type ]->getDB();

		switch($loginUserType){
			case 'admin':
				//nobodyモジュールがない場合nUserをパラメータ検索を行う
				if(!SystemUtil::existsModule("nobody")){
					$sr->setAlias("nUser","entry_user id match comp" );
					$sr->setAliasParam("nUser",array("entry_user_name","name","match","like"));
					$sr->value[ "entry_user_name" ] = $_GET["entry_user_name"];
				}
				break;
		}
	}

	function searchProc( &$gm, &$table, $loginUserType, $loginUserRank )
	{
		global $LOGIN_ID;
		$type = SearchTableStack::getType();

		$db = $gm[ $type ]->getDB();

		switch($loginUserType){
			case "admin":
				//nobodyモジュールがある場合nUser,nobodyのSQL検索を行う
				if(SystemUtil::existsModule("nobody")){
					if(!empty($_GET["entry_user_name"]))
						$table = nobodyLogic::searchEntry($db,$table, $_GET);
				}
				break;
			case "cUser":
				$table = $db->searchTable($table,"items_owner","=",$LOGIN_ID);
				break;
			case "nUser":
				$table = $db->searchTable($table,"entry_user","=",$LOGIN_ID);
				break;
		}

	}
}