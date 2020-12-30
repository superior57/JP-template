<?php

	class NewsSystem extends System
	{
		/**
		 * 登録内容確認。
		 *
		 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
		 * @param rec 編集対象のレコードデータ
		 * @param loginUserType ログインしているユーザの種別
		 * @param loginUserRank ログインしているユーザの権限
		 * @return エラーがあるかを真偽値で渡す。
		 */
		function registCheck( &$gm, $edit, $loginUserType, $loginUserRank )
		{
			$result	 = parent::registCheck( $gm, $edit, $loginUserType, $loginUserRank );

			//link_toやlink_typeの設定項目によって変わる必須入力項目のチェック
			switch( (int)$_POST['link_to'] )
			{
			case '0': // リンクなし
				break;
			case '1': // 本文へ誘導
				if( !isset($_POST['main']) || strlen($_POST['main' ]) == 0 ){ self::$checkData->addError('main'); }
				break;
			case '2': // 任意のURI
				if( !isset($_POST['url']) || strlen($_POST['url' ]) == 0 ){ self::$checkData->addError('url'); }
				break;
			}

			if( (int)$_POST['link_to'])
			{
				switch( (int)$_POST['link_type'] )
				{
				case '0': // リンクメッセージを用意
					if( !isset($_POST['link_message']) || strlen($_POST['link_message' ]) == 0 ){ self::$checkData->addError('link_message'); }
					break;
				case '1': // トピックにリンク
					break;
				}
			}

			return self::$checkData->getCheck();
		}


		/**
		 * 登録前段階処理。
		 * フォーム入力以外の方法でデータを登録する場合は、ここでレコードに値を代入します。
		 *
		 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
		 * @param rec 編集対象のレコードデータ
		 * @param loginUserType ログインしているユーザの種別
		 * @param loginUserRank ログインしているユーザの権限
		 */
		function registProc( &$gm, &$rec, $loginUserType, $loginUserRank, $check = false )
		{
		    parent::registProc( $gm, $rec, $loginUserType, $loginUserRank, $check );

            $db		 = $gm[ $_GET['type'] ]->getDB();
			$db->setData( $rec, 'regist', mktime( 0, 0, 0, $_POST['month'], $_POST['day'], $_POST['year'] ) );

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
			switch($loginUserType)
			{
			case 'admin':
				$check = true;
				break;			
			default:
				$db	 = $gm[ $_GET['type'] ]->getDB();
				$acceptUserType = explode("/",$db->getData($rec,"authority"));
				$open = $db->getData($rec,"state");
				$check = in_array($loginUserType, $acceptUserType) && (bool)$open;
				break;
			}
			return $check;
		}

		/**
		 * 編集前段階処理。
		 * フォーム入力以外の方法でデータを登録する場合は、ここでレコードに値を代入します。
		 *
		 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
		 * @param rec 編集対象のレコードデータ
		 * @param loginUserType ログインしているユーザの種別
		 * @param loginUserRank ログインしているユーザの権限
		 */
		function editProc( &$gm, &$rec, $loginUserType, $loginUserRank, $check = false )
		{
		    parent::editProc( $gm, $rec, $loginUserType, $loginUserRank, $check );

            $db		 = $gm[ $_GET['type'] ]->getDB();
			$db->setData( $rec, 'regist', mktime( 0, 0, 0, $_POST['month'], $_POST['day'], $_POST['year'] ) );

		}


		/**
		 * 検索処理。
		 * フォーム入力以外の方法で検索条件を設定したい場合に利用します。
		 *
		 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
		 * @param rec 編集対象のレコードデータ
		 * @param loginUserType ログインしているユーザの種別
		 * @param loginUserRank ログインしているユーザの権限
		 */
		function searchProc( &$gm, &$table, $loginUserType, $loginUserRank )
		{
			$db = $gm[$_GET['type']]->getDB();

			$table = News::getTable($table);
		}
	}
?>
