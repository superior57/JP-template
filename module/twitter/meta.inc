<?php

	$moduleName = 'twitter';

	$MODULES[ $moduleName ] = Array(
		'version' => '1.0' ,               ///<このモジュールのバージョン情報。
		'summary' => 'twitterモジュール。' ///<このモジュールの説明。
	);

	include_once './module/' . $moduleName . '/include/vendor/autoload.php';
	include_once './module/' . $moduleName . '/custom/conf/twitterConf.php';
	include_once './module/' . $moduleName . '/custom/logic/twitter.php';
