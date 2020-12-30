<?php

	//★クラス //

	/**
		@brief 既定のログインロック解除フォームのモデル。
	*/
	class AppUnlockModel extends AppBaseModel //
	{
		//■処理 //

		/**
			@brief ロックを解除する。
		*/
		function doUnlock() //
		{
			$aDB  = GMList::getDB( 'admin' );
			$aRec = $aDB->selectRecord( 'ADMIN' );
			$aID  = $aDB->getData( $aRec , 'mail' );

			$db    = GMList::getDB( 'accountLock' );
			$table = $db->getTable();
			$table = $db->searchTable( $table , 'login_id' , '=' , $aID );
			$table = $db->searchTable( $table , 'unlock_token' , '=' , $_POST[ 'token' ] );

			if( !$db->existsRow( $table ) ) //管理者のロック履歴がない場合
				{ return; }

			$rec      = $db->getRecord( $table , 0 );
			$password = $db->getData( $rec , 'onetime_password' );

			if( array_key_exists( 'password' , $_POST ) ) //パスワードが送信されている場合
			{
				if( $password == $_POST[ 'password' ] ) //パスワードが一致する場合
				{
					$db->setData( $rec , 'try_time' , '' );
					$db->updateRecord( $rec );

					$this->succeededUnlock = true;
				}
			}
		}

		/**
			@brief トークンの有効性を検証する。
		*/
		function verifyUnlockToken() //
		{
			if( array_key_exists( 'token' , $_GET ) ) //トークンがGETで送信されている場合
				{ $_POST[ 'token' ] = $_GET[ 'token' ]; }

			if( !array_key_exists( 'token' , $_POST ) ) //トークンが送信されていない場合
				{ throw new InvalidArgumentException( '引数 token は無効です' ); }
		}

		//■データ取得 //
		/**
			@brief ロック解除が成功したか確認する。
		*/
		function succeededUnlock() //
			{ return $this->succeededUnlock; }

		/**
			@brief コンストラクタ。
		*/
		function __construct() //
		{
			parent::__construct();

			$this->type = $_GET[ 'type' ];
			$this->sys  = SystemUtil::getSystem( $this->type );
			$this->db   = GMList::getDB( $this->type );
		}

		var     $type           = null;  ///<ターゲットタイプ。
		var     $sys            = null;  ///<Systemインスタンス。
		var     $db             = null;  ///<DBインスタンス。
		private $succeedeUnlock = false; ///<ロック解除に成功した場合はtrue。
	}
