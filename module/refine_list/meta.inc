<?php
	$moduleName = 'refine_list';

	$MODULES[ $moduleName ] = Array(
		'version' => '1.0'                                  , ///<このモジュールのバージョン情報。
		'summary' => '絞り込みリストを表示' , ///<このモジュールの説明。
	);

	include_once 'module/'.$moduleName.'/refine_list.php';
	
	$css_file_paths[ 'nobody' ][ 'refine_list' ] = './module/'.$moduleName.'/common/css/refine_list.css';
	$css_file_paths[ 'nUser' ][ 'refine_list' ] = './module/'.$moduleName.'/common/css/refine_list.css';
