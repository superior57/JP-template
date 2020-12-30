<?php

	/**
	 * システムコールクラス
	 * 
	 * @author ----
	 * @version 1.0.0
	 * 
	 */
	class accountLockSystem extends System
	{
		/**
		 * 検索処理。
		 * フォーム入力以外の方法で検索条件を設定したい場合に利用します。
		 *
		 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
		 * @param table フォームのからの入力内容に一致するレコードを格納したテーブルデータ。
		 */
		function searchProc( &$gm, &$table, $loginUserType, $loginUserRank )
		{
			if( 'admin' == $loginUserType )
			{
				$db = GMList::getDB( 'accountLock' );

				if( $_GET[ 'unlock_id' ] )
				{
					$delTable = $db->getTable();
					$delTable = $db->searchTable( $delTable , 'id' , '=' , $_GET[ 'unlock_id' ] );

					$db->setTableDataUpdate( $delTable , 'unlock_time' , 0 );
				}

				$table = $db->searchTable( $table , 'unlock_time' , '>' , time() );
			}
		}
	}

?>