<?php

	$moduleName = 'special';

	$MODULES[ $moduleName ] = Array(
		'version' => '1.0', ///<このモジュールのバージョン情報。
		'summary' => '特集ページ' , ///<このモジュールの説明。
	);

	$EDIT_TYPE                            = 'special';
	
	$ADD_LST[ "page" ][ 'special' ]    = '[special]/add/page.csv';
	$ADD_LST[ "mid" ][ 'special' ]    = '[special]/add/mid.csv';
	$ADD_LST[ "fresh" ][ 'special' ]    = '[special]/add/fresh.csv';
	
	include_once './module/' . $EDIT_TYPE . '/special.php';
	include_once './module/' . $EDIT_TYPE . '/custom/api/specialSettingApi.php';
	include_once './module/' . $EDIT_TYPE . '/custom/logic/specialLogic.php';
	
	
	//$css_file_paths[ 'all' ][ 'press' ] = './module/press/common/css/press.css';

    $js_file_paths[ 'all' ][ 'special' ] = './module/special/common/js/specialSetting.js';

	