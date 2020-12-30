<?php

	//■trueの場合、エラーメッセージを例外オブジェクトに変換してスローします
	$EXCEPTION_CONF[ 'UseErrorToException' ] = false;

	//■trueの場合、シャットダウン関数でエラーを検出した場合にログを出力します
	//　※環境によってはシャットダウン関数内でfopenを呼び出せないため、エラーメッセージが出力されることがあります
	$EXCEPTION_CONF[ 'UseShutdownErrorLog' ] = true;

	//▼エラーログを出力するファイル名
	$EXCEPTION_CONF[ 'ErrorLogFile' ] = './logs/error.log';

	//▼ログ出力および例外へ変換するエラーレベル
	$EXCEPTION_CONF[ 'ErrorHandlerLevel' ] = E_ERROR | E_WARNING | E_USER_ERROR | E_USER_WARNING | E_USER_NOTICE | E_RECOVERABLE_ERROR;

	//▼カレントディレクトリ(シャットダウン関数からのfopen呼び出しに必要です)
	$EXCEPTION_CONF[ 'WorkDirectory' ] = getcwd();

	//▼エラーログ出力から除外する例外の種類
	$EXCEPTION_CONF[ 'SecretExceptionType' ] = Array( 'IllegalTokenAccessException' , 'InternalErrorException' );

	include_once './include/extends/Exception/Concept.php';
	include_once './include/extends/Exception/ConceptSystem.php';
	include_once './include/extends/Exception/ExceptionManager.php';
	include_once './include/extends/Exception/ErrorManager.php';
?>