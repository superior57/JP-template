<?php

	$moduleName = 'cmsSPCode';

	$MODULES[ $moduleName ] = Array(
		'version' => '1.0', ///<このモジュールのバージョン情報。
		'summary' => '特殊コマンドモジュール。'  ///<このモジュールの説明。
	);

	include_once './module/' . $moduleName . '/cmsSPCode.php';
	include_once './module/' . $moduleName . '/replaceCC.inc';
	include_once './module/' . $moduleName . '/custom/logic/cmsSPCodeLogic.php';
	include_once './module/' . $moduleName . '/custom/api/cmsSPCodeApi.php';

