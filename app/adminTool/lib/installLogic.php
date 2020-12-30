<?php

	//★クラス //

	/**
		@brief インストール処理クラス。
	*/
	class InstallLogic //
	{
		//■処理 //

		/**
			@brief     FTP関数を使って書き込み権限を設定する。
			@param[in] $iHost     サーバー。
			@param[in] $iUser     ユーザー名。
			@param[in] $iPass     パスワード。
			@param[in] $iRootPath ルートディレクトリの絶対パス。
			@retval    true  設定に成功した場合。
			@retval    false 設定に失敗した場合。
		*/
		static function SetPermissionByFTP( $iHost , $iUser , $iPass , $iRootPath ) //
		{
			global $SYSTEM_FTP_WRITEABLE_ENTRIES;
			global $SYSTEM_FTP_EXECUTABLE_ENTRIES;
			global $SYSTEM_FTP_WRITEABLE_DIRECTORY_PERMIT;
			global $SYSTEM_FTP_WRITEABLE_FILE_PERMIT;
			global $SYSTEM_FTP_EXECUTABLE_FILE_PERMIT;

			try
			{
				$ftp = FTPLogic::Login( $iHost , $iUser , $iPass );

				FTPLogic::SyncWorkDirectory( $ftp );
			}
			catch( Exception $e )
				{ return false; }

			$currentPath = getcwd() . '/';

			if( $iRootPath == $currentPath ) //PHPとFTPのルートが同じ場合
				{ $relativePath = '/'; }
			else  //PHPとFTPのルートが異なる場合
				{ $relativePath = '/' . substr( $currentPath , strlen( $iRootPath ) ); }

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

			foreach( $SYSTEM_FTP_EXECUTABLE_ENTRIES as $entry ) //全てのエントリを処理
			{
				foreach( $SYSTEM_FTP_EXECUTABLE_FILE_PERMIT as $permit ) //全ての権限候補を処理
				{
					if( $ftp->ftp_site( 'chmod ' . sprintf( '%o' , $permit ) . ' ' . $entry ) ) //権限設定に成功した場合
						{ break; }
				}
			}

			return ( 0 == count( self::GetNeedPermissionEntries() ) );
		}

		/**
			@brief  chmod関数を使って書き込み権限を設定する。
			@retval true  設定に成功した場合。
			@retval false 設定に失敗した場合。
		*/
		function SetPermissionByFunction() //
		{
			global $SYSTEM_FTP_WRITEABLE_ENTRIES;
			global $SYSTEM_FTP_EXECUTABLE_ENTRIES;
			global $SYSTEM_FTP_WRITEABLE_DIRECTORY_PERMIT;
			global $SYSTEM_FTP_WRITEABLE_FILE_PERMIT;
			global $SYSTEM_FTP_EXECUTABLE_FILE_PERMIT;

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

			foreach( $SYSTEM_FTP_EXECUTABLE_ENTRIES as $entry ) //全てのエントリを処理
			{
				foreach( $SYSTEM_FTP_EXECUTABLE_FILE_PERMIT as $permit ) //全ての権限候補を処理
				{
					if( chmod( $entry , $permit ) ) //権限設定に成功した場合
						{ break; }
				}
			}

			return ( 0 == count( self::GetNeedPermissionEntries() ) );
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
			global $SYSTEM_FTP_EXECUTABLE_ENTRIES;
			global $SYSTEM_FTP_WRITEABLE_DIRECTORY_PERMIT;
			global $SYSTEM_FTP_WRITEABLE_FILE_PERMIT;
			global $SYSTEM_FTP_EXECUTABLE_FILE_PERMIT;

			$entries = GlobEntries( $SYSTEM_FTP_WRITEABLE_ENTRIES );
			$results = Array();


            if( SystemUtil::isWindows() ) // Windows 上では fileperms が利用できない。
            {
                return $results;
            }

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

			foreach( $SYSTEM_FTP_EXECUTABLE_ENTRIES as $entry ) //全てのエントリを処理
			{
				if( in_array( octdec( substr( sprintf( '%o' , fileperms( $entry ) ) , -3 ) ) , $SYSTEM_FTP_EXECUTABLE_FILE_PERMIT ) ) //権限が設定済みの場合
					{ continue; }

				$results[] = $entry;
			}

			return $results;
		}

		static function IsWriteableDirectoryEntry( $iEntry ) //
		{
			global $SYSTEM_FTP_WRITEABLE_ENTRIES;

			foreach( $SYSTEM_FTP_WRITEABLE_ENTRIES as $pattern ) //全ての定義を処理
			{
				if( preg_match( '/' . str_replace( '/' , '\\/' , $pattern ) . '/' , $iEntry ) && is_dir( $iEntry ) ) //定義に一致するディレクトリの場合
					{ return true; }
			}

			return false;
		}

		static function IsWriteableFileEntry( $iEntry ) //
		{
			global $SYSTEM_FTP_WRITEABLE_ENTRIES;

			foreach( $SYSTEM_FTP_WRITEABLE_ENTRIES as $pattern ) //全ての定義を処理
			{
				if( preg_match( '/' . str_replace( '/' , '\\/' , $pattern ) . '/' , $iEntry ) && !is_dir( $iEntry ) && !self::IsExecutableEntry( $iEntry ) ) //定義に一致するファイルで実行対象ではない場合
					{ return true; }
			}

			return false;
		}

		static function IsExecutableEntry( $iEntry ) //
		{
			global $SYSTEM_FTP_EXECUTABLE_ENTRIES;

			foreach( $SYSTEM_FTP_EXECUTABLE_ENTRIES as $pattern ) //全ての定義を処理
			{
				if( preg_match( '/' . str_replace( '/' , '\\/' , $pattern ) . '/' , $iEntry ) && !is_dir( $iEntry ) ) //定義に一致するファイルの場合
					{ return true; }
			}

			return false;
		}
	}
