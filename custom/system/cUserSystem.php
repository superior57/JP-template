<?php

include_once "custom/system/userSystem.php";

class cUserSystem extends UserSystem
{
	/**
	 * 登録前段階処理。
	 * フォーム入力以外の方法でデータを登録する場合は、ここでレコードに値を代入します。
	 *
	 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
	 * @param rec フォームのからの入力データを反映したレコードデータ。
	 */
	function registProc( &$gm, &$rec, $loginUserType, $loginUserRank ,$check = false)
	{
		global $ACTIVE_ACCEPT;
		$db		 = $gm[ $_GET['type'] ]->getDB();

		if($loginUserType=="admin" && !is_null($_POST["dotw"])){
			$_POST["dotw_text"]=implode("/",$_POST["dotw"]);
//			$db->setData($rec,"activate",$ACTIVE_ACCEPT);
		}
		parent::registProc( $gm, $rec, $loginUserType, $loginUserRank, $check );

		if(SystemUtil::existsModule("directMail")){
			$db->setData( $rec, 'list_id', "ML000002" );
		}

		$db->setData( $rec, 'inquiry', "on" );
		$db->setData($rec,"edit_comp",false);
		$db->setData( $rec, 'logout',	time() );
		$db->setData( $rec, 'edit', time() );

		if($check){

			if(preg_match("/sun/",$_POST["dotw_text"])){
				$db->setData( $rec, 'start_sun_am',mktime($_POST["start_sun_am_hour"],$_POST["start_sun_am_minute"],0,0,0,0));
				$db->setData( $rec, 'start_sun_pm',mktime($_POST["start_sun_pm_hour"],$_POST["start_sun_pm_minute"],0,0,0,0));
				$db->setData( $rec, 'end_sun_am',mktime($_POST["end_sun_am_hour"],$_POST["end_sun_am_minute"],0,0,0,0));
				$db->setData( $rec, 'end_sun_pm',mktime($_POST["end_sun_pm_hour"],$_POST["end_sun_pm_minute"],0,0,0,0));
			}else{
				$db->setData( $rec, 'start_sun_am',0);
				$db->setData( $rec, 'start_sun_pm',0);
				$db->setData( $rec, 'end_sun_am',0);
				$db->setData( $rec, 'end_sun_pm',0);
			}

			if(preg_match("/mon/",$_POST["dotw_text"])){
				$db->setData( $rec, 'start_mon_am',mktime($_POST["start_mon_am_hour"],$_POST["start_mon_am_minute"],0,0,0,0));
				$db->setData( $rec, 'start_mon_pm',mktime($_POST["start_mon_pm_hour"],$_POST["start_mon_pm_minute"],0,0,0,0));
				$db->setData( $rec, 'end_mon_am',mktime($_POST["end_mon_am_hour"],$_POST["end_mon_am_minute"],0,0,0,0));
				$db->setData( $rec, 'end_mon_pm',mktime($_POST["end_mon_pm_hour"],$_POST["end_mon_pm_minute"],0,0,0,0));
			}else{
				$db->setData( $rec, 'start_mon_am',0);
				$db->setData( $rec, 'start_mon_pm',0);
				$db->setData( $rec, 'end_mon_am',0);
				$db->setData( $rec, 'end_mon_pm',0);
			}

			if(preg_match("/tue/",$_POST["dotw_text"])){
				$db->setData( $rec, 'start_tue_am',mktime($_POST["start_tue_am_hour"],$_POST["start_tue_am_minute"],0,0,0,0));
				$db->setData( $rec, 'start_tue_pm',mktime($_POST["start_tue_pm_hour"],$_POST["start_tue_pm_minute"],0,0,0,0));
				$db->setData( $rec, 'end_tue_am',mktime($_POST["end_tue_am_hour"],$_POST["end_tue_am_minute"],0,0,0,0));
				$db->setData( $rec, 'end_tue_pm',mktime($_POST["end_tue_pm_hour"],$_POST["end_tue_pm_minute"],0,0,0,0));
			}else{
				$db->setData( $rec, 'start_tue_am',0);
				$db->setData( $rec, 'start_tue_pm',0);
				$db->setData( $rec, 'end_tue_am',0);
				$db->setData( $rec, 'end_tue_pm',0);
			}

			if(preg_match("/wed/",$_POST["dotw_text"])){
				$db->setData( $rec, 'start_wed_am',mktime($_POST["start_wed_am_hour"],$_POST["start_wed_am_minute"],0,0,0,0));
				$db->setData( $rec, 'start_wed_pm',mktime($_POST["start_wed_pm_hour"],$_POST["start_wed_pm_minute"],0,0,0,0));
				$db->setData( $rec, 'end_wed_am',mktime($_POST["end_wed_am_hour"],$_POST["end_wed_am_minute"],0,0,0,0));
				$db->setData( $rec, 'end_wed_pm',mktime($_POST["end_wed_pm_hour"],$_POST["end_wed_pm_minute"],0,0,0,0));
			}else{
				$db->setData( $rec, 'start_wed_am',0);
				$db->setData( $rec, 'start_wed_pm',0);
				$db->setData( $rec, 'end_wed_am',0);
				$db->setData( $rec, 'end_wed_pm',0);
			}

			if(preg_match("/thr/",$_POST["dotw_text"])){
				$db->setData( $rec, 'start_thr_am',mktime($_POST["start_thr_am_hour"],$_POST["start_thr_am_minute"],0,0,0,0));
				$db->setData( $rec, 'start_thr_pm',mktime($_POST["start_thr_pm_hour"],$_POST["start_thr_pm_minute"],0,0,0,0));
				$db->setData( $rec, 'end_thr_am',mktime($_POST["end_thr_am_hour"],$_POST["end_thr_am_minute"],0,0,0,0));
				$db->setData( $rec, 'end_thr_pm',mktime($_POST["end_thr_pm_hour"],$_POST["end_thr_pm_minute"],0,0,0,0));
			}else{
				$db->setData( $rec, 'start_thr_am',0);
				$db->setData( $rec, 'start_thr_pm',0);
				$db->setData( $rec, 'end_thr_am',0);
				$db->setData( $rec, 'end_thr_pm',0);
			}

			if(preg_match("/fri/",$_POST["dotw_text"])){
				$db->setData( $rec, 'start_fri_pm',mktime($_POST["start_fri_pm_hour"],$_POST["start_fri_pm_minute"],0,0,0,0));
				$db->setData( $rec, 'start_fri_am',mktime($_POST["start_fri_am_hour"],$_POST["start_fri_am_minute"],0,0,0,0));
				$db->setData( $rec, 'end_fri_am',mktime($_POST["end_fri_am_hour"],$_POST["end_fri_am_minute"],0,0,0,0));
				$db->setData( $rec, 'end_fri_pm',mktime($_POST["end_fri_pm_hour"],$_POST["end_fri_pm_minute"],0,0,0,0));
			}else{
				$db->setData( $rec, 'start_fri_am',0);
				$db->setData( $rec, 'start_fri_pm',0);
				$db->setData( $rec, 'end_fri_am',0);
				$db->setData( $rec, 'end_fri_pm',0);
			}

			if(preg_match("/sat/",$_POST["dotw_text"])){
				$db->setData( $rec, 'end_sat_pm',mktime($_POST["end_sat_pm_hour"],$_POST["end_sat_pm_minute"],0,0,0,0));
				$db->setData( $rec, 'end_sat_am',mktime($_POST["end_sat_am_hour"],$_POST["end_sat_am_minute"],0,0,0,0));
				$db->setData( $rec, 'start_sat_am',mktime($_POST["start_sat_am_hour"],$_POST["start_sat_am_minute"],0,0,0,0));
				$db->setData( $rec, 'start_sat_pm',mktime($_POST["start_sat_pm_hour"],$_POST["start_sat_pm_minute"],0,0,0,0));
			}else{
				$db->setData( $rec, 'start_sat_am',0);
				$db->setData( $rec, 'start_sat_pm',0);
				$db->setData( $rec, 'end_sat_am',0);
				$db->setData( $rec, 'end_sat_pm',0);
			}
		}
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
		// **************************************************************************************
		$db		 = $gm[ $_GET['type'] ]->getDB();
		if($_GET["rand"]=="true"){
			$sort[0]="desc";
			$sort[1]="asc";
			
			if(rand(1,5)==1){
				$table = $db->sortTable( $table , 'edit' , $sort[rand(0,1)] );
			}elseif(rand(1,5)==2){
				$table = $db->sortTable( $table , 'regist' , $sort[rand(0,1)] );
			}elseif(rand(1,5)==3){
				$table = $db->sortTable( $table , 'login' , $sort[rand(0,1)] );
			}elseif(rand(1,5)==4){
				$table = $db->sortTable( $table , 'logout' , $sort[rand(0,1)] );
			}else{
				$table = $db->sortTable( $table , 'name' , $sort[rand(0,1)] );
			}
			$table = $db->limitOffset( $table , 0 , 5 );
		};
		
		parent::searchProc( $gm, $table, $loginUserType, $loginUserRank );			
	}

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
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		// **************************************************************************************

		$db		 = $gm[ $_GET['type'] ]->getDB();

		$week = array("sun","mon","tue","wed","thr","fri","sat");
		foreach($week as $days){
			if($db->getData( $rec, 'start_'.$days.'_am')>0)
				$_POST["dotw"][]=$days;
		}
		if($_POST["dotw"]){
			$_POST["dotw_text"]=implode("/",$_POST["dotw"]);
		}


		parent::drawInfo( $gm, $rec, $loginUserType, $loginUserRank ); // 二重描画になるので編集する場合は削除
	}

	/**
	 * 編集フォームを描画する。
	 *
	 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
	 * @param rec 編集対象のレコードデータ
	 * @param loginUserType ログインしているユーザの種別
	 * @param loginUserRank ログインしているユーザの権限
	 */
	function drawEditForm( &$gm, &$rec, $loginUserType, $loginUserRank )
	{
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		// **************************************************************************************

		$db		 = $gm[ $_GET['type'] ]->getDB();

		$week = array("sun","mon","tue","wed","thr","fri","sat");
		foreach($week as $days){
			if($db->getData( $rec, 'start_'.$days.'_am')>0)
				$_POST["dotw"][]=$days;
		}

		parent::drawEditForm( $gm, $rec, $loginUserType, $loginUserRank ); // 二重描画になるので編集する場合は削除
	}


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
		// **************************************************************************************

		$db		 = $gm[ $_GET['type'] ]->getDB();
				$db->setData($rec,"edit_comp",true);

		if(!is_null($_POST["dotw"])){
			$_POST["dotw_text"]=implode("/",$_POST["dotw"]);
		}

		parent::editProc( $gm, $rec, $loginUserType, $loginUserRank ,$check );

		if($check){

			if(preg_match("/sun/",$_POST["dotw_text"])){
				$db->setData( $rec, 'start_sun_am',mktime($_POST["start_sun_am_hour"],$_POST["start_sun_am_minute"],0,0,0,0));
				$db->setData( $rec, 'start_sun_pm',mktime($_POST["start_sun_pm_hour"],$_POST["start_sun_pm_minute"],0,0,0,0));
				$db->setData( $rec, 'end_sun_am',mktime($_POST["end_sun_am_hour"],$_POST["end_sun_am_minute"],0,0,0,0));
				$db->setData( $rec, 'end_sun_pm',mktime($_POST["end_sun_pm_hour"],$_POST["end_sun_pm_minute"],0,0,0,0));
			}else{
				$db->setData( $rec, 'start_sun_am',0);
				$db->setData( $rec, 'start_sun_pm',0);
				$db->setData( $rec, 'end_sun_am',0);
				$db->setData( $rec, 'end_sun_pm',0);
			}

			if(preg_match("/mon/",$_POST["dotw_text"])){
				$db->setData( $rec, 'start_mon_am',mktime($_POST["start_mon_am_hour"],$_POST["start_mon_am_minute"],0,0,0,0));
				$db->setData( $rec, 'start_mon_pm',mktime($_POST["start_mon_pm_hour"],$_POST["start_mon_pm_minute"],0,0,0,0));
				$db->setData( $rec, 'end_mon_am',mktime($_POST["end_mon_am_hour"],$_POST["end_mon_am_minute"],0,0,0,0));
				$db->setData( $rec, 'end_mon_pm',mktime($_POST["end_mon_pm_hour"],$_POST["end_mon_pm_minute"],0,0,0,0));
			}else{
				$db->setData( $rec, 'start_mon_am',0);
				$db->setData( $rec, 'start_mon_pm',0);
				$db->setData( $rec, 'end_mon_am',0);
				$db->setData( $rec, 'end_mon_pm',0);
			}

			if(preg_match("/tue/",$_POST["dotw_text"])){
				$db->setData( $rec, 'start_tue_am',mktime($_POST["start_tue_am_hour"],$_POST["start_tue_am_minute"],0,0,0,0));
				$db->setData( $rec, 'start_tue_pm',mktime($_POST["start_tue_pm_hour"],$_POST["start_tue_pm_minute"],0,0,0,0));
				$db->setData( $rec, 'end_tue_am',mktime($_POST["end_tue_am_hour"],$_POST["end_tue_am_minute"],0,0,0,0));
				$db->setData( $rec, 'end_tue_pm',mktime($_POST["end_tue_pm_hour"],$_POST["end_tue_pm_minute"],0,0,0,0));
			}else{
				$db->setData( $rec, 'start_tue_am',0);
				$db->setData( $rec, 'start_tue_pm',0);
				$db->setData( $rec, 'end_tue_am',0);
				$db->setData( $rec, 'end_tue_pm',0);
			}

			if(preg_match("/wed/",$_POST["dotw_text"])){
				$db->setData( $rec, 'start_wed_am',mktime($_POST["start_wed_am_hour"],$_POST["start_wed_am_minute"],0,0,0,0));
				$db->setData( $rec, 'start_wed_pm',mktime($_POST["start_wed_pm_hour"],$_POST["start_wed_pm_minute"],0,0,0,0));
				$db->setData( $rec, 'end_wed_am',mktime($_POST["end_wed_am_hour"],$_POST["end_wed_am_minute"],0,0,0,0));
				$db->setData( $rec, 'end_wed_pm',mktime($_POST["end_wed_pm_hour"],$_POST["end_wed_pm_minute"],0,0,0,0));
			}else{
				$db->setData( $rec, 'start_wed_am',0);
				$db->setData( $rec, 'start_wed_pm',0);
				$db->setData( $rec, 'end_wed_am',0);
				$db->setData( $rec, 'end_wed_pm',0);
			}

			if(preg_match("/thr/",$_POST["dotw_text"])){
				$db->setData( $rec, 'start_thr_am',mktime($_POST["start_thr_am_hour"],$_POST["start_thr_am_minute"],0,0,0,0));
				$db->setData( $rec, 'start_thr_pm',mktime($_POST["start_thr_pm_hour"],$_POST["start_thr_pm_minute"],0,0,0,0));
				$db->setData( $rec, 'end_thr_am',mktime($_POST["end_thr_am_hour"],$_POST["end_thr_am_minute"],0,0,0,0));
				$db->setData( $rec, 'end_thr_pm',mktime($_POST["end_thr_pm_hour"],$_POST["end_thr_pm_minute"],0,0,0,0));
			}else{
				$db->setData( $rec, 'start_thr_am',0);
				$db->setData( $rec, 'start_thr_pm',0);
				$db->setData( $rec, 'end_thr_am',0);
				$db->setData( $rec, 'end_thr_pm',0);
			}

			if(preg_match("/fri/",$_POST["dotw_text"])){
				$db->setData( $rec, 'start_fri_pm',mktime($_POST["start_fri_pm_hour"],$_POST["start_fri_pm_minute"],0,0,0,0));
				$db->setData( $rec, 'start_fri_am',mktime($_POST["start_fri_am_hour"],$_POST["start_fri_am_minute"],0,0,0,0));
				$db->setData( $rec, 'end_fri_am',mktime($_POST["end_fri_am_hour"],$_POST["end_fri_am_minute"],0,0,0,0));
				$db->setData( $rec, 'end_fri_pm',mktime($_POST["end_fri_pm_hour"],$_POST["end_fri_pm_minute"],0,0,0,0));
			}else{
				$db->setData( $rec, 'start_fri_am',0);
				$db->setData( $rec, 'start_fri_pm',0);
				$db->setData( $rec, 'end_fri_am',0);
				$db->setData( $rec, 'end_fri_pm',0);
			}

			if(preg_match("/sat/",$_POST["dotw_text"])){
				$db->setData( $rec, 'end_sat_pm',mktime($_POST["end_sat_pm_hour"],$_POST["end_sat_pm_minute"],0,0,0,0));
				$db->setData( $rec, 'end_sat_am',mktime($_POST["end_sat_am_hour"],$_POST["end_sat_am_minute"],0,0,0,0));
				$db->setData( $rec, 'start_sat_am',mktime($_POST["start_sat_am_hour"],$_POST["start_sat_am_minute"],0,0,0,0));
				$db->setData( $rec, 'start_sat_pm',mktime($_POST["start_sat_pm_hour"],$_POST["start_sat_pm_minute"],0,0,0,0));
			}else{
				$db->setData( $rec, 'start_sat_am',0);
				$db->setData( $rec, 'start_sat_pm',0);
				$db->setData( $rec, 'end_sat_am',0);
				$db->setData( $rec, 'end_sat_pm',0);
			}
		}
	}

	/**
	 * 削除処理。
	 * 削除を実行する前に実行したい処理があれば、ここに記述します。
	 * 例えばユーザデータを削除する際にユーザデータに紐付けられたデータを削除する際などに有効です。
	 *
	 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
	 * @param rec フォームのからの入力データを反映したレコードデータ。
	 */
	function deleteProc(&$gm, &$rec, $loginUserType, $loginUserRank)
	{
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $LOGIN_ID;
		// **************************************************************************************

		$db = $gm[$_GET['type']]->getDB();

		// 企業情報削除時にはその企業が登録したアイテムも削除
		if ($loginUserType == 'admin') {
			$id = $_GET['id'];
		} else {
			$id = $LOGIN_ID;
		}
		// 中途採用求人の論理削除
		$mdb = $gm['mid']->getDB();
		$table = $mdb->searchTable($mdb->getTable(), 'owner', '=', $id);
		$mdb->setTableDataUpdate($table, "delete_flg", true);
		$mdb->setTableDataUpdate($table, "delete_date", time());
		// 新卒採用求人の論理削除
		$fdb = $gm['fresh']->getDB();
		$table = $fdb->searchTable($fdb->getTable(), 'owner', '=', $id);
		$fdb->setTableDataUpdate($table, "delete_flg", true);
		$fdb->setTableDataUpdate($table, "delete_date", time());

		// 企業に退会通知を送信
		MailLogic::userDeleteComp($rec, $_GET['type']);

		// レコードを削除
		$db->deleteRecord($rec);

		// 管理者に企業が退会した旨を通知
		if ($loginUserType != "admin") MailLogic::noticeResigns("cUser", $rec);
	}

	/**
	 * 復元完了処理。
	 * 復元完了時に実行したい処理があればココに記述します。
	 *
	 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
	 * @param rec フォームのからの入力データを反映したレコードデータ。
	 */
	function restoreComp( &$gm, &$rec, &$old_rec, $loginUserType, $loginUserRank )
	{
		$db = $gm['cUser']->getDB();
		$id = $db->getData( $rec, 'id' );
		$delete_time = $db->getData( $old_rec, 'delete_time' );

		$rdb = $gm['request']->getDB();
		$table = $rdb->searchTable( $rdb->getTable('d'), 'owner', '=', $id );
		$table = $rdb->searchTable( $table, 'delete_time', '=', $delete_time );

		$rdb->restoreTable($table);
	}

/*
	function feedProc( $table = null )
	{
		$db = GMList::getDB( 'cUser' );

		if(!Conf::checkData("user", "feed", "cUser"))
			return;

		if( !$table )
			{ $table = $db->getTable(); }

		$table = $db->searchTable( $table , 'activate' , '=' , 4 );
		$table = $db->sortTable( $table , 'regist' , 'desc' );
		$table = $db->limitOffset( $table , 0 , 10 );

		return parent::feedProc( $table );
	}
*/
	function feedProc($table = null) {
		$db = GMList::getDB('cUser');

		if (Conf::checkData('user', 'feed', 'cUser')) {
			$table = cUserLogic::getTable(null, null, 'nobody');
			$table = $db->getDistinct($table);
		} else {
			$table = $db->getEmptyTable();
		}
		return parent::feedProc($table);
	}

}

?>
