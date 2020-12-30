<?php

	class FTPLogic //
	{
		/**
			@brief     FTP接続を開始する。
			@exception InvalidArgumentException $host , $user , $pass のいずれかに無効な値が指定された場合。
			@exception RuntimeException         ホストに接続できない、またはログインできない場合。
			@param[in] $host 接続先ホスト。
			@param[in] $user 接続ユーザー。
			@param[in] $pass 接続ユーザーのパスワード。
			@return    FTP接続クラスのインスタンス。
		*/
		static function Login( $host , $user , $pass ) //
		{
			global $SYSTEM_FTP_DEBUG_ENABLE;

			if( !$host ) //ホストが空の場合
				{ throw new InvalidArgumentException( '引数 $host は無効です' ); }

			if( !$user ) //ユーザー名が空の場合
				{ throw new InvalidArgumentException( '引数 $user は無効です' ); }

			if( !$pass ) //パスワードが空の場合
				{ throw new InvalidArgumentException( '引数 $pass は無効です' ); }

			$ftp        = new ftp();
			$ftp->debug = $SYSTEM_FTP_DEBUG_ENABLE;

			if( !$ftp->ftp_connect( $host ) ) //接続に失敗した場合
				{ throw new RuntimeException( 'FTPLogin を完了できません[' . $host . ']' ); }

			try
			{
				if( !$ftp->ftp_login( $user , $pass ) ) //ログインに失敗した場合
					{ throw new RuntimeException( 'FTPLogin を完了できません[' . $user . '][' . $pass . ']' ); }

				return $ftp;
			}
			catch( Exception $e )
			{
				$ftp->ftp_quit();

				throw  $e;
			}
		}

		/**
			@brief         FTP接続のホームディレクトリを調べる。
			@exception     InvalidArgumentException $ftp に無効な値が指定された場合。
			@exception     RuntimeException         ホームディレクトリの検出に失敗した場合
			@param[in,out] $ftp FTP接続オブジェクト。
			@return        ホームディレクトリのパス。
		*/
		static function SurveyHomeDirectory( $ftp ) //
		{
			global $SYSTEM_FTP_HOME_SURVEY_NAME;

			if( !$ftp ) //FTP接続オブジェクトが空の場合
				{ throw new InvalidArgumentException( '引数 $ftp は無効です' ); }

			if( !$ftp->ftp_chdir( '/' ) ) //ホームディレクトリへの移動に失敗した場合
				{ throw new RuntimeException( 'FTPSurveyHomeDirectory を完了できません' ); }

			$surveyName = $SYSTEM_FTP_HOME_SURVEY_NAME;
			$entries    = $ftp->ftp_nlist();

			while( in_array( $surveyName , $entries ) ) //既存ディレクトリが存在する間繰り返し
				{ $surveyName .= '_'; }

			if( !$ftp->ftp_mkdir( $surveyName ) ) //一時ディレクトリの生成に失敗した場合
				{ return self::SurveyHomeDirectoryByList( $ftp ); }

			try
			{
				$currentDir = getcwd();

				if( '/' != substr( $currentDir , -1 ) ) //末尾が/でない場合
					{ $currentDir .= '/'; }

				while( $currentDir && !is_dir( $currentDir . $surveyName ) ) //一時ディレクトリがない場合
					{ $currentDir = preg_replace( '/(.*\/)[^\/]+\/$/' , '$1' , $currentDir ); }

				if( !$currentDir ) //ディレクトリが空になった場合
					{ throw new RuntimeException( 'FTPSurveyHomeDirectory を完了できません' ); }

				$ftp->ftp_rmdir( $surveyName );

				return $currentDir;
			}
			catch( Exception $e )
			{
				$ftp->ftp_rmdir( $surveyName );

				throw  $e;
			}
		}

		/**
			@brief         FTP接続のホームディレクトリを調べる。
			@exception     InvalidArgumentException $ftp に無効な値が指定された場合。
			@exception     RuntimeException         ホームディレクトリの検出に失敗した場合
			@param[in,out] $ftp FTP接続オブジェクト。
			@return        ホームディレクトリのパス。
		*/
		static function SurveyHomeDirectoryByList( $ftp ) //
		{
			if( !$ftp ) //FTP接続オブジェクトが空の場合
				{ throw new InvalidArgumentException( '引数 $ftp は無効です' ); }

			if( !$ftp->ftp_chdir( '/' ) ) //ホームディレクトリへの移動に失敗した場合
				{ throw new RuntimeException( 'FTPSurveyHomeDirectoryByList を完了できません' ); }

			$entries    = $ftp->ftp_nlist();
			$currentDir = getcwd();

			if( '/' != substr( $currentDir , -1 ) ) //末尾が/でない場合
				{ $currentDir .= '/'; }

			while( $currentDir ) //全ての階層を処理
			{
				$dir      = opendir( $currentDir );
				$entryNum = 0;
				$matchNum = 0;

				if( $dir ) //ディレクトリのオープンに成功した場合
				{
					while( ( $entry = readdir( $dir ) ) !== false ) //全てのファイルとディレクトリを処理
					{
						++$entryNum;

						if( '.' == $entry || '..' == $entry ) //比較の必要がないもの
							{ continue; }

						if( in_array( $entry , $entries ) ) //ディレクトリに存在する場合
							{ ++$matchNum; }
					}

					closedir( $dir );

					if( 100 * ( $matchNum / $entryNum ) > 75 ) //一致率が75%以上だった場合
						{ break; }
				}

				if( '/' == $currentDir ) //これ以上ディレクトリを遡れない場合
					{ break; }

				$currentDir = preg_replace( '/(.*\/)[^\/]+\/$/' , '$1' , $currentDir );
			}

			return $currentDir;
		}

		/**
			@brief         FTP接続の作業ディレクトリをPHPの作業ディレクトリと同期する。
			@exception     InvalidArgumentException $ftp に無効な値が指定された場合。
			@param[in,out] $ftp FTP接続オブジェクト。
			@retval        true  同期に成功した場合。
			@retval        false 同期に失敗した場合。
		*/
		static function SyncWorkDirectory( $ftp )
		{
			if( !$ftp ) //FTP接続オブジェクトが空の場合
				{ throw new InvalidArgumentException( '引数 $ftp は無効です' ); }

			$pwd = $ftp->ftp_pwd();

			try
			{
				$ftpHome     = self::SurveyHomeDirectory( $ftp );
				$currentPath = getcwd() . '/';

				if( $ftpHome == $currentPath ) //PHPとFTPのホームが同じ場合
					{ $relativePath = '/'; }
				else  //PHPとFTPのホームが異なる場合
					{ $relativePath = substr( $currentPath , strlen( $ftpHome ) ); }

				if( !$ftp->ftp_chdir( $relativePath ) ) //FTPの作業ディレクトリをシステム設置先に移動できない場合
					{ throw new RuntimeException( 'FTPInitPermit を完了できません[' . $relativePath . ']' ); }

				return true;
			}
			catch( Exception $e )
			{
				$ftp->ftp_chdir( $pwd );

				return false;
			}
		}
	}
