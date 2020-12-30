<?php
	
	$moduleName = 'resetter';

	$MODULES[ $moduleName ] = Array(
		'version' => '1.0'                                  , ///<このモジュールのバージョン情報。
		'summary' => 'パスワードリセット管理' , ///<このモジュールの説明。
	);
	
	include_once './module/' . $moduleName . '/custom/api/resetterApi.php';
	include_once './module/' . $moduleName . '/custom/view/resetterView.php';
	



?>
