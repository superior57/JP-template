<?php
	$moduleName = 'smtp';

	$MODULES[ $moduleName ] = Array(
		'version' => '1.0'                                  , ///<このモジュールのバージョン情報。
		'summary' => 'PHPMailerを使用してSMTPを使えるようにする' , ///<このモジュールの説明。
    );

/**** smtp_confの定義 ************************************************/

	$EDIT_TYPE                            = 'smtp_conf';
	$TABLE_NAME[]                         = $EDIT_TYPE;
	$JP_TABLE_NAME[ $EDIT_TYPE ]          = 'SMTPの設定';
	$THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ] = false;
	$LOGIN_KEY_COLUM[ $EDIT_TYPE ]        = null;
	$LOGIN_PASSWD_COLUM[ $EDIT_TYPE ]     = null;
	$LST[ $EDIT_TYPE ]                    = '[smtp]/smtp_conf.csv';
	$TDB[ $EDIT_TYPE ]                    = '[smtp]/smtp_conf.csv';
	$ID_HEADER[ $EDIT_TYPE ]              = '';
	$ID_LENGTH[ $EDIT_TYPE ]              = 0;
    
    include_once 'module/' . $moduleName . '/vendor/autoload.php';
	include_once 'module/' . $moduleName . '/smtp.php';
	include_once 'module/' . $moduleName . '/custom/api/smtpApi.php';
    include_once 'module/' . $moduleName . '/custom/logic/SMTPLogic.php';
	include_once 'module/' . $moduleName . '/custom/SMTPSettings.php';