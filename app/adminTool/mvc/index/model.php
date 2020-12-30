<?php

	//★クラス //

	/**
		@brief 既定の管理ツールのログイン・ログアウトのモデル。
	*/
	class AppIndexModel extends AppBaseModel //
	{
		//■処理 //

		function doCountUpdate() //
		{
			global $TABLE_NAME;

			foreach( $TABLE_NAME as $table ) //全てのテーブルを処理
				{ UpdateSystemTable( $table ); }
		}

		function doExit() //
		{
			global $TABLE_NAME;
			global $THIS_TABLE_IS_USERDATA;
			global $TDB;      ///<テーブル初期値設定ファイルの配列。

			foreach( $TABLE_NAME as $table ) //全てのテーブルを処理
			{
				if( $THIS_TABLE_IS_USERDATA[ $table ] )
				{ unlink( PathUtil::ModifyTDBFilePath( $TDB[ $table ] ) ); }
			}

			InstallStatus::DoExit();
		}

		/**
			@brief ログインする。
		*/
		function doLogin() //
		{
			global $TABLE_NAME;

			if( !array_key_exists( 'password' , $_POST ) || !$_POST[ 'password' ] ) //パスワードが送信されていない場合
				{ $this->errors[ 'password' ] = true; }
			else //パスワードが送信されている場合
			{
				$password = GetToolPassword();

				if( $password != md5( $_POST[ 'password' ] ) ) //パスワードが一致しない場合
					{ $this->errors[ 'password_confirm' ] = true; }
			}

			if( !count( $this->errors ) ) //エラーがない場合
			{
				foreach( $TABLE_NAME as $table ) //全てのテーブルを処理
					{ UpdateSystemTable( $table ); }

				$_SESSION[ 'loginedAdminTool' ] = true;

				return true;
			}

			return false;
		}

		/**
			@brief ログアウトする。
		*/
		function doLogout() //
		{
			$_SESSION[ 'loginedAdminTool' ] = false;

			return false;
		}

		/**
			@brief 描画用の変数などを準備する。
		*/
		function setRenderStatus() //
		{
			global $HOME;

			$tableName = new TableName( 'system' );
			$csv       = new CSV( 'system' );

			if( in_array( $tableName->real() , Query::ShowTables() ) ) //テーブルが存在する場合
			{
				$statement = Query::GetSelectStatement( $tableName->real() , $csv->getColumns() );

				$statement->setFetchMode( PDO::FETCH_ASSOC );

				foreach( $statement as $row ) //全てのレコードを処理
				{
					$HOME = $row[ 'home' ];
					break;
				}
			}

			InstallConfig::ReadMiscConfig();
		}
	}
