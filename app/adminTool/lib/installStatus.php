<?php

	//★クラス //

	/**
		@brief インストールステータス処理クラス。
	*/
	class InstallStatus //
	{
		//■処理 //

		static function DoSkip() //
		{
			self::Set( 'complete' , true );

			$_SESSION[ 'loginedAdminTool' ] = false;
			$_SESSION[ 'firstToolAccess' ] = false;
		}

		static function DoComplete() //
		{
			self::Set( 'complete' , true );

			$_SESSION[ 'loginedAdminTool' ] = false;
			$_SESSION[ 'firstToolAccess' ] = false;
		}

		static function DoExit() //
		{
			$_SESSION[ 'loginedAdminTool' ] = false;
			$_SESSION[ 'firstToolAccess' ]  = false;

			$fp = fopen( self::$EnableConfPath , 'wb' );

			if( !$fp ) //ファイルが開けない場合
				{ throw new RuntimeException(); }

			fputs( $fp , '<?php' . "\n" );
			fputs( $fp , "\t" . '$SYSTEM_INSTALL_STATUS[ \'disableTool\' ] = true;' . "\n" );
			fclose( $fp );
		}

		static function Skipable() //
		{
			global $SQL_MASTER;
			global $SQL_SERVER;
			global $SQL_PORT;
			global $DB_NAME;
			global $SQL_ID;
			global $SQL_PASS;

			if( self::Get( 'permission' ) ) //パーミッション設定が有効な場合
			{
			if( $_SESSION[ 'firstToolAccess' ] && is_file( self::$InstallConfPath ) ) //初回アクセスではない場合
				{ return self::Get( 'verify' ); }

			$_SESSION[ 'firstToolAccess' ] = true;

			if( InstallConfig::VerifyDBConfig( $SQL_MASTER , $SQL_SERVER , $SQL_PORT , $DB_NAME , $SQL_ID , $SQL_PASS ) ) //DB接続設定が使用できる場合
				{ self::Set( 'verify' , true ); }
			else //DB接続設定が使用できない場合
				{ self::Set( 'verify' , false ); }
			}

			return self::Get( 'verify' );
		}

		//■データ取得 //

		/**
			@brief  インストールステータスを取得する。
			@return ステータスの値。
		*/
		static function Get( $iStatusName ) //
		{
			global $SYSTEM_INSTALL_STATUS;

			return $SYSTEM_INSTALL_STATUS[ $iStatusName ];
		}

		static function IsComplete() //
			{ return self::Get( 'complete' ); }

		//■データ変更 //

		/**
			@brief     インストールステータスを更新する。
			@param[in] $iStatusName ステータス名。
			@param[in] $iState      ステータスの値。
		*/
		static function Set( $iStatusName , $iState ) //
		{
			global $SYSTEM_INSTALL_STATUS;

			$fp = fopen( self::$InstallConfPath , 'wb' );

			if( !$fp ) //ファイルが開けない場合
				{ throw new RuntimeException(); }

			$SYSTEM_INSTALL_STATUS[ $iStatusName ] = $iState;

			fputs( $fp , '<?php' . "\n" );
			chmod( self::$InstallConfPath,0666 );

			foreach( $SYSTEM_INSTALL_STATUS as $key => $value ) //全ての要素を処理
				{ fputs( $fp , "\t" . '$SYSTEM_INSTALL_STATUS[ \'' . $key . '\' ] = ' . ( $value ? 'true' : 'false' ) . ';' . "\n" ); }
		}

		//■変数 //
		private static $InstallConfPath = 'custom/extends/installConf.php'; ///<インストールステータス保存ファイルのパス。
		private static $EnableConfPath  = 'custom/extends/toolEnableConf.php'; ///<インストールステータス保存ファイルのパス。
	}
