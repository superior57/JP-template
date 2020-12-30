<?php

	//★クラス //

	/**
		@brief 既定のログインフォームのコントローラ。
	*/
	class AppLoginController extends AppBaseController //
	{
		//■処理 //

		/**
			@brief ログインフォーム表示要求への応答。
		*/
		function loginForm() //
			{ $this->view->drawLoginFormPage( $this->model ); }

		/**
			@brief ログイン実行要求への応答。
		*/
		function doLogin() //
		{
			$this->model->doLogin();

			if( $this->model->succeededLogin() ) //ログインに成功した場合
			{
				$this->model->initializeLoginResultURL();
				$this->view->redirectLoginResultURL( $this->model );
			}
			else //ログインに失敗した場合
				{ $this->view->drawFailedLoginPage( $this->model ); }
		}

		/**
			@brief ログアウト実行要求への応答。
		*/
		function doLogout() //
		{
			$this->model->doLogout();

			$this->view->redirectIndexURL( $this->model );
		}

		/**
			@brief ログイン操作停止要求への応答。
		*/
		function denyLogin() //
		{ $this->view->drawDenyLoginPage( $this->model ); }

		//■コンストラクタ・デストラクタ //

		/**
			@brief コンストラクタ。
		*/
		function __construct() //
		{
			global $LOGIN_KEY_FORM_NAME;
			global $LOGIN_PASSWD_FORM_NAME;

			if( 'true' == $_GET[ 'logout' ] ) //ログアウト操作の実行が要求されている場合
				{ $this->action = 'doLogout'; }
			else if( AccountLockLogic::IsTryOver() ) //アカウントがロックされている場合
				{ $this->action = 'denyLogin'; }
			else if( isset( $_POST[ $LOGIN_KEY_FORM_NAME ] ) && isset( $_POST[ $LOGIN_PASSWD_FORM_NAME ] ) ) //ログイン情報が送信されている場合
				{ $this->action = 'doLogin'; }
			else if( 'true' == $_GET[ 'run' ] ) //ログイン操作の実行が要求されている場合
				{ $this->action = 'doLogin'; }
			else //要求がない場合
				{ $this->action = 'loginForm'; }

			parent::__construct();
		}
	}
