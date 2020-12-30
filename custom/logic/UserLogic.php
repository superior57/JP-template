<?php

class UserLogic
{
	/**
	 * 利用不可ステータスの場合indexと問い合わせ以外アクセス出来ない様にする
	 */
	function activateCheckController()
	{
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $loginUserType;
		// **************************************************************************************
		
		switch($loginUserType)
		{
		case 'cUser': cUserLogic::activateCheck(); break;
		case 'nUser': nUserLogic::activateCheck(); break;
		}
	}


	/**
	 * 仮登録のまま3日経過しているユーザを削除。
	 *
	 * @param type cUser,nUser 
	 */
	function deleteActivateNone( $type )
	{
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $ACTIVE_NONE;
		// **************************************************************************************

		$db = GMList::getDB( $type );

		$table = $db->getTable();
		$table = $db->searchTable( $table, 'activate', '=', $ACTIVE_NONE );
		$table = $db->searchTable( $table, 'regist', '<', time()-86400*3 );
		$db->deleteTable($table);

	}


	/**
	 * 選択されたユーザを削除。
	 *
	 * @param type cUser,nUser 
	 * @param idList 対象ユーザーID 
	 */
	function deleteSelectUser( $type, $idList )
	{
		switch($type)
		{
		case 'cUser': cUserLogic::delete( $idList ); break;
		case 'nUser': nUserLogic::delete( $idList ); break;
		}
	}


	/**
	 * 初回ログイン許可の場合その旨をメール送信。
	 *
	 * @param rec ユーザーレコード 
	 * @param type cUser,nUser 
	 */
	function firstAccept( $rec, $type )
	{
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $ACTIVE_ACCEPT;
		// **************************************************************************************

		$db = GMList::getDB( $type );
		if( $db->getData( $rec, 'activate' ) == $ACTIVE_ACCEPT && $db->getData( $rec, 'first_accept' ) == 0 )
		{// 利用可能状態で初回許可がoffの場合初回処理を実行
			$db->setData($rec, 'first_accept', 1 );
			$db->updateRecord( $rec );

			$ad_check = Conf::checkData( 'user', 'ad_check', $type );
			if( !$ad_check ) { MailLogic::userRegistComp( $rec, $type ); }
			else			 { MailLogic::userAdminComp( $rec, $type ); }

			self::setFirstLimit( $db, $rec ); // 利用期間を設定
		}

	}


	/**
	 * 初回ログイン許可時に利用期間申請レコードの作成と利用期間のセットを行う。
	 *
	 * @param rec ユーザーレコード 
	 * @param type cUser,nUser 
	 */
	function setFirstLimit( $db, $rec )
	{
		$ul_term = $db->getData( $rec, 'ul_term' );
		if( strlen($ul_term) )
		{
			$userId = $db->getData( $rec, 'id' );
			$ulRec = UserLimit::addRecord( $userId, $ul_term );
			cUserLogic::addLimit($userId, $db->getData( $ulRec, 'term' ));
			Pay::addRecord($userId, $db->getData( $ulRec, 'cost' ), 'user_limit', $db->getData( $ulRec, 'id' ) );
		}
	}
}

?>