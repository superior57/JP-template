<?php
	$moduleName = 'edittext';

	$MODULES[ $moduleName ] = Array(
		'version' => '1.0'                                  , ///<このモジュールのバージョン情報。
		'summary' => 'タイトルテキスト編集' , ///<このモジュールの説明。
	);

	include_once 'module/'.$moduleName.'/custom/SearchValueManager.php';
	include_once 'module/'.$moduleName.'/custom/SearchObjectUtil.php';
	include_once 'module/'.$moduleName.'/custom/SearchObjectSettings.php';
	include_once 'module/'.$moduleName.'/custom/SearchObjectStack.php';
	include_once 'module/'.$moduleName.'/custom/exception/ExtendsException.php';
	include_once 'module/'.$moduleName.'/edittext.php';