<?php
	$moduleName = 'disp_conf';

	$MODULES[ $moduleName ] = Array(
		'version' => '1.0'                                  , ///<このモジュールのバージョン情報。
		'summary' => 'トップページ表示非表示設定' , ///<このモジュールの説明。
	);

	$EDIT_TYPE								 = 'disp_conf';
	$TABLE_NAME[]							 = $EDIT_TYPE;
	$THIS_TABLE_REGIST_USER[ $EDIT_TYPE ]	 = Array('admin');
	$THIS_TABLE_EDIT_USER[ $EDIT_TYPE ]  	 = Array('admin');
	$THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ]	 = false;
	$LOGIN_KEY_COLUM[ $EDIT_TYPE ]			 = null;
	$LOGIN_PASSWD_COLUM[ $EDIT_TYPE ]		 = null;
	$LST[ $EDIT_TYPE ]						 = '[disp_conf]/disp_conf.csv';
	$TDB[ $EDIT_TYPE ]						 = '[disp_conf]/disp_conf.csv';
	$ID_HEADER[ $EDIT_TYPE ]                 = '';
	$ID_LENGTH[ $EDIT_TYPE ]                 = 0;

	include_once 'module/'.$moduleName.'/disp_conf.php';