<?php

	//★クラス //

	/**
		@brief 既定の管理ツールのデータベース設定処理のモデル。
	*/
	class AppInstallFTPModel extends AppBaseModel //
	{
		//■処理 //

		/**
			@brief 設定適用処理を実行する。
		*/
		function doInstall() //
		{
			global $SYSTEM_FTP_HOST;
			global $SYSTEM_FTP_USER;
			global $SYSTEM_FTP_PASS;
			global $SYSTEM_FTP_HOME;

			InstallLogic::SetPermissionByFTP( $SYSTEM_FTP_HOST , $SYSTEM_FTP_USER , $SYSTEM_FTP_PASS , $SYSTEM_FTP_HOME );
			InstallConfig::SetFTPConfig( $SYSTEM_FTP_HOST , $SYSTEM_FTP_USER , $SYSTEM_FTP_PASS , $SYSTEM_FTP_HOME );
			InstallStatus::Set( 'permission' , true );
			InstallStatus::Set( 'ftp' , true );
		}

		/**
			@brief クエリを更新する。
		*/
		function updateQuery() //
		{
			global $SYSTEM_FTP_HOST;
			global $SYSTEM_FTP_USER;
			global $SYSTEM_FTP_PASS;
			global $SYSTEM_FTP_HOME;

			if( isset( $_POST[ 'ftpHost' ] ) ) //クエリがある場合
				{ $SYSTEM_FTP_HOST = $_POST[ 'ftpHost' ]; }

			if( isset( $_POST[ 'ftpUser' ] ) ) //クエリがある場合
				{ $SYSTEM_FTP_USER = $_POST[ 'ftpUser' ]; }

			if( isset( $_POST[ 'ftpPass' ] ) ) //クエリがある場合
				{ $SYSTEM_FTP_PASS = $_POST[ 'ftpPass' ]; }

			if( isset( $_POST[ 'ftpHome' ] ) ) //クエリがある場合
				{ $SYSTEM_FTP_HOME = $_POST[ 'ftpHome' ]; }

			if( !$SYSTEM_FTP_HOME ) //ホームディレクトリが不明の場合
			{
				try
				{
					$ftp             = FTPLogic::Login( $SYSTEM_FTP_HOST , $SYSTEM_FTP_USER , $SYSTEM_FTP_PASS );
					$SYSTEM_FTP_HOME = FTPLogic::SurveyHomeDirectory( $ftp );

					$ftp->ftp_quit();
				}
				catch( Exception $e ) //例外処理
				{
					if( $ftp )
						{ $ftp->ftp_quit(); }
				}
			}
		}

		/**
			@brief 入力内容を検証する。
		*/
		function verifyInput() //
		{
			global $SYSTEM_FTP_HOST;
			global $SYSTEM_FTP_USER;
			global $SYSTEM_FTP_PASS;
			global $SYSTEM_FTP_HOME;

			if( !InstallConfig::VerifyFTPConfig( $SYSTEM_FTP_HOST , $SYSTEM_FTP_USER , $SYSTEM_FTP_PASS ) ) //有効な設定ではない場合
				{ $this->errors[ 'failed' ] = true; }
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
