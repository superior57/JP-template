<?php

	//★クラス //

	/**
		@brief 既定の管理ツールのインストールウィザードのモデル。
	*/
	class AppInstallTableModel extends AppBaseModel //
	{
		//■処理 //

		/**
			@brief 設定適用処理を実行する。
		*/
		function updateInstallStatus() //
		{
			InstallConfig::SetIDConfig( $_POST[ 'id_mode' ] );
			InstallStatus::Set( 'table' , true );
		}

		/**
			@brief 入力内容を検証する。
		*/
		function verifyInput() //
		{
			if( !array_key_exists( 'password' , $_POST ) || !$_POST[ 'password' ] ) //パスワードが送信されていない場合
				{ $this->errors[ 'password' ] = true; }
			else //パスワードが送信されている場合
			{
				$password = GetToolPassword();

				if( $password != md5( $_POST[ 'password' ] ) ) //パスワードが一致しない場合
					{ $this->errors[ 'password_confirm' ] = true; }
			}
		}

		function login() //
			{ $_SESSION[ 'loginedAdminTool' ] = true; }

		//■データ取得 //

		/**
			@brief  設定を適用できる状態か確認する。
			@retval true  適用できる状態の場合。
			@retval false 適用できる状態ではない場合。
		*/
		function canInstall() //
			{ return ( 0 >= count( $this->errors ) ? true : false ); }
	}
