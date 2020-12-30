<?php

	include_once 'custom/extends/ftpConf.php';
	include_once 'custom/logic/ftpLogic.php';
	include_once 'custom/model/ftp.php';

	//★処理 //

	try
	{
		$ftp = FTPLogic::Login( $SYSTEM_FTP_HOST , $SYSTEM_FTP_USER , $SYSTEM_FTP_PASS );

		if( !$SYSTEM_FTP_HOME ) //ホームディレクトリが不明の場合
			{ $SYSTEM_FTP_HOME = FTPLogic::SurveyHomeDirectory( $ftp ); }

		InitPermit( $ftp );
	}
	catch( Exception $e )
		{ print $e->getMessage(); }

	if( isset( $ftp ) && $ftp ) //FTP接続が完了している場合
		{ $ftp->ftp_quit(); }

	//★関数 //

	/**
		@brief         システムファイルのパーミッションを変更する。
		@details       ホームディレクトリへの書き込みができない場合の代替処理です。
		@exception     InvalidArgumentException $ftp に無効な値が指定された場合。
		@exception     RuntimeException         パーミッションの変更に失敗した場合
		@param[in,out] $ftp FTP接続オブジェクト。
	*/
	function InitPermit( $ftp ) //
	{
		global $SYSTEM_FTP_HOME;
		global $SYSTEM_FTP_WRITEABLE_ENTRIES;
		global $SYSTEM_FTP_EXECUTABLE_ENTRIES;
		global $SYSTEM_FTP_WRITEABLE_DIRECTORY_PERMIT;
		global $SYSTEM_FTP_WRITEABLE_FILE_PERMIT;
		global $SYSTEM_FTP_EXECUTABLE_FILE_PERMIT;

		if( !$ftp ) //FTP接続オブジェクトが空の場合
			{ throw new InvalidArgumentException( '引数 $ftp は無効です' ); }

		$currentPath = getcwd() . '/';

		if( $SYSTEM_FTP_HOME == $currentPath ) //PHPとFTPのホームが同じ場合
			{ $relativePath = '/'; }
		else  //PHPとFTPのホームが異なる場合
			{ $relativePath = '/' . substr( $currentPath , strlen( $SYSTEM_FTP_HOME ) ); }

		if( !$ftp->ftp_chdir( $relativePath ) ) //FTPの作業ディレクトリをシステム設置先に移動できない場合
			{ throw new RuntimeException( 'FTPInitPermit を完了できません[' . $relativePath . ']' ); }

		foreach( $SYSTEM_FTP_WRITEABLE_ENTRIES as $entryPath ) //全てのエントリを処理
		{
			$entries = GlobEntries( Array( $entryPath ) );
			$success = false;

			foreach( $entries as $entry ) //全てのエントリを処理
			{
				if( is_dir( $entry ) ) //ディレクトリの場合
				{
					foreach( $SYSTEM_FTP_WRITEABLE_DIRECTORY_PERMIT as $permit ) //全ての権限候補を処理
					{
						if( $ftp->ftp_site( 'chmod ' . sprintf( '%o' , $permit ) . ' ' . $entry ) ) //権限設定に成功した場合
						{
							print 'chmod ' . sprintf( '%o' , $permit ) . ' ' . $entry . '<br />';
							$success = true;

							break;
						}
					}
				}
				else //ファイルの場合
				{
					foreach( $SYSTEM_FTP_WRITEABLE_FILE_PERMIT as $permit ) //全ての権限候補を処理
					{
						if( $ftp->ftp_site( 'chmod ' . sprintf( '%o' , $permit ) . ' ' . $entry ) ) //権限設定に成功した場合
						{
							print 'chmod ' . sprintf( '%o' , $permit ) . ' ' . $entry . '<br />';
							$success = true;

							break;
						}
					}
				}

				if( !$success )
					{ throw new RuntimeException( 'FTPInitPermit を完了できません[' . $entry . ']' ); }
			}
		}

		foreach( $SYSTEM_FTP_EXECUTABLE_ENTRIES as $entry ) //全てのエントリを処理
		{
			$success = false;

			foreach( $SYSTEM_FTP_EXECUTABLE_FILE_PERMIT as $permit ) //全ての権限候補を処理
			{
				if( $ftp->ftp_site( 'chmod ' . sprintf( '%o' , $permit ) . ' ' . $entry ) ) //権限設定に成功した場合
				{
					print 'chmod ' . sprintf( '%o' , $permit ) . ' ' . $entry . '<br />';
					$success = true;

					break;
				}
			}

			if( !$success )
				{ throw new RuntimeException( 'FTPInitPermit を完了できません[' . $entry . ']' ); }
		}
	}

	/**
		@brief     全ての配列要素にglobを実行し、一次配列にして返す。
		@param[in] $entries パス配列。
		@return    globの結果を格納した配列。
	*/
	function GlobEntries( $entries ) //
	{
		$result = Array();

		foreach( $entries as $entry ) //全ての要素を処理
		{
			$globResult = glob( $entry );

			if( FALSE === $globResult ) //globに失敗した場合
				{ continue; }

			foreach( $globResult as $getEntry ) //全ての結果を処理
				{ $result[] = $getEntry; }
		}

		return $result;
	}
