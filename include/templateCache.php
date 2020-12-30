<?php

	//★クラス //

	/**
		@brief テンプレートキャッシュ制御クラス。
	*/
	class TemplateCache //
	{
		//■処理 //

		/**
			@brief キャッシュ処理を初期化する。
		*/
		static function Initialize() //
			{ self::$HasPost = ( 0 < count( $_POST ) ); }

		/**
			@brief  現在のURLのキャッシュを読み込んで出力する。
			@retval キャッシュが使用できる場合。
			@retval キャッシュが使用できない場合。
		*/
		static function LoadCache() //
		{
			global $loginUserType;
			global $NOT_LOGIN_USER_TYPE;
			global $USE_TEMPLATE_CACHE;
            global $controllerName;

			if( $controllerName == "Register" )
				{ return false; }

			if( is_null( self::$NoCache ) )
				{ self::$NoCache = !$USE_TEMPLATE_CACHE; }

			if( $NOT_LOGIN_USER_TYPE != $loginUserType )
				{ return false; }

			if( self::$HasPost )
				{ return false; }

			if( self::$NoCache )
				{ return false; }

			$cacheFile = self::GetCacheFilePath();
			$usingFile = self::GetCacheFilePath() . '.utl';

			if( is_file( $cacheFile ) && is_file( $usingFile ) )
			{
				$cacheTime = filemtime( $cacheFile );

				if( self::$MaxCacheTime < time() - $cacheTime )
					{ return false; }

				if( self::GetDBUpdateTime() > $cacheTime )
					{ return false; }

				$usingList = explode( "\n" , file_get_contents( $usingFile ) );

				foreach( $usingList as $usingFile )
				{
					if( !is_file( $usingFile ) )
						{ continue; }

					if( filemtime( $usingFile ) > $cacheTime )
						{ return false; }
				}

				print file_get_contents( $cacheFile );

				self::$CacheUsed = true;

				return true;
			}
			else
				{ return false; }
		}

		/**
			@brief 現在のURLのキャッシュを保存する。
		*/
		static function SaveCache( $iSource ) //
		{
			global $loginUserType;
			global $NOT_LOGIN_USER_TYPE;
			global $USE_TEMPLATE_CACHE;
            global $controllerName;

			if( $controllerName == "Register" )
				{ return false; }

			if( is_null( self::$NoCache ) )
				{ self::$NoCache = !$USE_TEMPLATE_CACHE; }

			if( $NOT_LOGIN_USER_TYPE != $loginUserType )
				{ return false; }

			if( self::$HasPost )
				{ return false; }

			if( self::$NoCache )
				{ return false; }

			file_put_contents( self::GetCacheFilePath() , $iSource );
			file_put_contents( self::GetCacheFilePath() . '.utl' , implode( "\n" , array_keys( self::$UsingList ) ) );

			if( !chmod( self::GetCacheFilePath() , 0777 ) )
				{ chmod( self::GetCacheFilePath() , 0707 ); }

			if( !chmod( self::GetCacheFilePath() . '.utl' , 0777 ) )
				{ chmod( self::GetCacheFilePath() . '.utl' , 0707 ); }
		}

		//■データ変更 //

		/**
			@brief データベースの最終更新時刻を更新する。
		*/
		static function SetDBUpdateTime() //
			{ file_put_contents( 'templateCache/dbupdatetime' , time() ); }

		/**
			@brief     使用テンプレートリストにテンプレート名を追加する。
			@param[in] $iTemplateFile テンプレートファイル名。
		*/
		static function Using( $iTemplateFile ) //
			{ self::$UsingList[ $iTemplateFile ] = true; }

		//■データ取得 //

		/**
			@brief  現在のURLのキャッシュファイルのパスを取得する。
			@return キャッシュファイルのパス。
		*/
		static function GetCacheFilePath() //
		{
			global $terminal_type;
			global $sp_mode;
            global $controllerName;

			if( $_SERVER[ 'QUERY_STRING' ] )
				{ $url = $_SERVER[ 'SCRIPT_NAME' ] . '?' . $_SERVER[ 'QUERY_STRING' ]; }
			else
				{ $url = $_SERVER[ 'SCRIPT_NAME' ]; }

			$filePath = md5( $url );

			$directory = 'templateCache';
			if( !is_dir( $directory ) )
				{ mkdir( $directory ); }

			$directoryList[] = $controllerName;

			if( isset($_GET['type']) )
				{ $directoryList[] = $_GET['type']; }

			$mode = 'pc';
			if( $sp_mode ){ $mode = 'sp'; }
			$directoryList[] = $mode;

			foreach( $directoryList as $tmp )
			{
				$directory .= '/'.$tmp;
				if( !is_dir( $directory ) )
					{ mkdir( $directory ); }
			}

			return $directory.'/'. $terminal_type . $filePath;
		}

		/**
			@brief  データベースの最終更新時刻を更新する。
			@return データベースの最終更新時刻。
		*/
		static function GetDBUpdateTime() //
		{
			if( is_file( 'templateCache/dbupdatetime' ) )
				{ return file_get_contents( 'templateCache/dbupdatetime' ); }
			else
				{ return 0; }
		}

		//■変数 //

		static $CacheUsed    = false;   ///<キャッシュを出力済みの場合はtrue。
		static $NoCache      = null;    ///<キャッシュ更新を無効にする場合はtrue。
		static $MaxCacheTime = 600;     ///<キャッシュの有効期限(秒)
		static $UsingList    = Array(); ///<使用されたテンプレートファイル。
		static $HasPost      = false;   ///<POSTデータがある場合はtrue。
	}
