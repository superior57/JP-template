<?php

include_once "custom/system/userSystem.php";

class nUserSystem extends UserSystem
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
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $LOGIN_ID;
		// **************************************************************************************

		$db = $gm[$_GET["type"]]->getDB();
		$db->setData($rec,"edit_comp",true);

		if($loginUserType=="nUser"){
			if($db->getData($rec,"view_mode"))viewMode::setViewMode($db->getData($rec,"view_mode"));
		}

		if(SystemUtil::existsModule("directMail")){
			$db->setData( $rec, 'list_id', "ML000001" );
		}
		$db->setData( $rec, 'edit', time() );

		parent::registProc( $gm, $rec, $loginUserType, $loginUserRank, $check );
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
		global $LOGIN_ID;
		// **************************************************************************************

		if($loginUserType=="nUser"){
			$db = $gm[$_GET["type"]]->getDB();
			$db->setData($rec,"edit_comp",true);
			if($db->getData($rec,"view_mode")) viewMode::setViewMode($db->getData($rec,"view_mode"));
		}

		parent::editProc( $gm, $rec, $loginUserType, $loginUserRank ,$check );
	}

	function editComp( &$gm, &$rec, &$old_rec, $loginUserType, $loginUserRank )
	{
		$db = $gm[$_GET["type"]]->getDB();
		$owner = $db->getData($rec,"id");
		$adds = $db->getData($rec,"adds");

		resumeLogic::updateAdds($owner, $adds);

		parent::editComp($gm, $rec, $old_rec, $loginUserType, $loginUserRank);
	}

	function registComp(&$gm, &$rec, $loginUserType, $loginUserRank){
		$db = GMList::getDB("nUser");
		nUserLogic::registInit($db->getData($rec, "id"));
		parent::registComp($gm, $rec, $loginUserType, $loginUserRank);
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
		if ($loginUserType != "admin") MailLogic::noticeResigns("nUser", $rec);

		// 求職者の履歴書を削除
		$db = $gm[$_GET["type"]]->getDB();
		$userID = $db->getData($rec, "id");
		resumeLogic::delete($userID);

		// 求職者に退会通知を送信
		MailLogic::userDeleteComp($rec, $_GET['type']);

		// 削除処理
		parent::deleteProc($gm, $rec, $loginUserType, $loginUserRank);
	}

	function feedProc( $table = null )
	{
		$db = GMList::getDB( 'nUser' );

		if( Conf::checkData( 'user', 'feed', 'nUser' ) ){
			$table = nUserLogic::getTable(null,null,'nobody');
		}else{
			$table = $db->getEmptyTable();
		}

		return parent::feedProc( $table );
	}
}


