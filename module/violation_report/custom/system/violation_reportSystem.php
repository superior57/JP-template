<?php

class violation_reportSystem extends System
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
		global $ACTIVE_NONE;
		// **************************************************************************************

		parent::registProc( $gm, $rec, $loginUserType, $loginUserRank, $check );

		$db	 = $gm[ $_GET['type'] ]->getDB();
		$cid = $db->getData($rec,"cid");


		$db->setData( $rec, 'owner', $LOGIN_ID );
		//$db->setData( $rec, 'sowner', $cid );
		$db->setData( $rec, 'activate', $ACTIVE_NONE );
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
		global $ACTIVE_ACCEPT;
		// **************************************************************************************

		$type = SearchTableStack::getType();



		$db		 = $gm[ $type ]->getDB();

		if(isset($_GET["name"]) && strlen($_GET["name"])){
			$table = reviewLogic::searchName($db,$table,$_GET);
		}
		switch($loginUserType)
		{
		case 'admin':
			break;
		case 'cUser':
			if($_GET["sowner"]==$LOGIN_ID){
				break;
			}
			$table = $db->searchTable(  $table, 'owner', '=', $LOGIN_ID  );
			break;
		case 'nUser':
			$table = $db->searchTable(  $table, 'owner', '=', $LOGIN_ID  );
			break;
		case 'nobody':
			$table = $db->searchTable(  $table, 'activate', '=', $ACTIVE_ACCEPT  );
			break;
		}
	}
}

?>
