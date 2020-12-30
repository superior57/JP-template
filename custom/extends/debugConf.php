<?php
	$ALL_DEBUG_FLAG = false;

	$DEBUG_TYPE = 'echo';	//  echo/file/header/subview/console
	$DEBUG_TRACE = false;
	$CONFIG_DEBUG_TEMPLATE_PATH_COMMENT = false;
    $DEBUG_ALLLLOW_IP = Array();


	if( $ALL_DEBUG_FLAG ){

		define( "DEBUG_FLAG_MAIL", false );
		define( "DEBUG_FLAG_TEMPLATE", false );
		define( "DEBUG_FLAG_CRON", false );
		define( "DEBUG_FLAG_CHECK_DATA", false );
		define( "DEBUG_FLAG_SQLDATABASE", false );
		define( "DEBUG_FLAG_RECORD_LOAD", false );
		define( "DEBUG_FLAG_RECORD_SET", false );
		define( "DEBUG_FLAG_EXCEPTION", false );
		define( "DEBUG_FLAG_CCPROC", false );

		ini_set( "display_errors",  1 );

		//	error_reporting(E_ALL);
		error_reporting( E_ERROR | E_WARNING |  E_PARSE );

		//  携帯表示シミュレート
		if( preg_match( '/UP.Browser/' , $_SERVER[ 'HTTP_USER_AGENT' ] ) ){
			$terminal_type = 2;
		}else if( preg_match( '/SoftBank/' , $_SERVER[ 'HTTP_USER_AGENT' ] ) ){
			$terminal_type = 3;
		}else if( preg_match( '/DoCoMo/' , $_SERVER[ 'HTTP_USER_AGENT' ] ) ){
			$terminal_type = 1;
		}


		$DEBUG_START_TIME = microtime(true);
		define( 'DEBUG_DRAW_CHANGE_DIFF', 0.05 );

		//メールクラスからのメール送信を止める。
		$MAIL_BLOCK = false;

		//実行時間の出力
		$DRAW_TIMER = true;

	}else{
		ini_set( "display_errors", 0 );
		error_reporting( 0 );

		define( "DEBUG_FLAG_MAIL", false );
		define( "DEBUG_FLAG_TEMPLATE", false );
		define( "DEBUG_FLAG_CRON", false );
		define( "DEBUG_FLAG_CHECK_DATA", false );
		define( "DEBUG_FLAG_SQLDATABASE", false );
		define( "DEBUG_FLAG_RECORD_LOAD", false );
		define( "DEBUG_FLAG_RECORD_SET", false );
		define( "DEBUG_FLAG_EXCEPTION", false );
		define( "DEBUG_FLAG_CCPROC", false );

		$DEBUG_TYPE = 'header';
		$MAIL_BLOCK = false;
		$DRAW_TIMER = false;
	}

	include_once "./include/extends/Debug.php";
