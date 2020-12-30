<?php
class messageSystem extends System{


	function searchProc( &$gm, &$table, $loginUserType, $loginUserRank )
	{

		global $LOGIN_ID;
		$type = SearchTableStack::getType();

		$db = $gm[ $type ]->getDB();

		$table = messageLogic::searchFreeword($db, $table, $_GET); // フリーワードの検索条件をセット

		switch($_GET["pal"]){
			case "rec":
				$table = $db->searchTable($table,"destination","=",$LOGIN_ID);
				$table = $db->searchTable($table,"receiver_del","=",false);
				$max = $db->getMaxTable( "regist", "thread_id", $table);
				$table = $db->joinTableSubQuerySQL($table, $max, "x", "x.max=" . $db->tableName . ".regist and x.thread_id=" . $db->tableName . ".thread_id");
				break;
			case "send":
				$table = $db->searchTable($table,"owner","=",$LOGIN_ID);
				$table = $db->searchTable($table,"sender_del","=",false);
				$max = $db->getMaxTable( "regist", "thread_id", $table);
				$table = $db->joinTableSubQuerySQL($table, $max, "x", "x.max=" . $db->tableName . ".regist and x.thread_id=" . $db->tableName . ".thread_id");
				break;
			default:
				//$table = $db->getEmptyTable();
				break;
		}

		//埋め込み表示切替時の既読、既読課金処理
		if($_GET["embedID"]=="message_simple"){
			if(Conf::getData("charges","scout") == "read"){
				if($loginUserType == "nUser" && !$_SESSION['ADMIN_MODE']){
					$thread = threadLogic::getData($_GET["thread_id"],"cUser");
					$cUser = $thread["cUser"];
					$nUser = $thread["nUser"];
					pay_jobLogic::addScoutLog($cUser,$nUser);	//既読時課金
				}
			}

			if($loginUserType != "admin" && !$_SESSION['ADMIN_MODE'])
				messageLogic::setReadFlg($_GET["thread_id"],$LOGIN_ID);
		}
	}


	function drawSearch( &$gm, &$sr, $table, $loginUserType, $loginUserRank ){
		SearchTableStack::pushStack($table);
		$design = $this->getDesign('SEARCH_RESULT_DESIGN');
		Template::drawTemplate( $gm[ $_GET['type'] ] , null , $loginUserType , $loginUserRank , $_GET['type'] , $design );
	}

	function drawSearchNotFound( &$gm, $loginUserType, $loginUserRank )
	{
		$design = $this->getDesign('SEARCH_NOT_FOUND_DESIGN');
		if( strlen($design) )	 {
			Template::drawTemplate( $gm[ $_GET['type'] ] , null , $loginUserType , $loginUserRank , $_GET['type'] , $design );
		}
		else					 { Template::drawErrorTemplate();
		}
	}

	function drawSearchForm( &$sr, $loginUserType, $loginUserRank )
	{
		$sr->addHiddenForm( 'type', $_GET['type'] );

		$file = Template::getTemplate( $loginUserType , $loginUserRank , $_GET['type'] , 'SEARCH_FORM_PAGE_DESIGN' );
		if( strlen($file) )
			print $sr->getFormString( $file , 'search.php' );
		else
			Template::drawErrorTemplate();
	}


	function getSearchResult( &$gm, $table, $loginUserType, $loginUserRank )
	{
		$type = SearchTableStack::getType();

		$html = '';
		$design = $this->getDesign('SEARCH_LIST_PAGE_DESIGN');
		if( strlen($design) ) {
			$html = Template::getListTemplateString( $gm , $table , $loginUserType , $loginUserRank , $_GET['type'] , $design );
		}

		$this->addBuffer( $html );
	}

	function doInfo( &$gm, &$rec, $loginUserType, $loginUserRank )
	{
		global $LOGIN_ID;
		$db = $gm[$_GET["type"]]->getDB();

		$thread_id = $db->getData($rec,"thread_id");

		if(Conf::getData("charges","scout") == "read"){
			if($loginUserType == "nUser" && !$_SESSION['ADMIN_MODE']){
				$nUser = $db->getData($rec,"destination");
				$cUser = $db->getData($rec,"owner");
				pay_jobLogic::addScoutLog($cUser,$nUser);	//既読時課金
			}
		}

		if($loginUserType != "admin" && !$_SESSION['ADMIN_MODE'])
			messageLogic::setReadFlg($thread_id,$LOGIN_ID);
	}

	function drawRegistForm( &$gm, $rec, $loginUserType, $loginUserRank )
	{
		$this->setErrorMessage($gm[ $_GET['type'] ]);

		if(!empty($_GET["mailtype"]))	$query["mailtype"] = $_GET["mailtype"];
		if(!empty($_GET["destination"]))$query["destination"] = $_GET["destination"];
		if(!empty($_GET["mid_id"]))	$query["mid_id"] = $_GET["mid_id"];
		if(!empty($_GET["fresh_id"]))	$query["fresh_id"] = $_GET["fresh_id"];

		$label = "REGIST_FORM_PAGE_DESIGN";

		switch($loginUserType){
			case "cUser":
				Concept::IsTrue(in_array($_GET["mailtype"], array("scout","reply")))->OrThrow("InvalidQuery");
				break;
			case "nUser":
			case "nobody":
				Concept::IsTrue(in_array($_GET["mailtype"], array("inquiry","reply")))->OrThrow("InvalidQuery");
				$applyInquiry = SystemUtil::getTableData("cUser", $query["destination"], "inquiry") == "on";
				if(!$applyInquiry && $query["mailtype"] == "inquiry")
					{ $label = "UNAUTH_INQUIRY"; }
				break;
		}

		switch(  $_GET['type']  )
		{
			default:
				// 汎用処理
				if($gm[$_GET['type']]->maxStep >= 2)
					Template::drawTemplate( $gm[ $_GET['type'] ] , $rec , $loginUserType , $loginUserRank , $_GET['type'] , $label . $_POST['step'] , SystemUtil::GetFormTarget( 'registForm' )."&".http_build_query((array)$query) );
				else
					Template::drawTemplate( $gm[ $_GET['type'] ] , $rec , $loginUserType , $loginUserRank , $_GET['type'] , $label , SystemUtil::GetFormTarget( 'registForm' )."&".http_build_query((array)$query) );
		}
	}

	function drawRegistCheck( &$gm, $rec, $loginUserType, $loginUserRank )
	{
		if(!empty($_GET["mailtype"]))	$query["mailtype"] = $_GET["mailtype"];
		if(!empty($_GET["destination"]))$query["destination"] = $_GET["destination"];
		if(!empty($_GET["mid_id"]))	$query["mid_id"] = $_GET["mid_id"];
		if(!empty($_GET["fresh_id"]))	$query["fresh_id"] = $_GET["fresh_id"];

		switch(  $_GET['type']  )
		{
			default:
				// 汎用処理
				Template::drawTemplate( $gm[ $_GET['type'] ] , $rec , $loginUserType , $loginUserRank , $_GET['type'] , 'REGIST_CHECK_PAGE_DESIGN' , SystemUtil::GetFormTarget( 'registForm' )."&".http_build_query((array)$query) );
		}
	}

	function registCheck(&$gm, $edit, $loginUserType, $loginUserRank)
	{
		global $LOGIN_ID;
		self::$checkData->generalCheck($edit);
		$data = self::$checkData->getData();

		$mailtype = $_GET["mailtype"];

		switch($mailtype){
			case "scout":
				if($loginUserType == "cUser"){
					if(empty($data['file_name'])){
						self::$checkData->checkNull("file_name", array());
					}else{
						$jobID = $data["file_name"];
						$jobType = SystemUtil::getJobType($jobID);
						if(SystemUtil::getTableData($jobType,$jobID,"owner") != $LOGIN_ID){
							self::$checkData->addError("job_id_NOT_MINE",null,"file_name");
						}
					}
				}
				break;
			default:
		}

		// エラー内容取得
		return self::$checkData->getCheck();
	}


	function registProc( &$gm, &$rec, $loginUserType, $loginUserRank ,$check = false)
	{
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $LOGIN_ID;
		// **************************************************************************************

		parent::registProc( $gm, $rec, $loginUserType, $loginUserRank, $check );

		$db	 = $gm[ $_GET['type'] ]->getDB();

		$db->setData( $rec, 'owner', $LOGIN_ID );
		$db->setData( $rec, 'destination', $_GET["destination"] );
		$db->setData( $rec, 'mailtype', $_GET["mailtype"] );
		$db->setData( $rec, 'read_flg', false );
		$db->setData( $rec, 'sender_del', false );
		$db->setData( $rec, 'receiver_del', false );

		switch($_GET["mailtype"]){
			case"scout":
				if(!empty($_POST["file_name"])){
					$link = "index.php?app_controller=info&type=".SystemUtil::getJobType(h($_POST["file_name"]))."&id=".h($_POST["file_name"]);
				}
				break;
			case"inquiry":
				if(!empty($_GET["mid_id"])){
					$link = "index.php?app_controller=info&type=mid&id=".$_GET["mid_id"];
				}
				if(!empty($_GET["fresh_id"])){
					$link = "index.php?app_controller=info&type=fresh&id=".$_GET["fresh_id"];
				}
				break;
		}
		$db->setData($rec,"file",$link);

		switch($loginUserType){
			case "cUser":
				$db->setData($rec,"thread_id",threadLogic::getThreadID($LOGIN_ID, $_GET["destination"]));
				$db->setData($rec,"owner_type","cUser");
				break;
			case "nUser":
				$db->setData($rec,"thread_id",threadLogic::getThreadID($_GET["destination"],$LOGIN_ID));
				$db->setData($rec,"owner_type","nUser");
				break;
		}
	}

	function registComp( &$gm, &$rec, $loginUserType, $loginUserRank ){
		$db	 = $gm[ $_GET['type'] ]->getDB();
		if(Conf::getData("charges", "scout") == "advance"){
			if($db->getData($rec,"mailtype") == "scout"){
				if($db->getData($rec,"owner_type") == "cUser"){
					$cUser = $db->getData($rec,"owner");
					$nUser = $db->getData($rec,"destination");
				}else{
					$cUser = $db->getData($rec,"destination");
					$nUser = $db->getData($rec,"owner");
				}
				$MessageID = $db->getData($rec, "id");
				pay_jobLogic::addScoutLog($cUser,$nUser,$_POST["file_name"],$MessageID);
			}
		}
		MailLogic::noticeReceiveMessage($rec);
	}
	/////////////////////////////////////////////////////////////////////////////////////////////////////////
	// 詳細ページ関係
	/////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * 詳細情報ページを描画する。
	 *
	 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
	 * @param rec 編集対象のレコードデータ
	 * @param loginUserType ログインしているユーザの種別
	 * @param loginUserRank ログインしているユーザの権限
	 */
	function drawInfo( &$gm, &$rec, $loginUserType, $loginUserRank )
	{

		$db = GMList::getDB('message');

		switch($loginUserType)
		{
		case 'admin';
				$design = $this->getDesign('INFO_PAGE_DESIGN');
				Template::drawTemplate( $gm[ $_GET['type'] ] , $rec , $loginUserType , $loginUserRank , $_GET['type'] , $design  );
				break;
		default:
			if( $this->userCheck($rec) )
			{
				switch($db->getData($rec,"owner_type")){
					case 'nobody':
						$design = $this->getDesign('NOBODY_INFO_PAGE_DESIGN');
						break;
					default:
						$design = $this->getDesign('INFO_PAGE_DESIGN');
						break;
				}
				Template::drawTemplate( $gm[ $_GET['type'] ] , $rec , $loginUserType , $loginUserRank , $_GET['type'] , $design  );
			}
			else
			{ Template::drawErrorTemplate(); }
			break;
		}
	}

	function infoCheck(&$gm, &$rec, $loginUserType, $loginUserRank)
	{
		switch($loginUserType){
			case "admin":
				$result = true;
				break;
			case "cUser":
			case "nUser":
				$result = $this->userCheck($rec);
				break;
			default:
				$result = false;
				break;
		}
		return $result && parent::infoCheck($gm, $rec, $loginUserType, $loginUserRank);
	}


	/**
	 * 閲覧可能ユーザーか確認。
	 */
	function userCheck( $rec )
	{
		// **************************************************************************************
		global $loginUserType;
		global $LOGIN_ID;
		// **************************************************************************************

		$db = GMList::getDB('message');

		$userId = '';
		switch( $_GET['pal'] )
		{
		case 'rec':  $userId = $db->getData($rec, 'destination');	 break;
		case 'send': $userId = $db->getData($rec, 'owner');	 break;
		}

		return  ($userId == $LOGIN_ID);
	}

	function getDesign( $design )
	{
		switch($_GET['pal'])
		{
		case 'rec':  $design = $design.'_REC'; break;
		case 'send': $design = $design.'_SEND'; break;
		case 'client': $design = $design.'_CLIENT'; break;
		case 'member_apply':  $design = $design.'_MEMBER_APPLY'; break;
		case 'member_invi':  $design = $design.'_MEMBER_INVI'; break;
		default:		$design .= '';  break;
		}

		return $design;
	}

}