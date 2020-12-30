<?php

	/****************************************************
	 *  FTP接続設定
	 ***************************************************/

	$SYSTEM_FTP_HOST = ''; //FTP接続に使用するホスト名。
	$SYSTEM_FTP_USER = ''; //FTP接続に使用するアカウントのユーザー名。
	$SYSTEM_FTP_PASS = ''; //FTP接続に使用するアカウントのパスワード。
	$SYSTEM_FTP_HOME = ''; //FTP接続に使用するアカウントのホームディレクトリ。※指定がない場合は自動的に検出しますが、ホームディレクトリに書き込み権限がない場合は正しく検出できない可能性があります。

	/****************************************************
	 *  FTP動作設定
	 ***************************************************/

	$SYSTEM_FTP_DEBUG_ENABLE     = false;                    //FTP通信内容を画面に表示する場合はtrue。
	$SYSTEM_FTP_HOME_SURVEY_NAME = 'WS_FTP_HOME_SERVEY_DIR'; //FTPのホームディレクトリを調査するのに一時的に生成するディレクトリの名前。サーバー上に存在していないものを指定してください。

	/***************************************************
	 *  パーミッション設定
	 ***************************************************/

	$SYSTEM_FTP_WRITEABLE_ENTRIES = Array( //書き込み可能権限を付与するファイルとディレクトリ。ワイルドカードとして*が使用できます。
		'./db/'                  ,
		'./db/tdb/'              ,
		'./db/tdb/indexs'        ,
		'./db/tdb/lst'           ,
		'./db/tdb/tdb'           ,
		'./db/tdb/tdb_hash'      ,
		'./db/tdb/template'      ,
		'./db/tdb/*/*.csv'       ,
		'./db/tdb_hash/*'        ,
		'./db/tdb_hash/*/*.csv'  ,
		'./feed/'                ,
		'./feed/*'               ,
		'./file/'                ,
		'./file/*'               ,
		'./file/*/*'             ,
		'./logs/'                ,
		'./logs/*.log'           ,
		'./logs/*.txt'           ,
		'./report/'              ,
		'./report/*'             ,
		'./tdb/'                 ,
		'./tdb/*.csv'            ,
		'./module/*/db/*'        ,
		'./module/*/db/*/*.csv'  ,
		'./custom/'              ,
		'./custom/head_main.php' ,
		'./custom/extends'       ,
		'./custom/extends/*'     ,
		'./module/*/template/*/other/mail_contents/*',
		'./module/*/template/*/other/mail_contents/*/*.txt',
		'./template/*/other/mail_contents/*',
		'./template/*/other/mail_contents/*/*.txt',
		'./templateCache/',
		'./templateCache/*',
		'./sitemap.xml',
	);

	$SYSTEM_FTP_EXECUTABLE_ENTRIES = Array( //実行権限を付与するファイル。
		'cron.php'
	);

	$SYSTEM_FTP_WRITEABLE_DIRECTORY_PERMIT = Array( 0777 , 0707 ); //ディレクトリの書き込み可能権限設定。多くの環境では0777ですが、稀に0707でなければならない環境があります。
	$SYSTEM_FTP_WRITEABLE_FILE_PERMIT      = Array( 0666 , 0606 ); //ファイルの書き込み可能権限設定。多くの環境では0666ですが、稀に0606でなければならない環境があります。
	$SYSTEM_FTP_EXECUTABLE_FILE_PERMIT     = Array( 0755 , 0705 ); //ファイルの書き込み可能権限設定。多くの環境では0755ですが、稀に0705でなければならない環境があります。
