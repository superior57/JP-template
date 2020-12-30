<?php

	//★クラス //

	/**
		@brief 既定の管理ツールのデータベース設定処理のモデル。
	*/
	class AppInstallDBModel extends AppBaseModel //
	{
		//■処理 //

		/**
			@brief 設定適用処理を実行する。
		*/
		function doInstall() //
		{
			global $SQL_MASTER;
			global $SQL_SERVER;
			global $SQL_PORT;
			global $DB_NAME;
			global $SQL_ID;
			global $SQL_PASS;

			InstallConfig::SetDBConfig( $SQL_MASTER , $SQL_SERVER , $SQL_PORT , $DB_NAME , $SQL_ID , $SQL_PASS );
			InstallStatus::Set( 'db' , true );
			InstallStatus::Set( 'verify' , true );
		}

		/**
			@brief クエリを更新する。
		*/
		function updateQuery() //
		{
			global $SQL_MASTER;
			global $SQL_SERVER;
			global $SQL_PORT;
			global $DB_NAME;
			global $SQL_ID;
			global $SQL_PASS;

			if( isset( $_POST[ 'sqlDBMS' ] ) ) //クエリがある場合
				{ $SQL_MASTER = $_POST[ 'sqlDBMS' ]; }

			if( isset( $_POST[ 'sqlServer' ] ) ) //クエリがある場合
				{ $SQL_SERVER = $_POST[ 'sqlServer' ]; }

			if( isset( $_POST[ 'sqlPort' ] ) ) //クエリがある場合
				{ $SQL_PORT = $_POST[ 'sqlPort' ]; }

			if( isset( $_POST[ 'sqlDBName' ] ) ) //クエリがある場合
				{ $DB_NAME = $_POST[ 'sqlDBName' ]; }

			if( isset( $_POST[ 'sqlUser' ] ) ) //クエリがある場合
				{ $SQL_ID = $_POST[ 'sqlUser' ]; }

			if( isset( $_POST[ 'sqlPass' ] ) ) //クエリがある場合
				{ $SQL_PASS = $_POST[ 'sqlPass' ]; }
		}

		/**
			@brief 入力内容を検証する。
		*/
		function verifyInput() //
		{
			global $SQL_MASTER;
			global $SQL_SERVER;
			global $SQL_PORT;
			global $DB_NAME;
			global $SQL_ID;
			global $SQL_PASS;

			if( !InstallConfig::VerifyDBConfig( $SQL_MASTER , $SQL_SERVER , $SQL_PORT , $DB_NAME , $SQL_ID , $SQL_PASS ) ) //有効な設定ではない場合
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
