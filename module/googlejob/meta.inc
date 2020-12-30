<?php

	$moduleName = 'googlejob';

	$MODULES[ $moduleName ] = Array(
		'version' => '1.0', ///<このモジュールのバージョン情報。
		'summary' => 'googlejobのタグをheadに' , ///<このモジュールの説明。
	);


	$EDIT_TYPE = 'googlejob_conf';
	$TABLE_NAME[] = $EDIT_TYPE;
	$JP_TABLE_NAME[$EDIT_TYPE] = 'Googleしごと検索';
	$THIS_TABLE_IS_USERDATA[$EDIT_TYPE] = false;
	$LOGIN_KEY_COLUM[$EDIT_TYPE] = null;
	$LOGIN_PASSWD_COLUM[$EDIT_TYPE] = null;
	$LST[$EDIT_TYPE] = '[googlejob]/googlejob_conf.csv';
	$TDB[$EDIT_TYPE] = '[googlejob]/googlejob_conf.csv';
	$ID_HEADER[$EDIT_TYPE] = '';
	$ID_LENGTH[$EDIT_TYPE] = 0;

	include_once './module/' . $moduleName . '/googlejob.php';
	include_once './module/' . $moduleName . '/custom/GoogleJobSettings.php';
	include_once './module/' . $moduleName . '/custom/logic/GoogleJobLogic.php';
