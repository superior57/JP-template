<?php

	//★クラス //

	/**
		@brief インストールウィザード関連の処理クラス。
	*/
	class Install //
	{
		//■処理 //

		/**
			@brief  FTP関数を使って書き込み権限を設定する。
			@retval true  設定に成功した場合。
			@retval false 設定に失敗した場合。
		*/
		static function SetPermissionByFTP() //
		{
			global $SYSTEM_FTP_HOST;
			global $SYSTEM_FTP_USER;
			global $SYSTEM_FTP_PASS;
			global $SYSTEM_FTP_HOME;
			global $SYSTEM_FTP_WRITEABLE_ENTRIES;
			global $SYSTEM_FTP_WRITEABLE_DIRECTORY_PERMIT;
			global $SYSTEM_FTP_WRITEABLE_FILE_PERMIT;

			$ftp     = FTPLogic::Login( $SYSTEM_FTP_HOST , $SYSTEM_FTP_USER , $SYSTEM_FTP_PASS );

			FTPLogic::SyncWorkDirectory( $ftp );

			$currentPath = getcwd() . '/';

			if( $SYSTEM_FTP_HOME == $currentPath ) //PHPとFTPのルートが同じ場合
				{ $relativePath = '/'; }
			else  //PHPとFTPのルートが異なる場合
				{ $relativePath = '/' . substr( $currentPath , strlen( $SYSTEM_FTP_HOME ) ); }

			if( !$ftp->ftp_chdir( $relativePath ) ) //FTPの作業ディレクトリをシステム設置先に移動できない場合
				{ return false; }

			foreach( $SYSTEM_FTP_WRITEABLE_ENTRIES as $entryPath ) //全てのエントリを処理
			{
				$entries = GlobEntries( Array( $entryPath ) );

				foreach( $entries as $entry ) //全てのエントリを処理
				{
					if( is_dir( $entry ) ) //ディレクトリの場合
					{
						foreach( $SYSTEM_FTP_WRITEABLE_DIRECTORY_PERMIT as $permit ) //全ての権限候補を処理
						{
							if( $ftp->ftp_site( 'chmod ' . sprintf( '%o' , $permit ) . ' ' . $entry ) ) //権限設定に成功した場合
								{ break; }
						}
					}
					else //ファイルの場合
					{
						foreach( $SYSTEM_FTP_WRITEABLE_FILE_PERMIT as $permit ) //全ての権限候補を処理
						{
							if( $ftp->ftp_site( 'chmod ' . sprintf( '%o' , $permit ) . ' ' . $entry ) ) //権限設定に成功した場合
								{ break; }
						}
					}
				}
			}

			return ( 0 == count( self::GetNeedPermissionEntries() ) );
		}

		/**
			@brief  chmod関数を使って書き込み権限を設定する。
			@retval true  設定に成功した場合。
			@retval false 設定に失敗した場合。
		*/
		static function SetPermissionByFunction() //
		{
			global $SYSTEM_FTP_WRITEABLE_ENTRIES;
			global $SYSTEM_FTP_WRITEABLE_DIRECTORY_PERMIT;
			global $SYSTEM_FTP_WRITEABLE_FILE_PERMIT;

			foreach( $SYSTEM_FTP_WRITEABLE_ENTRIES as $entryPath ) //全てのエントリを処理
			{
				$entries = GlobEntries( Array( $entryPath ) );

				foreach( $entries as $entry ) //全てのエントリを処理
				{
					if( is_dir( $entry ) ) //ディレクトリの場合
					{
						foreach( $SYSTEM_FTP_WRITEABLE_DIRECTORY_PERMIT as $permit ) //全ての権限候補を処理
						{
							if( chmod( $entry , $permit ) ) //権限設定に成功した場合
								{ break; }
						}
					}
					else //ファイルの場合
					{
						foreach( $SYSTEM_FTP_WRITEABLE_FILE_PERMIT as $permit ) //全ての権限候補を処理
						{
							if( chmod( $entry , $permit ) ) //権限設定に成功した場合
								{ break; }
						}
					}
				}
			}

			return ( 0 == count( self::GetNeedPermissionEntries() ) );
		}

		/**
			@brief     インストール設定ファイルを更新する。
			@param[in] $iUpdateMode 更新する項目。
			@retval    true  更新に成功した場合。
			@retval    false 更新に失敗した場合。
		*/
		static function UpdateInstallConfig( $iUpdateMode , $iStatus = true ) //
		{
			global $SYSTEM_FTP_HOST;
			global $SYSTEM_FTP_USER;
			global $SYSTEM_FTP_PASS;
			global $SYSTEM_FTP_HOME;
			global $SQL_SERVER;
			global $SQL_PORT;
			global $SQL_MASTER;
			global $DB_NAME;
			global $SQL_ID;
			global $SQL_PASS;
			global $SYSTEM_INSTALL_TIME;
			global $SYSTEM_INSTALL_PERMISSION;
			global $SYSTEM_INSTALL_FTP;
			global $SYSTEM_INSTALL_DB;
			global $SYSTEM_INSTALL_TABLE;
			global $SYSTEM_INSTALL_COMPLETE;

			$SYSTEM_INSTALL_TIME = time();

			switch( $iUpdateMode ) //更新項目で分岐
			{
				case 'permission' : //書き込み権限
				{
					$SYSTEM_INSTALL_PERMISSION = $iStatus;
					break;
				}

				case 'ftp' : //FTP接続情報
				{
					$SYSTEM_INSTALL_FTP = $iStatus;
					break;
				}

				case 'db' : //DB接続情報
				{
					$SYSTEM_INSTALL_DB = $iStatus;
					break;
				}

				case 'table' : //テーブル初期化
				{
					$SYSTEM_INSTALL_TABLE = $iStatus;
					break;
				}

				case 'complete' : //インストール処理
				{
					$SYSTEM_INSTALL_COMPLETE = $iStatus;
					break;
				}
			}

			$fp = fopen( 'custom/extends/installConf.php' , 'wb' );

			if( !$fp ) //ファイルを開けない場合
				{ return false; }

			fputs( $fp , '<?php' . "\n" );

			if( $SYSTEM_INSTALL_FTP ) //FTP接続情報が設定済みの場合
			{
				fputs( $fp , "\t" . '$SYSTEM_FTP_HOST = \'' . $SYSTEM_FTP_HOST . '\';' . "\n" );
				fputs( $fp , "\t" . '$SYSTEM_FTP_USER = \'' . $SYSTEM_FTP_USER . '\';' . "\n" );
				fputs( $fp , "\t" . '$SYSTEM_FTP_PASS = \'' . $SYSTEM_FTP_PASS . '\';' . "\n" );
				fputs( $fp , "\t" . '$SYSTEM_FTP_HOME = \'' . $SYSTEM_FTP_HOME . '\';' . "\n" );
				fputs( $fp , "\n" );
			}

			if( $SYSTEM_INSTALL_DB ) //DB接続情報が設定済みの場合
			{
				fputs( $fp , "\t" . '$SQL_SERVER = \'' . $SQL_SERVER . '\';' . "\n" );
				fputs( $fp , "\t" . '$SQL_PORT   = \'' . $SQL_PORT . '\';' . "\n" );
				fputs( $fp , "\t" . '$SQL_MASTER = \'' . $SQL_MASTER . '\';' . "\n" );
				fputs( $fp , "\t" . '$DB_NAME    = \'' . $DB_NAME . '\';' . "\n" );
				fputs( $fp , "\t" . '$SQL_ID     = \'' . $SQL_ID . '\';' . "\n" );
				fputs( $fp , "\t" . '$SQL_PASS   = \'' . $SQL_PASS . '\';' . "\n" );
				fputs( $fp , "\n" );
			}

			fputs( $fp , "\t" . '$SYSTEM_INSTALL_TIME       = ' . $SYSTEM_INSTALL_TIME . ';' . "\n" );
			fputs( $fp , "\t" . '$SYSTEM_INSTALL_PERMISSION = ' . ( $SYSTEM_INSTALL_PERMISSION ? 'true' : 'false' ) . ';' . "\n" );
			fputs( $fp , "\t" . '$SYSTEM_INSTALL_FTP        = ' . ( $SYSTEM_INSTALL_FTP ? 'true' : 'false' ) . ';' . "\n" );
			fputs( $fp , "\t" . '$SYSTEM_INSTALL_DB         = ' . ( $SYSTEM_INSTALL_DB ? 'true' : 'false' ) . ';' . "\n" );
			fputs( $fp , "\t" . '$SYSTEM_INSTALL_TABLE      = ' . ( $SYSTEM_INSTALL_TABLE ? 'true' : 'false' ) . ';' . "\n" );
			fputs( $fp , "\t" . '$SYSTEM_INSTALL_COMPLETE   = ' . ( $SYSTEM_INSTALL_COMPLETE ? 'true' : 'false' ) . ';' . "\n" );

			return true;
		}

		//■データ取得 //

		/**
			@brief  初期化が必要なテーブル一覧を取得する。
			@return テーブル名配列。
		*/
		static function GetNeedInitializeTables() //
		{
			global $TABLE_NAME;

			$exists  = Query::ShowTables();
			$results = Array();

			foreach( $TABLE_NAME as $name ) //全てのテーブルを処理
			{
				$main = new TableName( $name );

				if( !in_array( $main->real() , $exists ) ) //テーブルが未作成の場合
					{ $results[] = $name; }
			}

			return $results;
		}

		/**
			@brief  書き込み権限の設定が必要なエントリ一覧を取得する。
			@return エントリ配列。
		*/
		static function GetNeedPermissionEntries() //
		{
			global $SYSTEM_FTP_WRITEABLE_ENTRIES;
			global $SYSTEM_FTP_WRITEABLE_DIRECTORY_PERMIT;
			global $SYSTEM_FTP_WRITEABLE_FILE_PERMIT;

			$entries = GlobEntries( $SYSTEM_FTP_WRITEABLE_ENTRIES );
			$results = Array();

			foreach( $entries as $entry ) //全てのエントリを処理
			{
				if( is_dir( $entry ) ) //ディレクトリの場合
				{
					if( in_array( octdec( substr( sprintf( '%o' , fileperms( $entry ) ) , -3 ) ) , $SYSTEM_FTP_WRITEABLE_DIRECTORY_PERMIT ) ) //権限が設定済みの場合
						{ continue; }
				}
				else //ファイルの場合
				{
					if( in_array( octdec( substr( sprintf( '%o' , fileperms( $entry ) ) , -3 ) ) , $SYSTEM_FTP_WRITEABLE_FILE_PERMIT ) ) //権限が設定済みの場合
						{ continue; }
				}

				$results[] = $entry;
			}

			return $results;
		}

		/**
			@brief  DB接続情報が有効か確認する。
			@retval true  有効な場合。
			@retval false 有効でない場合。
		*/
		static function HasValidDBConfig() //
		{
			try
			{
				$db = CreateDBConnect();

				unset( $db );

				return true;
			}
			catch( Exception $e ) //例外処理
				{ return false; }
		}

		/**
			@brief  FTP接続情報が有効か確認する。
			@retval true  有効な場合。
			@retval false 有効でない場合。
		*/
		static function HasValidFTPConfig() //
		{
			try
			{
				global $SYSTEM_FTP_HOST;
				global $SYSTEM_FTP_USER;
				global $SYSTEM_FTP_PASS;
				global $SYSTEM_FTP_HOME;

				$ftp = FTPLogic::Login( $SYSTEM_FTP_HOST , $SYSTEM_FTP_USER , $SYSTEM_FTP_PASS );

				FTPLogic::SyncWorkDirectory( $ftp );

				return true;
			}
			catch( Exception $e ) //例外処理
				{ return false; }
		}

		/**
			@brief インストールが完了しているか確認する。
			@retval true  インストールが完了している場合。
			@retval false インストールが完了していない場合。
		*/
		static function IsComplete() //
		{
			global $SYSTEM_INSTALL_PERMISSION;
			global $SYSTEM_INSTALL_DB;
			global $SYSTEM_INSTALL_TABLE;
			global $SYSTEM_INSTALL_COMPLETE;

			if( $_SESSION[ 'toolSkipInstall' ] ) //インストールをスキップした場合
				{ return true; }

			if( $SYSTEM_INSTALL_COMPLETE ) //インストールが完了している場合
				{ return true; }

			if( $SYSTEM_INSTALL_PERMISSION ) //権限設定が必要なファイルがない場合
			{
				if( $SYSTEM_INSTALL_DB ) //DB接続情報が設定済みの場合
				{
					if( $SYSTEM_INSTALL_TABLE ) //初期化が必要なテーブルがない場合
						{ return true; }
				}
			}

			return false;
		}
	}
