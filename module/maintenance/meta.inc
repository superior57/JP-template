<?php

	$moduleName = 'maintenance';

	$MODULES[ $moduleName ] = Array(
		'version' => '1.0'                    , ///<このモジュールのバージョン情報。
		'summary' => 'メンテナンス表示の管理' , ///<このモジュールの説明。
	);

	//テーブル定義
	$EDIT_TYPE                            = 'maintenance';
	$TABLE_NAME[]                         = $EDIT_TYPE;
	$THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ] = false;
	$THIS_TABLE_IS_NOHTML[ $EDIT_TYPE ]   = false;
	$LOGIN_KEY_COLUM[ $EDIT_TYPE ]        = null;
	$LOGIN_PASSWD_COLUM[ $EDIT_TYPE ]     = null;
	$LOGIN_PASSWD_COLUM2[ $EDIT_TYPE ]    = null;
	$LST[ $EDIT_TYPE ]                    = '[maintenance]/maintenance.csv';
	$TDB[ $EDIT_TYPE ]                    = '[maintenance]/maintenance.csv';
	$ID_HEADER[ $EDIT_TYPE ]              = 'MT';
	$ID_LENGTH[ $EDIT_TYPE ]              = 6;

	include_once 'module/' . $moduleName . '/maintenance.php';
	include_once 'module/' . $moduleName . '/custom/logic/maintenanceLogic.php';

	CodeScheduler::BeforeMain( 'MaintenanceLogic' , 'Run' , Array() );
