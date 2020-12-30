<?php

	class inquirySystem extends System
	{
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
			case 'aUser':
				$tgm = GMList::getGM($loginUserType);
				$db	 = $tgm->getDB();
				$rec = $db->selectRecord($LOGIN_ID);
				foreach( $tgm->colName as $col ) { $_GET[$col] = $db->getData( $rec, $col ); }
				break;
			}

			return false;
		}

		function registProc(&$gm, &$rec, $loginUserType, $loginUserRank, $check = false){

			$db = GMList::getDB($_GET['type']);
			$db->setData($rec,"supported",false);

			parent::registProc($gm, $rec, $loginUserType, $loginUserRank, $check);
		}

		/**
		 * 登録処理完了処理。
		 * 登録完了時にメールで内容を通知したい場合などに用います。
		 *
		 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
		 * @param rec レコードデータ。
		 */
		function registComp( &$gm, &$rec, $loginUserType, $loginUserRank )
		{
			global $INQUIRY_SPAM_CHECK;
			global $INQUIRY_SPAM_REGEX;

			if( $INQUIRY_SPAM_CHECK ) //スパムチェックが有効である場合
			{
				$db   = GMList::getDB( 'inquiry' );
				$spam = true;

				foreach( Array( 'sub' , 'note' ) as $column ) //件名と本文を処理
				{
					$data = $db->getData( $rec , $column );

					if( !$data ) //入力がない場合
						{ continue; }

					if( !preg_match( $INQUIRY_SPAM_REGEX , $data ) ) //スパム条件と一致しない場合
					{
						$spam = false;

						break;
		}
				}

				if( !$spam ) //スパムではない場合
					{ MailLogic::inquiry($rec); }
			}
			else //スパムチェックをしない場合
				{ MailLogic::inquiry($rec); }
		}


		function infoProc( &$gm, &$rec, $loginUserType, $loginUserRank )
		{
			// 簡易情報変更（情報ページからの内容変更処理）
			if(  isset( $_POST['post'] ) ){
				if( $loginUserType == 'admin' ){
					$db		 = $gm[ $_GET['type'] ]->getDB();

					for( $i=0; $i<count($db->colName); $i++ ){
						if(   isset(   $_POST[ $db->colName[$i] ]  )   ){
							$db->setData( $rec, $db->colName[$i], $_POST[ $db->colName[$i] ] );
						}
					}
					$db->updateRecord( $rec );
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
			global $LOGIN_ID;
			// **************************************************************************************
			$type = SearchTableStack::getType();

			$db		 = $gm[ $type ]->getDB();

			if(strlen($_GET['free']))
			{// フリーワードが指定されている場合
				$free = '%'.$_GET['free'].'%';

				$table1 = $db->searchTable( $table, 'name', '=', $free );
				$table2 = $db->searchTable( $table, 'sub', '=', $free );
				$table3 = $db->searchTable( $table, 'note', '=', $free );

				$table4 = $db->orTable( $table1, $table2 );
				$table = $db->orTable( $table4, $table3 );
			}
		}
	}

?>