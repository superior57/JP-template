<?php

	error_reporting( E_ALL );
	date_default_timezone_set( 'Asia/Tokyo' );
	set_time_limit( 120 );

	include_once "./include/base/QueryParser.php";

	include_once './custom/extends/debugConf.php';
	include_once './include/base/apiClass.php';
	include_once './include/extends/CodeScheduler.php';
	include_once './custom/conf.php';
	include_once './custom/extends/initConf.php';
	include_once './custom/extends/mobileConf.php';
	include_once './custom/extends/conf.php';
	include_once './module/module.inc';

	include_once './app/adminTool/lib/anyDB.php';
	include_once './app/adminTool/lib/mysqlDB.php';
	include_once './app/adminTool/lib/sqliteDB.php';
	include_once './app/adminTool/lib/sqlite3DB.php';
	include_once './app/adminTool/lib/csvBase.php';
	include_once './app/adminTool/lib/csv.php';
	include_once './app/adminTool/lib/db.php';
	include_once './app/adminTool/lib/insertScheduler.php';
	include_once './app/adminTool/lib/installConfig.php';
	include_once './app/adminTool/lib/installStatus.php';
	include_once './app/adminTool/lib/installLogic.php';
	include_once './app/adminTool/lib/misc.php';
	include_once './app/adminTool/lib/queryBase.php';
	include_once './app/adminTool/lib/queryWriterBase.php';
	include_once './app/adminTool/lib/tableName.php';
	include_once './app/mvc/mvc.php';
	include_once './include/templateCache.php';

	include_once './custom/extends/ftpConf.php';
	include_once './custom/logic/ftpLogic.php';
	include_once './custom/model/ftp.php';

	include_once './custom/extends/versionConf.php';

	if( is_file( './custom/extends/installConf.php' ) )
		{ include_once './custom/extends/installConf.php'; }

	if( is_file( './custom/extends/toolEnableConf.php' ) )
		{ include_once './custom/extends/toolEnableConf.php'; }

	$ENABLE_CHARCODE_SETTING = true;                        ///<文字コード関連の初期設定をする場合はtrue
	$TOOL_TEMPLATE_PATH      = './app/adminTool/template/'; ///<管理ツール用のテンプレートファイルのパス。
	$TOOL_PASSWORD_TABLE     = 'tool_admin_password';       ///<管理ツールのパスワードを保存するテーブル。
	$USE_PDO                 = true;                       ///<DB接続にPDOを使用する場合はtrue(システム側と合わせるために現状はfalseとする)
	$USE_SQLITE_VERSION      = 'auto';                      ///<SQLte使用時、2と3のどちらを使用するか。固定する場合は2か3を指定。PHPバージョンによって判断する場合はautoを指定。

	if( $ENABLE_CHARCODE_SETTING ) //文字コード関連設定が有効な場合
	{
		ini_set( 'output_buffering'              , 'Off' );              // 出力バッファリングを指定します
		ini_set( 'default_charset'               , $SYSTEM_CHARACODE );  // デフォルトの文字コードを指定します
		ini_set( 'extension'                     , 'php_mbstring.dll' ); // マルチバイト文字列を有効にします。
		ini_set( 'mbstring.language'             , 'uni' );              // デフォルトを日本語に設定します。
		ini_set( 'mbstring.internal_encoding'    , $SYSTEM_CHARACODE );  // 内部文字エンコーディングをSJISに設定します。
		ini_set( 'mbstring.http_input'           , 'auto' );             // HTTP入力文字エンコーディング変換をautoに設定します。
		ini_set( 'mbstring.http_output'          , $SYSTEM_CHARACODE );  // HTTP出力文字エンコーディング変換をSJISに設定します。
		ini_set( 'mbstring.encoding_translation' , 'On'   );             // 内部文字エンコーディングへの変換を有効にします。
		ini_set( 'mbstring.detect_order'         , 'auto' );             // 文字コード検出をautoに設定します。
		ini_set( 'mbstring.substitute_character' , 'none' );             // 無効な文字を出力しない。
		mb_http_output( $SYSTEM_CHARACODE );
		mb_internal_encoding( $SYSTEM_CHARACODE );
	}

	if( !isset( $template_csv_dirs ) ) //テンプレートパスの一覧が設定されていない場合
		{ $template_csv_dirs = Array( 'template' => $template_tdb_path , 'module' => './module/template/' ); }

	session_start();
