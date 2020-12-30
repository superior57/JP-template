<?php

	//★クラス //

	/**
		@brief 既定のパスワードリマインダのモデル。
	*/
	class AppReminderModel extends AppBaseModel //
	{
		//■処理 //

		/**
			@brief パスワードを再発行する。
		*/
		function reissuePassword() //
		{
			global $gm;
			global $TABLE_NAME;
			global $THIS_TABLE_IS_USERDATA;
			global $ACTIVE_NONE;
			global $MAILSEND_ADDRES;
			global $MAILSEND_NAMES;

			if( !$_POST[ 'mail' ] ) //メールアドレスが入力されていない場合
				{ return; }

			foreach( $TABLE_NAME as $type ) //全てのテーブルを処理
			{
				if( !$THIS_TABLE_IS_USERDATA[ $type ] ) //ユーザーテーブルではない場合
					{ continue; }

				$db    =$gm[ $type ]->getDB( $type );
				$table = $db->getTable();
				$table = $db->searchTable( $table , 'mail' , '=' , $_POST[ 'mail' ] );
				$table = $db->searchTable( $table , 'activate' , '!' , $ACTIVE_NONE );
				$table = $db->limitOffset( $table , 0 , 1 );

				if( $db->getRow( $table ) ) //レコードが見つかった場合
				{
					$rec = $db->getRecord( $table , 0 );

					Mail::send( Template::getTemplate( 'reminder' , $this->loginUserRank , '' , 'SEND_MAIL' ) , $MAILSEND_ADDRES , $_POST[ 'mail' ] , $gm[ $type ], $rec , $MAILSEND_NAMES );

					$this->succeededPasswordReissue = true;

					break;
				}
			}
		}

		/**
			@brief トークンの有効性を検証する。
		*/
		function verifyToken() //
			{ ConceptSystem::CheckAuthenticityToken()->OrThrow( 'IllegalTokenAccess' ); }

		//■データ取得 //

		/**
			@brief パスワードの再発行に成功したか確認する。
		*/
		function succeededPasswordReissue() //
			{ return $this->succeededPasswordReissue; }
	}
