<?php

	//★クラス //

	/**
		@brief 各種設定ファイル更新クラス。
	*/
	class InstallConfig //
	{
		//■処理 //

		/**
			@brief   一部の設定の読み込み。
			@details 処理の都合上includeで取り込めない設定変数などを解析して読みます。
		*/
		static function ReadMiscConfig() //
		{
			global $mobile_flag;
			global $sp_flag;

			$contents = file_get_contents( 'custom/head_main.php' , 'rb' );

			if( preg_match( '/\$mobile_flag[\t\s]*=[\t\s]*(\w+)/' , $contents , $matches ) )
				{ $mobile_flag = ( 'true' == strtolower( $matches[ 1 ] ) ? true : false ); }

			if( preg_match( '/\$sp_flag[\t\s]*=[\t\s]*(\w+)/' , $contents , $matches ) )
				{ $sp_flag = ( 'true' == strtolower( $matches[ 1 ] ) ? true : false ); }
		}

		/**
			@brief DB設定ファイルを更新する。
			@exception RuntimeException 設定ファイルが開けない場合。
			@param[in] $iMaster DBMSの種類。
			@param[in] $iHost   サーバー。
			@param[in] $iPort   ポート番号。
			@param[in] $iDBNmae データベース名。
			@param[in] $iUser   ユーザー名。
			@param[in] $iPass   パスワード。
		*/
		static function SetDBConfig( $iMaster , $iHost , $iPort , $iDBName , $iUser , $iPass ) //
		{
			$fp     = fopen( self::$DBConfPath , 'rb' );
			$result = '';

			if( !$fp ) //ファイルが開けない場合
				{ throw new RuntimeException(); }

			while( !feof( $fp ) ) //ファイルの末端まで繰り返し
			{
				$line = fgets( $fp );

				$line = preg_replace( '/(\$SQL_SERVER[\t\s]*=[\t\s]*)(\'[^\']*\'|"[^"]*")/' , '$1\'' . $iHost . '\'' , $line );
				$line = preg_replace( '/(\$SQL_PORT[\t\s]*=[\t\s]*)(\'[^\']*\'|"[^"]*")/' , '$1\'' . $iPort . '\'' , $line );
				$line = preg_replace( '/(\$DB_NAME[\t\s]*=[\t\s]*)(\'[^\']*\'|"[^"]*")/' , '$1\'' . $iDBName . '\'' , $line );
				$line = preg_replace( '/(\$SQL_ID[\t\s]*=[\t\s]*)(\'[^\']*\'|"[^"]*")/' , '$1\'' . $iUser . '\'' , $line );
				$line = preg_replace( '/(\$SQL_PASS[\t\s]*=[\t\s]*)(\'[^\']*\'|"[^"]*")/' , '$1\'' . $iPass . '\'' , $line );

				if( $iPort ) //ポートの指定がある場合
					{ $line = preg_replace( '/\/\/([\t\s]*\$SQL_PORT[\t\s]*=[\t\s]*\'[^\']*\'|"[^"]*")/' , '$1' , $line ); }
				else //ポートの指定がない場合
					{ $line = preg_replace( '/^([\t\s]*\$SQL_PORT[\t\s]*=[\t\s]*\'[^\']*\'|"[^"]*")/' , '//$1' , $line ); }

				if( 'MySQLDatabase' == $iMaster ) //MySQLを使用する場合
				{
					$line = preg_replace( '/\/\/([\t\s]*\$SQL_MASTER[\t\s]*=[\t\s]*\'MySQLDatabase\'|"MySQLDatabase*")/' , '$1' , $line );
					$line = preg_replace( '/^([\t\s]*\$SQL_MASTER[\t\s]*=[\t\s]*\'SQLiteDatabase\'|"SQLiteDatabase*")/' , '//$1' , $line );
				}
				else //SQLiteを使用する場合
				{
					$line = preg_replace( '/^([\t\s]*\$SQL_MASTER[\t\s]*=[\t\s]*\'MySQLDatabase\'|"MySQLDatabase*")/' , '//$1' , $line );
					$line = preg_replace( '/\/\/([\t\s]*\$SQL_MASTER[\t\s]*=[\t\s]*\'SQLiteDatabase\'|"SQLiteDatabase*")/' , '$1' , $line );
				}

				$result .= $line;
			}

			$fp = fopen( self::$DBConfPath , 'wb' );

			if( !$fp ) //ファイルが開けない場合
				{ throw new RuntimeException(); }

			fputs( $fp , $result );

			fclose( $fp );
		}

		/**
			@brief FTP設定ファイルを更新する。
			@exception RuntimeException 設定ファイルが開けない場合。
			@param[in] $iHost     サーバー。
			@param[in] $iUser     ユーザー名。
			@param[in] $iPass     パスワード。
			@param[in] $iRootPath ルートディレクトリの絶対パス。
		*/
		static function SetFTPConfig( $iHost , $iUser , $iPass , $iRootPath ) //
		{
			$fp     = fopen( self::$FTPConfPath , 'rb' );
			$result = '';

			if( !$fp ) //ファイルが開けない場合
				{ throw new RuntimeException(); }

			while( !feof( $fp ) ) //ファイルの末端まで繰り返し
			{
				$line = fgets( $fp );

				$line = preg_replace( '/(\$SYSTEM_FTP_HOST[\t\s]*=[\t\s]*)(\'[^\']*\'|"[^"]*")/' , '$1\'' . $iHost . '\'' , $line );
				$line = preg_replace( '/(\$SYSTEM_FTP_USER[\t\s]*=[\t\s]*)(\'[^\']*\'|"[^"]*")/' , '$1\'' . $iUser . '\'' , $line );
				$line = preg_replace( '/(\$SYSTEM_FTP_PASS[\t\s]*=[\t\s]*)(\'[^\']*\'|"[^"]*")/' , '$1\'' . $iPass . '\'' , $line );
				$line = preg_replace( '/(\$SYSTEM_FTP_HOME[\t\s]*=[\t\s]*)(\'[^\']*\'|"[^"]*")/' , '$1\'' . $iRootPath . '\'' , $line );

				$result .= $line;
			}

			$fp = fopen( self::$FTPConfPath , 'wb' );

			if( !$fp ) //ファイルが開けない場合
				{ throw new RuntimeException(); }

			fputs( $fp , $result );

			fclose( $fp );
		}

		/**
			@brief 携帯設定ファイルを更新する。
			@exception RuntimeException 設定ファイルが開けない場合。
			@param[in] $iEnableMobile     携帯自動切り替え設定。
			@param[in] $iEnableSmartPhone スマートフォン自動切り替え設定。
		*/
		static function SetMobileConfig( $iEnableMobile , $iEnableSmartPhone ) //
		{
			$fp     = fopen( self::$MobileConfPath , 'rb' );
			$result = '';

			if( !$fp ) //ファイルが開けない場合
				{ throw new RuntimeException(); }

			while( !feof( $fp ) ) //ファイルの末端まで繰り返し
			{
				$line = fgets( $fp );

				$line = preg_replace( '/(\$mobile_flag[\t\s]*=[\t\s]*)(true|false)/' , '$1' . ( $iEnableMobile ? 'true' : 'false' )  , $line );
				$line = preg_replace( '/(\$sp_flag[\t\s]*=[\t\s]*)(true|false)/' , '$1' . ( $iEnableSmartPhone ? 'true' : 'false' )  , $line );

				$result .= $line;
			}

			$fp = fopen( self::$MobileConfPath , 'wb' );

			if( !$fp ) //ファイルが開けない場合
				{ throw new RuntimeException(); }

			fputs( $fp , $result );

			fclose( $fp );
		}

		/**
			@brief SSL設定ファイルを更新する。
			@exception RuntimeException 設定ファイルが開けない場合。
			@param[in] $iEnableSwitch SSL自動切り替え設定。
			@param[in] $iEnableMobile 携帯SSL自動切り替え設定。
		*/
		static function SetSSLConfig( $iEnableSwitch , $iEnableMobile ) //
		{
			$fp     = fopen( self::$SSLConfPath , 'rb' );
			$result = '';

			if( !$fp ) //ファイルが開けない場合
				{ throw new RuntimeException(); }

			while( !feof( $fp ) ) //ファイルの末端まで繰り返し
			{
				$line = fgets( $fp );

				$line = preg_replace( '/(\$CONFIG_SSL_ENABLE[\t\s]*=[\t\s]*)(true|false)/' , '$1' . ( $iEnableSwitch ? 'true' : 'false' )  , $line );
				$line = preg_replace( '/(\$CONFIG_SSL_MOBILE[\t\s]*=[\t\s]*)(true|false)/' , '$1' . ( $iEnableMobile ? 'true' : 'false' )  , $line );

				$result .= $line;
			}

			$fp = fopen( self::$SSLConfPath , 'wb' );

			if( !$fp ) //ファイルが開けない場合
				{ throw new RuntimeException(); }

			fputs( $fp , $result );

			fclose( $fp );
		}

		/**
			@brief ID設定ファイルを更新する。
			@exception RuntimeException 設定ファイルが開けない場合。
			@param[in] $iIDMode ID設定。
		*/
		static function SetIDConfig( $iIDMode ) //
		{
			$fp     = fopen( self::$IDConfPath , 'rb' );
			$result = '';

			if( !$fp ) //ファイルが開けない場合
				{ throw new RuntimeException(); }

			while( !feof( $fp ) ) //ファイルの末端まで繰り返し
			{
				$line = fgets( $fp );

				$line = preg_replace( '/(\$MAIN_ID_TYPE[\t\s]*=[\t\s]*)(\'[^\']*\')/' , '$1' . '\'' . $iIDMode . '\'' , $line );

				$result .= $line;
			}

			$fp = fopen( self::$IDConfPath , 'wb' );

			if( !$fp ) //ファイルが開けない場合
				{ throw new RuntimeException(); }

			fputs( $fp , $result );

			fclose( $fp );
		}

		/**
			@brief サイトURLを設定する。
			@exception RuntimeException 設定ファイルが開けない場合。
			@param[in] $iURL サイトURL。
		*/
		static function SetHomeURL( $iURL ) //
		{
			$tableName = new TableName( 'system' );
			$csv       = new CSV( 'system' );
			$statement = Query::GetSelectStatement( $tableName->real() , $csv->getColumns() );

			$statement->setFetchMode( PDO::FETCH_ASSOC );

			foreach( $statement as $row ) //全てのレコードを処理
			{
				$row[ 'home' ] = $iURL;

				break;
			}

			$statement->closeCursor();

			Query::UpdateRecord( $tableName->real() , $csv->getColumns() , $row );
		}

		static function SetTemplateCache( $iEnableSwitch ) //
		{
			$fp     = fopen( self::$TemplateCacheConfPath , 'rb' );
			$result = '';

			if( !$fp ) //ファイルが開けない場合
				{ throw new RuntimeException(); }

			while( !feof( $fp ) ) //ファイルの末端まで繰り返し
			{
				$line = fgets( $fp );

				$line = preg_replace( '/(\$USE_TEMPLATE_CACHE[\t\s]*=[\t\s]*)(true|false)/' , '$1' . ( $iEnableSwitch ? 'true' : 'false' )  , $line );

				$result .= $line;
			}

			$fp = fopen( self::$TemplateCacheConfPath , 'wb' );

			if( !$fp ) //ファイルが開けない場合
				{ throw new RuntimeException(); }

			fputs( $fp , $result );

			fclose( $fp );
		}

		/**
			@brief DB設定が使用できるか確認する。
			@param[in] $iMaster DBMSの種類。
			@param[in] $iHost   サーバー。
			@param[in] $iPort   ポート番号。
			@param[in] $iDBNmae データベース名。
			@param[in] $iUser   ユーザー名。
			@param[in] $iPass   パスワード。
			@retval    true  設定が使用できる場合。
			@retval    false 設定が使用できない場合。
		*/
		function VerifyDBConfig( $iMaster , $iHost , $iPort , $iDBName , $iUser , $iPass ) //
		{
			try
			{
				$db = CreateDBConnect( $iMaster , $iHost , $iPort , $iDBName , $iUser , $iPass );

				unset( $db );

				return true;
			}
			catch( Exception $e )
				{ return false; }
		}

		/**
			@brief FTP設定ファイルが使用できるか確認する。
			@exception RuntimeException 設定ファイルが開けない場合。
			@param[in] $iHost サーバー。
			@param[in] $iUser ユーザー名。
			@param[in] $iPass パスワード。
			@retval    true  設定が使用できる場合。
			@retval    false 設定が使用できない場合。
		*/
		function VerifyFTPConfig( $iHost , $iUser , $iPass ) //
		{
			try
			{
				$ftp = FTPLogic::Login( $iHost , $iUser , $iPass );

				unset( $ftp );

				return true;
			}
			catch( Exception $e ) //例外処理
				{ return false; }
		}

		//■変数 //
		private static $DBConfPath            = 'custom/extends/sqlConf.php';    ///<DB設定ファイルのパス。
		private static $FTPConfPath           = 'custom/extends/ftpConf.php';    ///<FTP設定ファイルのパス。
		private static $MobileConfPath        = 'custom/head_main.php';          ///<携帯設定ファイルのパス。
		private static $SSLConfPath           = 'custom/extends/sslConf.php';    ///<SSL設定ファイルのパス。
		private static $IDConfPath            = 'custom/extends/initConf.php';   ///<ID設定ファイルのパス。
		private static $TemplateCacheConfPath = 'custom/extends/systemConf.php'; ///<テンプレートキャッシュ設定ファイルのパス。
	}
