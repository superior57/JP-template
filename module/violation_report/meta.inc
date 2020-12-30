<?php

	$moduleName = 'violation_report';

	$MODULES[ $moduleName ] = Array(
		'version' => '1.0', ///<このモジュールのバージョン情報。
		'summary' => '違反通報モジュール。'  ///<このモジュールの説明。
	);

	/**********          violation_reportの定義          **********/

	$EDIT_TYPE                            = 'violation_report';
	$TABLE_NAME[]                         = $EDIT_TYPE;
	$THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ] = false;
	$THIS_TABLE_REGIST_USER[ $EDIT_TYPE ] = Array('nUser');
	$THIS_TABLE_EDIT_USER[ $EDIT_TYPE ]   = Array('nUser');
	$THIS_TABLE_OWNER_COLUM[ $EDIT_TYPE ] = Array( 'nUser' => 'owner' );
	$LOGIN_KEY_COLUM[ $EDIT_TYPE ]        = null;
	$LOGIN_PASSWD_COLUM[ $EDIT_TYPE ]     = null;
	$LST[ $EDIT_TYPE ]                    = '[violation_report]/violation_report.csv';
	$TDB[ $EDIT_TYPE ]                    = '[violation_report]/violation_report.csv';
	$ID_HEADER[ $EDIT_TYPE ]              = 'VR';
	$ID_LENGTH[ $EDIT_TYPE ]              = 9;

	//$ADD_LST[ "nUser" ][ 'violation_report' ]    = '[violationReport]/add/nUser.csv';
	//$ADD_LST[ "cUser" ][ 'violation_report' ]    = '[violationReport]/add/cUser.csv';

	include_once './module/' . $EDIT_TYPE . '/violation_report.php';
	//include_once './module/' . $EDIT_TYPE . '/custom/logic/violationReportLogic.php';
	include_once './module/' . $EDIT_TYPE . '/violation_report_category.php';

	/**********          violation_report_categoryの定義          **********/

	$EDIT_TYPE                            = 'violation_report_category';
	$TABLE_NAME[]                         = $EDIT_TYPE;
	$THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ] = false;
	$LOGIN_KEY_COLUM[ $EDIT_TYPE ]        = null;
	$LOGIN_PASSWD_COLUM[ $EDIT_TYPE ]     = null;
	$LST[ $EDIT_TYPE ]                    = '[violation_report]/violation_report_category.csv';
	$TDB[ $EDIT_TYPE ]                    = '[violation_report]/violation_report_category.csv';
	$ID_HEADER[ $EDIT_TYPE ]              = 'VC';
	$ID_LENGTH[ $EDIT_TYPE ]              = 5;
	
	//$css_file_paths[ 'nobody' ][ 'violation_report' ] = './module/violation_report/common/css/style.css';
	$css_file_paths[ 'nUser' ][ 'violation_report' ] = './module/violation_report/common/css/violation_report.css';
	//$css_file_paths[ 'cUser' ][ 'violation_report' ] = './module/violation_report/common/css/system/admin.css';
