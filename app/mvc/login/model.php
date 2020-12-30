<?php

	//★クラス //

	/**
		@brief 既定のログインフォームのモデル。
	*/
	class AppLoginModel extends AppBaseModel //
	{
		//■処理 //

		/**
			@brief ログインする。
		*/
		function doLogin() //
		{
			global $LOGIN_KEY_FORM_NAME;
			global $LOGIN_PASSWD_FORM_NAME;
			global $TABLE_NAME;
			global $THIS_TABLE_IS_USERDATA;

			$loginType = '';
			$loginID   = '';
			$success   = false;

			if( isset( $_POST[ $LOGIN_KEY_FORM_NAME ] ) && isset( $_POST[ $LOGIN_PASSWD_FORM_NAME ] ) ) //ログイン情報が送信されている場合
			{
				$targets = $TABLE_NAME;

				if( isset( $_GET[ 'type' ] ) || isset( $_POST[ 'type' ] ) ) //ユーザー種別の指定がある場合
				{
					if( isset( $_POST[ 'type' ] ) ) //POSTクエリがある場合
						{ $_GET[ 'type' ] = $_POST[ 'type' ]; }

					$targets = Array( $_GET[ 'type' ] );
				}

				foreach( $targets as $target ) //全てのテーブルを処理
				{
					if( !$THIS_TABLE_IS_USERDATA[ $target ] ) //ユーザーテーブルではない場合
						{ continue; }

					$loginID = SystemUtil::login_check( $target , $_POST[ $LOGIN_KEY_FORM_NAME ] , $_POST[ $LOGIN_PASSWD_FORM_NAME ] );

					if( $loginID ) //認証に成功した場合
					{
						$success   = true;
						$loginType = $target;
						break;
					}
				}
			}

			$sys     = SystemUtil::getSystem( $loginType );
			$success = $sys->loginProc( $success , $loginType , $loginID );

			if( $success ) //ログインに成功した場合
			{
				$this->loginType      = $loginType;
				$this->succeededLogin = true;
			}
			else //ログインに失敗した場合
				{ accountLockLogic::addTryCount(); }

			SystemUtil::login( $loginID , $loginType );
		}

		/**
			@brief ログイン成功時の遷移先を初期化する。
		*/
		function initializeLoginResultURL() //
		{
			if( $_POST[ 'redirect_path' ] ) //リダイレクト先の指定がある場合
				{ $this->loginResultURL = $_POST[ 'redirect_path' ]; }
			else if( $_SESSION[ 'previous_page' ] ) //直前のページがある場合
			{
				if( 'admin' == $this->loginType ) //管理者の場合
					{ $this->loginResultURL = $_SESSION[ 'previous_page_admin' ]; }
				else //その他のユーザーの場合
					{ $this->loginResultURL = $_SESSION[ 'previous_page' ]; }

				unset( $_SESSION[ 'previous_page' ] );
				unset( $_SESSION[ 'previous_page_admin' ] );
			}
			else //その他の場合
				{ $this->loginResultURL = 'index.php'; }
		}

		/**
			@brief ログアウトする。
		*/
		function doLogout() //
		{
			$sys = SystemUtil::getSystem( $this->loginUserType );

			if( $sys->logoutProc( $this->loginUserType ) ) //ログアウトに成功した場合
				{ SystemUtil::logout( $this->loginUserType ); }
		}

		//■データ取得 //
		/**
			@brief  ログイン処理が成功したか確認する。
			@retval true  成功した場合。
			@retval false 失敗した場合。
		*/
		function succeededLogin() //
			{ return $this->succeededLogin; }

		//■変数
		var     $loginResultURL = null;  ///<ログイン成功時の遷移先。
		private $succeededLogin = false; ///<ログインに成功した場合はfalse。
	}
