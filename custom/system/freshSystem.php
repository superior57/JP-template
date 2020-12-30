<?php

class freshSystem extends System
{


	/**
	 * 編集前段階処理。
	 * フォーム入力以外の方法でデータを登録する場合は、ここでレコードに値を代入します。
	 *
	 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
	 * @param rec フォームのからの入力データを反映したレコードデータ。
	 */
	function editProc( &$gm, &$rec, $loginUserType, $loginUserRank ,$check = false)
	{
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $LOGIN_ID;
		global $ACTIVE_NONE;
		// **************************************************************************************

		parent::editProc( $gm, $rec, $loginUserType, $loginUserRank ,$check );

		$db	 = $gm[ $_GET['type'] ]->getDB();

		if($db->getData($rec,"use_limit_time_apply")==false) $db->setData( $rec, 'limits', 0 );

		if($db->getData($rec,"attention")==false){
			$db->setData( $rec, 'attention_time', 0 );
		}

		if(Conf::checkData("charges", "apply", "off") && Conf::checkData("charges", "employment", "on")){
			$db->setData($rec,"term_type","employment");
		}elseif(Conf::checkData("charges", "apply", "on") && Conf::checkData("charges", "employment", "off")){
			$db->setData($rec,"term_type","apply");
		}

		if( $loginUserType == 'cUser' )
		{
			if( Conf::checkData( 'job', 'ad_check', 'edit' ) ) { $db->setData( $rec, 'activate', $ACTIVE_NONE ); }
		}

	}

	/**
	 * 登録内容確認ページを描画する。
	 *
	 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
	 * @param rec 登録情報を格納したレコードデータ
	 * @param loginUserType ログインしているユーザの種別
	 * @param loginUserRank ログインしているユーザの権限
	 */
	function drawRegistCheck(&$gm, $rec, $loginUserType, $loginUserRank)
	{
		foreach ($_POST as $key => $value) {
			if (strpos($key, 'limits') !== false || strpos($key, 'attention') !== false) {
				$gm[$_GET['type']]->addHiddenForm($key, $value);
			}
		}
		parent::drawRegistCheck($gm, $rec, $loginUserType, $loginUserRank);
	}

	/**
	 * 編集内容確認ページを描画する。
	 *
	 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
	 * @param rec 編集対象のレコードデータ
	 * @param loginUserType ログインしているユーザの種別
	 * @param loginUserRank ログインしているユーザの権限
	 */
	function drawEditCheck(&$gm, &$rec, $loginUserType, $loginUserRank)
	{
		foreach ($_POST as $key => $value) {
			if (strpos($key, 'limits') !== false || strpos($key, 'attention') !== false) {
				$gm[$_GET['type']]->addHiddenForm($key, $value);
			}
		}
		parent::drawEditCheck($gm, $rec, $loginUserType, $loginUserRank);
	}

	/**
	 * 編集完了処理。
	 * フォーム入力以外の方法でデータを登録する場合は、ここでレコードに値を代入します。
	 *
	 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
	 * @param rec フォームのからの入力データを反映したレコードデータ。
	 */
	function editComp( &$gm, &$rec, &$oldRec, $loginUserType, $loginUserRank )
	{
		global $ACTIVE_ACCEPT;

		JobLogic::AdjustDayEndTime($rec);

		$db = $gm[$_GET["type"]]->getDB();
		$old = $db->getData($oldRec,"activate");
		$new = $db->getData($rec,"activate");

		if($old != $new && $new == $ACTIVE_ACCEPT)
			MailLogic::noticeProjectActivate("fresh",$rec);

		MailLogic::editNotice( $rec, $_GET['type'] );
		MailLogic::noticeNewPending($rec, $_GET['type'], 'edit');
		$USE_CRON = Conf::getData('sitemap', 'use_cron');
		if($old != $new && !$USE_CRON){
			SitemapLogic::create();
		}
	}

	function deleteProc( &$gm, &$rec, $loginUserType, $loginUserRank )
	{
		global $ACTIVE_NONE;

		$db		 = $gm[ $_GET['type'] ]->getDB();
		$id = $db->getData($rec,"id");

		if(entryLogic::existsApply($id)){
			$db->setData($rec,"delete_flg",true);
			$db->setData($rec,"delete_date",time());
			$db->setData($rec,"activate",$ACTIVE_NONE);
			$db->setData($rec,"publish","off");
			$db->updateRecord($rec);
		}else{
			$db->deleteRecord($rec);
		}
	}

	function registCheck( &$gm, $edit, $loginUserType, $loginUserRank ){
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $LOGIN_ID;
		// **************************************************************************************
		$check = parent::registCheck($gm, $edit, $loginUserType, $loginUserRank);

		// 求人登録数のチェック
		$limit = Conf::getData( 'job', 'max' );

		if( $limit > 0 )
		{
			if(!$edit) self::$checkData->limitCheck( $LOGIN_ID, $limit );
		}

		if(!$edit)
		{
			if( cUserLogic::getJobCharges($LOGIN_ID,$_GET["type"]) == 'malti' )
			{
				switch($_POST['term_type'])
				{
					case 'apply':
					case 'employment':
						break;
					default:
						self::$checkData->addError('term_type');
						break;
				}
			}
		}

		return $check && self::$checkData->getCheck();
	}

	/**
	 * 登録前段階処理。
	 * フォーム入力以外の方法でデータを登録する場合は、ここでレコードに値を代入します。
	 *
	 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
	 * @param rec フォームのからの入力データを反映したレコードデータ。
	 */
	function registProc( &$gm, &$rec, $loginUserType, $loginUserRank ,$check = false)
	{
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $LOGIN_ID;
		global $ACTIVE_NONE;
		global $ACTIVE_ACCEPT;
		// **************************************************************************************

		parent::registProc( $gm, $rec, $loginUserType, $loginUserRank, $check );

		$db	 = $gm[ $_GET['type'] ]->getDB();
		$db->setData( $rec, 'owner', $LOGIN_ID );
		$db->setData( $rec, 'activate', $ACTIVE_ACCEPT );
		$db->setData( $rec, 'edit', time() );

		if($_POST["use_limit_time_apply"]=="FALSE") $db->setData( $rec, 'limits', 0 );

		if( Conf::checkData( 'job', 'ad_check', 'regist' ) )
		{
			$db->setData( $rec, 'activate', $ACTIVE_NONE );
		}

		JobLogic::setParam( $db, $rec ,$_GET["type"]);
	}

	/**
	 * 登録処理完了処理。
	 * 登録完了時にメールで内容を通知したい場合などに用います。
	 *
	 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
	 * @param rec レコードデータ。
	 */
	function registComp(&$gm, &$rec, $loginUserType, $loginUserRank)
	{
		global $ACTIVE_ACCEPT;
		JobLogic::AdjustDayEndTime($rec);
		MailLogic::noticeNewPending($rec, $_GET['type'], 'regist');

		$db = $gm[$_GET["type"]]->getDB();
		$activate = $db->getData($rec,"activate");

		$USE_CRON = Conf::getData('sitemap', 'use_cron');
		if($activate == $ACTIVE_ACCEPT && !$USE_CRON){
			SitemapLogic::create();
		}
	}

	/**
	 * 登録フォームを描画する。
	 *
	 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
	 * @param loginUserType ログインしているユーザの種別
	 * @param loginUserRank ログインしているユーザの権限
	 */
	function drawRegistForm( &$gm, $rec, $loginUserType, $loginUserRank )
	{
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $LOGIN_ID;
		// **************************************************************************************


		$this->setErrorMessage($gm[ $_GET['type'] ]);
		if( 'normal' == WS_SYSTEM_SYSTEM_FORM_ACTON )
		{
			$action = 'index.php?app_controller=register&type=' . $_GET[ 'type' ];
		}
		else if( 'index' == WS_SYSTEM_SYSTEM_FORM_ACTON )
		{
			$action = 'index.php?app_controller=register&type=' . $_GET[ 'type' ];
		}
		else
		{ $action = ' ';
		}
		if( JobLogic::checkRegistMax($LOGIN_ID) )
		{
			if($gm[$_GET['type']]->maxStep >= 2)
				Template::drawTemplate( $gm[ $_GET['type'] ] , $rec , $loginUserType , $loginUserRank , $_GET['type'] , 'REGIST_FORM_PAGE_DESIGN' . $_POST['step'] , $action );
			else
				Template::drawTemplate( $gm[ $_GET['type'] ] , $rec , $loginUserType , $loginUserRank , $_GET['type'] , 'REGIST_FORM_PAGE_DESIGN' , $action );
		}
		else
		{// 求人登録上限に達している
			Template::drawTemplate( $gm[ $_GET['type'] ] , $rec , $loginUserType , $loginUserRank , $_GET['type'] , 'REGIST_LIMIT_PAGE_DESIGN', $action );
		}

	}


	function feedProc( $table = null )
	{
		$db = GMList::getDB( 'fresh' );

		if(!Conf::checkData("job", "feed", "fresh"))
			return;

		if (!$table) {
			$table = JobLogic::getTable('fresh', null, null, "nobody");
		}

		$table = $db->searchTable( $table , 'publish' , '=' , 'on' );
		$table = $db->searchTable( $table , 'activate' , '=' , 4 );
		$table = $db->sortTable( $table , 'edit' , 'desc' );
		$table = $db->limitOffset( $table , 0 , 10 );

		return parent::feedProc( $table );
	}

	/**
	 * 検索処理。
	 * フォーム入力以外の方法で検索条件を設定したい場合に利用します。
	 *
	 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
	 * @param table フォームのからの入力内容に一致するレコードを格納したテーブルデータ。
	 */
	function searchProc( &$gm, &$table, $loginUserType, $loginUserRank )
	{
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $LOGIN_ID;
		// **************************************************************************************

		$db		 = $gm[ $_GET['type'] ]->getDB();

		$table = JobLogic::getTable( "fresh", $table, $_GET );			//デフォルト検索条件をセット
		$table = JobLogic::searchTrain( $db, $table, $_GET ); // 電車関連の検索条件をセット
		$table = JobLogic::searchFreeword($db, $table, $_GET); // フリーワードの検索条件をセット

		if( $_GET['attention'][0] == 1 )
			{ $table = $db->searchTable(  $table, 'attention_time', '>=', time() ); }

		if( strlen($_GET['s_salary']) )
			{ $table = $db->searchTable(  $table, 'salary', '>=', $_GET['s_salary'] ); }

		if( !empty($_GET['sort']) && !empty($_GET['sort_PAL'][0]) )
			{ $table = $db->sortTable( $table, $_GET['sort'],$_GET['sort_PAL'][0] ); }

		if($_GET["pid"] && SystemUtil::existsModule("special")){
			specialLogic::updateJobSpecial($db, $table, $_GET);
		}

		$table = JobLogic::sortTotal($db, $table, $_GET);

		//rand=trueの場合はランダム表示、件数はテンプレ側でlimit指定
		if($_GET["rand"]){
			//$table = $db->getColumn("id", $table );
			$table = $db->sortReset( $table );
			$table = $db->sortRandom( $table );
		}
	}


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
		case 'cUser':
			$result = true;
			break;
		case 'aUser':
		}

		return $result;
	}

	/**
	 * 詳細情報が閲覧されたときに表示して良い情報かを返すメソッド。
	 * activateカラムや公開可否フラグ、registやupdate等による表示期間の設定、アクセス権限によるフィルタなどを行います。
	 *
	 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
	 * @param rec アクセスされたレコードデータ。
	 * @return 表示して良いかどうかを真偽値で渡す。
	 */
	function infoCheck( &$gm, &$rec, $loginUserType, $loginUserRank )
	{
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $ACTIVE_ACCEPT;
		global $LOGIN_ID;
		// **************************************************************************************

		$db	 = $gm[ $_GET['type'] ]->getDB();
		$id = $db->getData($rec,"id");
		$owner = $db->getData($rec,"owner");
		$deleteFlg = $db->getData( $rec , 'delete_flg' );
		$uselimits = $db->getData( $rec , 'use_limit_time_apply' );
		$limits = $db->getData( $rec , 'limits' );

		$result = true;
		switch($loginUserType)
		{
			case 'admin':
				break;
			case 'cUser':
				if($db->getData( $rec , 'delete_flg' ))
					$result = false;

				if( $owner != $LOGIN_ID )
					$result = false;
				break;
			case 'nUser':
			default:
				if(!$deleteFlg){
					if( $db->getData( $rec , 'activate' ) != $ACTIVE_ACCEPT ){
						$result = false;
					}
				}

				//求人企業の課金有効判定
				Concept::IsTrue(pay_jobLogic::isAvailable($owner, "fresh"))->OrThrow("expiredJob");

				//公開期限判定
				Concept::IsTrue(!($uselimits && $limits < time()))->OrThrow("nonPeriodic");
				break;
		}

		return $result;
	}

	/**
	 * 詳細情報前処理。
	 * 簡易情報変更で利用
	 *
	 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
	 * @param rec アクセスされたレコードデータ。
	 */
	function infoProc( &$gm, &$rec, $loginUserType, $loginUserRank )
	{
		global $ACTIVE_ACCEPT;

		// 簡易情報変更（情報ページからの内容変更処理）
		if(  isset( $_POST['post'] ) ){
			if( $loginUserType == 'admin' ){
				$db		 = $gm[ $_GET['type'] ]->getDB();

				for( $i=0; $i<count($db->colName); $i++ ){
					if(   isset(   $_POST[ $db->colName[$i] ]  )   ){
						switch ($db->colName[$i]){
							case "activate":
								if($db->getData($rec, $db->colName[$i]) !== $_POST[$db->colName[$i]]){
									if($_POST["activate"] == $ACTIVE_ACCEPT) MailLogic::noticeProjectActivate("fresh", $rec);
								}
								break;
							case "attention":
								if($_POST["attention"]=="TRUE" && !empty($_POST["attention_time_year"]) && !empty($_POST["attention_time_month"])  && !empty($_POST["attention_time_day"])){
									$atTime = SystemUtil::createEpochTime(mktime(0,0,0,$_POST["attention_time_month"],$_POST["attention_time_day"],$_POST["attention_time_year"]),'de');
									$db->setData($rec,"attention_time",$atTime);
								}elseif($_POST["attention"]=="FALSE"){
									$db->setData($rec,"attention_time",0);
								}
								break;
							default:
								break;
						}
						$db->setData( $rec, $db->colName[$i], $_POST[ $db->colName[$i] ] );
					}
				}
				$db->updateRecord( $rec );
				SystemUtil::async( 'FeedApi', 'update', array( 'targetType' => $_GET['type'] ) );
			}
		}
	}

	function drawInfo( &$gm, &$rec, $loginUserType, $loginUserRank )
	{
		global $LOGIN_ID;
		$db = $gm[ $_GET['type'] ]->getDB();

		$publish = $db->getData($rec,"publish");
		$id = $db->getData($rec,"id");
		$owner = $db->getData($rec,"owner");
		$designType = $_GET['type'];

		$label = 'INFO_PAGE_DESIGN';
		if( $db->getData($rec,"delete_flg") && !in_array($loginUserType,array("admin","cUser") )){
			$label = 'INFO_LOGICAL_DELETE_PAGE_DESIGN';
		}else{
			if( !JobLogic::checkNobodyDisp($_GET["type"],$rec) )
				$label = 'INFO_LIMIT_PAGE_DESIGN';

			switch($loginUserType){
				case "nUser":
				case "nobody":
					$existsSocut = messageLogic::getScoutCnt($owner,$LOGIN_ID,$id) > 0;
					if( $publish == "off" && !$existsSocut) {
						$label = 'INFO_NOT_PUBLISHED_PAGE_DESIGN';
					} else if(SystemUtil::existsModule('googlejob')) {
						GoogleJobLogic::setJobRecord($rec);
					}
					break;
			}
		}
		Template::drawTemplate( $gm[ $_GET['type'] ] , $rec , $loginUserType , $loginUserRank ,  $designType, $label , 'index.php?app_controller=info&type='. $_GET['type'] .'&id='. $_GET['id'], Template::getOwner() );
	}

	/**
	 * canonicalタグを出力
	 * 特定の条件で出力内容を変更したい場合は$bufferにその内容を指定してください
	 *
	 * @param gm GUIManagerオブジェクトです。
	 * @param rec 登録情報のレコードデータです。
	 * @param args コマンドコメント引数配列です。　第一引数にIDを渡します。 第二引数にリンクするかを真偽値で渡します。
	 */
	function drawCanonical( &$gm, $rec, $args )
	{
		global $HOME;
		global $STATIC_URL_FLG;

		$buffer = "";
		switch($_GET["app_controller"]) {
			case 'search':
				if($STATIC_URL_FLG && SystemUtil::checkOnlySearch($_GET, 
					array(
						array('category', 'work_place_adds'),
						array('category', 'work_place_adds', 'work_place_add_sub'),
						array('category', 'addition'),
						array('work_place_adds', 'addition'),
						array('work_place_adds', 'work_place_add_sub', 'addition'),
						array('category'),
						array('areaID'),
						array('work_place_adds'),
						array('work_place_adds', 'work_place_add_sub'),
						array('addition')
					), 
					array(
						'foreign_flg',
						'addsID',
						'num', 
						'select_limit',
						'sort', 
						'sort_PAL', 
						'select_sort'
					),
					$valList)
				)
				{
					$buffer = $HOME.SystemUtil::getStaticURL($valList, $_GET['type']);
					$this->addBuffer( '<link rel="canonical" href="'.$buffer.'">' );
					return;
				}
				break;
		}
		parent::drawCanonical( $gm, $rec, $args );
	}

	function drawSearch( &$gm, &$sr, $table, $loginUserType, $loginUserRank ){

		if(SystemUtil::existsModule('csvExport') && isset($_GET['csvExport'])){
			csvExportLogic::exportCsv($table);
}
		parent::drawSearch( $gm, $sr, $table, $loginUserType, $loginUserRank ); // 二重描画になるので編集する場合は削除
	}
}
