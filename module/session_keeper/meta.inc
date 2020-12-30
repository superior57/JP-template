<?php

	$moduleName = 'session_keeper';

	$MODULES[ $moduleName ] = Array(
		'version' => '1.0'                                  , ///<このモジュールのバージョン情報。
		'summary' => 'セッション監視管理' , ///<このモジュールの説明。
	);

	include_once './module/' . $moduleName . '/custom/api/session_keeperApi.php';

	$gc_maxlifetime=ini_get('session.gc_maxlifetime');

	$css_file_paths['cUser']['session_keeper'] = './module/session_keeper/common/css/session_keeper.css';
	$css_file_paths['nUser']['session_keeper'] = './module/session_keeper/common/css/session_keeper.css';

	$js_file_paths['nUser']['session_keeper'] = './module/session_keeper/common/js/session_keeper.php?mlt='.$gc_maxlifetime.'&ac='.$_GET["app_controller"];
	$js_file_paths['cUser']['session_keeper'] = './module/session_keeper/common/js/session_keeper.php?mlt='.$gc_maxlifetime.'&ac='.$_GET["app_controller"];


?>