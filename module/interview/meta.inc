<?php

	$moduleName = 'interview';

	$MODULES[ $moduleName ] = Array(
		'version' => '1.0', ///<このモジュールのバージョン情報。
		'summary' => 'インタビューモジュール。'  ///<このモジュールの説明。
	);

	/**********          interviewの定義          **********/

	$EDIT_TYPE                            = 'interview';
	$TABLE_NAME[]                         = $EDIT_TYPE;
	$THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ] = false;
	$THIS_TABLE_REGIST_USER[ $EDIT_TYPE ] = Array('cUser');
	$THIS_TABLE_EDIT_USER[ $EDIT_TYPE ]   = Array('cUser');
	$THIS_TABLE_OWNER_COLUM[ $EDIT_TYPE ] = Array('cUser' => 'owner');
	$THIS_TABLE_MAX_REGIST[ $EDIT_TYPE ]  = Array('cUser' => 1);
	$LOGIN_KEY_COLUM[ $EDIT_TYPE ]        = null;
	$LOGIN_PASSWD_COLUM[ $EDIT_TYPE ]     = null;
	$LST[ $EDIT_TYPE ]                    = '[interview]/interview.csv';
	$TDB[ $EDIT_TYPE ]                    = '[interview]/interview.csv';
	$ID_HEADER[ $EDIT_TYPE ]              = 'IV';
	$ID_LENGTH[ $EDIT_TYPE ]              = 9;

	include_once './module/' . $EDIT_TYPE . '/interview.php';
	include_once './module/' . $EDIT_TYPE . '/custom/logic/interviewLogic.php';
	include_once './module/' . $EDIT_TYPE . '/custom/view/interviewView.php';

	$css_file_paths[ 'nUser' ][ 'interview' ] = './module/interview/common/css/interview.css';
	$css_file_paths[ 'nobody' ][ 'interview' ] = './module/interview/common/css/interview.css';
	$css_file_paths[ 'cUser' ][ 'interview' ] = './module/interview/common/css/interview.css';
	$css_file_paths[ 'admin' ][ 'interview' ] = './module/interview/common/css/interview_admin.css';


	/**********          interview_confの定義          **********/

	$EDIT_TYPE								 = 'interview_conf';
	$TABLE_NAME[]							 = $EDIT_TYPE;
	$THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ]	 = false;
	$LOGIN_KEY_COLUM[ $EDIT_TYPE ]			 = null;
	$LOGIN_PASSWD_COLUM[ $EDIT_TYPE ]		 = null;
	$LST[ $EDIT_TYPE ]						 = '[interview]/interview_conf.csv';
	$TDB[ $EDIT_TYPE ]						 = '[interview]/interview_conf.csv';
	$ID_HEADER[ $EDIT_TYPE ]                 = '';
	$ID_LENGTH[ $EDIT_TYPE ]                 = 0;
