<?php

	$moduleName = 'nobody';

	$MODULES[ $moduleName ] = Array(
		'version' => '0.1', ///<このモジュールのバージョン情報。
		'summary' => '非会員モジュール。'  ///<このモジュールの説明。
	);

	$EDIT_TYPE                            = 'nobody';
	$TABLE_NAME[]                         = $EDIT_TYPE;
	$THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ] = false;
	$THIS_TABLE_IS_NOHTML[ $EDIT_TYPE ]   = false;
	$THIS_TABLE_REGIST_USER[ $EDIT_TYPE ] = Array( $NOT_LOGIN_USER_TYPE );
	$THIS_TABLE_EDIT_USER[ $EDIT_TYPE ]   = Array();
	$THIS_TABLE_OWNER_COLUM[ $EDIT_TYPE ] = Array();
	$LOGIN_KEY_COLUM[ $EDIT_TYPE ]        = null;
	$LOGIN_PASSWD_COLUM[ $EDIT_TYPE ]     = null;
	$LST[ $EDIT_TYPE ]                    = '[nobody]/nobody.csv';
	$TDB[ $EDIT_TYPE ]                    = '[nobody]/nobody.csv';
	$ID_HEADER[ $EDIT_TYPE ]              = 'NB';
	$ID_LENGTH[ $EDIT_TYPE ]              = 8;

	include_once './module/' . $EDIT_TYPE . '/nobody.php';
	include_once './module/' . $EDIT_TYPE . '/custom/logic/nobodyLogic.php';
	include_once './module/' . $EDIT_TYPE . '/custom/api/nobodyApi.php';
