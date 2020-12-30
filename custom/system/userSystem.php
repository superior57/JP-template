<?php

class UserSystem extends System
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
		global $ACTIVE_NONE;
		// **************************************************************************************

		parent::registProc( $gm, $rec, $loginUserType, $loginUserRank, $check );

		$db	 = $gm[ $_GET['type'] ]->getDB();

		$db->setData( $rec, 'activate', $ACTIVE_NONE );
		$db->setData( $rec, 'receive_notice', true );
	}

	/**
	 * 登録処理完了処理。
	 * 登録完了時にメールで内容を通知したい場合などに用います。
	 *
	 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
	 * @param rec レコードデータ。
	 */
/*	function registComp( &$gm, &$rec, $loginUserType, $loginUserRank )
	{
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $THIS_TABLE_IS_USERDATA;
		// **************************************************************************************

		$db	 = $gm[ $_GET['type'] ]->getDB();

		// ユーザ情報であればアクティベーションコードを記載したメールを送信。
		if(  $THIS_TABLE_IS_USERDATA[ $_GET['type'] ]  )
		{
			MailLogic::activateCheck( $rec, $_GET['type'] );
		}
	}
*/
	function registComp( &$gm, &$rec, $loginUserType, $loginUserRank ){
		global $ACTIVE_NONE;
		global $ACTIVE_ACCEPT;

		$db	 = $gm[ $_GET['type'] ]->getDB();


		$ad_check = Conf::checkData( 'user', 'ad_check', $_GET['type'] );

		$activate = $ACTIVE_ACCEPT;

		if( $ad_check && $loginUserType != "admin" ){
			switch($_GET['type']){
				case "nUser":
				case "cUser":
					$activate = $ACTIVE_NONE;
					break;
			}
		}

		$db->setData( $rec, 'activate', $activate );
		$db->updateRecord( $rec );

		if( !$ad_check || $loginUserType == "admin") {
			MailLogic::userRegistComp( $rec, $_GET['type'] );
		}else{
			MailLogic::userAdminCheck( $rec, $_GET['type'] );
		}
	}

	function drawRegistComp( &$gm, $rec, $loginUserType, $loginUserRank ){
		$ad_check = Conf::checkData( 'user', 'ad_check', $_GET['type'] );

		if( !$ad_check ) {
			$label = "ACTIVATE_COMP_DESIGN_HTML";
		}else{
			$label = "ACTIVATE_AD_CHECK_DESIGN_HTML";
		}

		if($loginUserType=="admin"){
			$label = "REGIST_COMP_PAGE_DESIGN";
		}

		Template::drawTemplate( $gm[ $_GET['type'] ] , $rec , $loginUserType , $loginUserRank , $_GET['type'] , $label );
	}

	/**
	 * 編集完了処理。
	 * フォーム入力以外の方法でデータを登録する場合は、ここでレコードに値を代入します。
	 *
	 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
	 * @param rec フォームのからの入力データを反映したレコードデータ。
	 */
	function editComp( &$gm, &$rec, &$old_rec, $loginUserType, $loginUserRank )
	{
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $THIS_TABLE_IS_USERDATA;
		global $ACTIVE_NONE;
		global $ACTIVE_ACTIVATE;
		global $ACTIVE_ACCEPT;
		// **************************************************************************************

		$db	 = $gm[ $_GET["type"] ]->getDB();

		if(  $THIS_TABLE_IS_USERDATA[ $_GET['type'] ]  ){
			MailLogic::editNotice( $rec, $_GET['type'] ); // ユーザ情報であれば登録情報編集通知を管理者に送信。
			if($db->getData($rec,"activate") == $ACTIVE_ACCEPT && in_array($db->getData($old_rec,"activate"),array($ACTIVE_NONE,$ACTIVE_ACTIVATE) )){
				MailLogic::userRegistComp( $rec, $_GET['type'] );
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
		global $THIS_TABLE_IS_USERDATA;
		global $ACTIVE_ACTIVATE;
		global $ACTIVE_ACCEPT;
		// **************************************************************************************

		$type = SearchTableStack::getType();
		$db		 = $gm[ $type ]->getDB();
		if(  $THIS_TABLE_IS_USERDATA[ $_GET['type'] ]  )
		{
			if( $loginUserType != 'admin' )	 {  $table	 = $db->searchTable( $table, 'activate', 'in', array($ACTIVE_ACTIVATE ,$ACTIVE_ACCEPT) ); }
		}
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
		global $ACTIVE_NONE;
		// **************************************************************************************

		$db	 = $gm[ $_GET['type'] ]->getDB();
		if( $loginUserType != 'admin' && $db->getData( $rec, 'activate' ) == $ACTIVE_NONE ){return false;}

		return true;
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
		global $ACTIVE_NONE;
		global $ACTIVE_ACTIVATE;
		global $ACTIVE_ACCEPT;

		// 簡易情報変更（情報ページからの内容変更処理）
		if(  isset( $_POST['post'] ) ){
			if( $loginUserType == 'admin' ){
				$db		 = $gm[ $_GET['type'] ]->getDB();
				if(in_array($db->getData($rec,"activate"),array($ACTIVE_NONE,$ACTIVE_ACTIVATE) ) && $_POST["activate"] == $ACTIVE_ACCEPT){
					MailLogic::userRegistComp( $rec, $_GET['type'] );
				}
				for( $i=0; $i<count($db->colName); $i++ ){
					if(   isset(   $_POST[ $db->colName[$i] ]  )   ){
						$db->setData( $rec, $db->colName[$i], $_POST[ $db->colName[$i] ] );
					}
				}
				$db->updateRecord( $rec );
				SystemUtil::async( 'FeedApi', 'update', array( 'targetType' => $_GET['type'] ) );

				if( isset( $_POST[ 'reactivate' ] ) )
					{ MailLogic::activateCheck( $rec, $_GET['type'] ); }
			}
		}
	}

	/////////////////////////////////////////////////////////////////////////////////////////////////////////
	//   アクティベート関係
	/////////////////////////////////////////////////////////////////////////////////////////////////////////

	//activate判定及びアクティベート完了処理
	function activateAction( &$gm, &$rec, $loginUserType, $loginUserRank )
	{
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $ACTIVE_NONE;
		global $ACTIVE_ACTIVATE;
		global $ACTIVE_ACCEPT;
		// **************************************************************************************

		$db = $gm[ $_GET['type'] ]->getDB();

		if(  $db->getData( $rec, 'activate' ) == $ACTIVE_NONE  )
		{
			$ad_check = Conf::checkData( 'user', 'ad_check', $_GET['type'] );

			$activate = $ACTIVE_ACCEPT;
			$first_accept = 1;

			// 管理者承認設定の状況確認
			if( $ad_check )
			{
				$activate = $ACTIVE_ACTIVATE;
				$first_accept = 0;
			}

			$db->setData( $rec, 'activate', $activate );
			$db->setData( $rec, 'first_accept', $first_accept );
			$db->updateRecord( $rec );

			if( !$ad_check ) { MailLogic::userRegistComp( $rec, $_GET['type'] ); }
			else			 { MailLogic::userAdminCheck( $rec, $_GET['type'] ); }

			//if( $first_accept == 1 ) { UserLogic::setFirstLimit( $db, $rec ); } // 利用期間を設定
		}

		return true;
	}

	function drawActivateComp( &$gm, &$rec, $loginUserType, $loginUserRank )
	{
		$label = 'ACTIVATE_COMP_DESIGN_HTML';
		// 管理者承認設定の状況確認
		if( Conf::checkData( 'user', 'ad_check', $_GET['type'] ) ) { $label = 'ACTIVATE_AD_CHECK_DESIGN_HTML'; }

		Template::drawTemplate( $gm[ $_GET['type'] ] , $rec , $loginUserType , $loginUserRank , $_GET['type'] , $label );
	}


	function drawActivateFaled( &$gm, &$rec, $loginUserType, $loginUserRank ){
		Template::drawTemplate( $gm[ $_GET['type'] ] , $rec , $loginUserType , $loginUserRank , $_GET['type'] , 'ACTIVATE_FALED_DESIGN_HTML' );
	}

	function drawSearchNotFound( &$gm, $loginUserType, $loginUserRank )
	{
		if( isset( $_GET[ 'mailSend' ] ) ){
			Template::drawTemplate( $gm[ $_GET['type'] ] , null , $loginUserType , $loginUserRank , $_GET['type'] , 'SEND_USER_NOT_FOUND' );
		}else{
			Template::drawTemplate( $gm[ $_GET['type'] ] , null , $loginUserType , $loginUserRank , $_GET['type'] , 'SEARCH_NOT_FOUND_DESIGN' );
		}
	}

}

?>
