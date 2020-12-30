<?php

	//★クラス //

	/**
		@brief   既定のパーミッション設定ツールのモデル。
	*/
	class AppPermitModel extends AppBaseModel //
	{
		//■処理 //

		/**
			@brief FTPで接続する。
		*/
		function connectFTP() //
		{
			global $SYSTEM_FTP_HOST;
			global $SYSTEM_FTP_USER;
			global $SYSTEM_FTP_PASS;
			global $SYSTEM_FTP_HOME;

			$this->ftp = FTPLogic::Login( $SYSTEM_FTP_HOST , $SYSTEM_FTP_USER , $SYSTEM_FTP_PASS );

			if( $ftp ) //接続に成功した場合
			{
				if( !$SYSTEM_FTP_HOME ) //ホームディレクトリが不明の場合
					{ $SYSTEM_FTP_HOME = FTPLogic::SurveyHomeDirectory( $ftp ); }
				}
		}

		/**
			@brief ファイルのパーミッションを変更する。
		*/
		function updatePermit() //
		{
			global $SYSTEM_FTP_HOME;
			global $SYSTEM_FTP_WRITEABLE_ENTRIES;
			global $SYSTEM_FTP_WRITEABLE_DIRECTORY_PERMIT;
			global $SYSTEM_FTP_WRITEABLE_FILE_PERMIT;

			$writeableEntries = Array();

			foreach( $SYSTEM_FTP_WRITEABLE_ENTRIES as $entry ) //全ての要素を処理
			{
				$globResult = glob( $entry );

				if( FALSE === $globResult ) //globに失敗した場合
					{ continue; }

				foreach( $globResult as $getEntry ) //全ての結果を処理
					{ $writeableEntries[] = $getEntry; }
			}

			$currentPath = getcwd() . '/';

			if( $SYSTEM_FTP_HOME == $currentPath ) //PHPとFTPのホームが同じ場合
				{ $relativePath = '/'; }
			else  //PHPとFTPのホームが異なる場合
				{ $relativePath = '/' . substr( $currentPath , strlen( $SYSTEM_FTP_HOME ) ); }

			if( !$ftp->ftp_chdir( $relativePath ) ) //FTPの作業ディレクトリをシステム設置先に移動できない場合
				{ return; }

			foreach( $writeableEntries as $entry ) //全ての操作対象を処理
			{
				if( is_dir( $entry ) ) //ディレクトリの場合
					{ $permit = $SYSTEM_FTP_WRITEABLE_DIRECTORY_PERMIT; }
				else //ファイルの場合
					{ $permit = $SYSTEM_FTP_WRITEABLE_FILE_PERMIT; }

				if( !$ftp->ftp_site( 'chmod ' . $permit . ' ' . $entry ) ) //パーミッションの変更に失敗した場合
					{ return; }

				$this->results[] = $permit . ' ' . $entry;
			}
		}

		//■変数 //
		var     $results = Array(); ///<処理結果の一覧。
		private $ftp     = null;    ///<FTP接続インスタンス。
	}
