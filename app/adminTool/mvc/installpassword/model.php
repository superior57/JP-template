<?php

	//★クラス //

	/**
		@brief 既定の管理ツールのインストールウィザードのモデル。
	*/
	class AppInstallPasswordModel extends AppBaseModel //
	{
		//■処理 //

		/**
			@brief 設定適用処理を実行する。
		*/
		function updateInstallStatus() //
		{
			UpdateToolPassword( $_POST[ 'password' ] );

			InstallStatus::Set( 'password' , true );
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
				if( 8 > strlen( $_POST[ 'password' ] ) )
					{ $this->errors[ 'password_length' ] = true; }

				if( preg_match( '/^\d+$/' , $_POST[ 'password' ] ) )
					{ $this->errors[ 'password_character' ] = true; }

				if( preg_match( '/^[a-zA-Z]+$/' , $_POST[ 'password' ] ) )
					{ $this->errors[ 'password_character' ] = true; }
			}
		}

		//■データ取得 //

		/**
			@brief  設定を適用できる状態か確認する。
			@retval true  適用できる状態の場合。
			@retval false 適用できる状態ではない場合。
		*/
		function canInstall() //
			{ return ( 0 >= count( $this->errors ) ? true : false ); }
	}
