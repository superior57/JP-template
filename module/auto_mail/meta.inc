<?php

	$moduleName = 'auto_mail';

	$MODULES[ $moduleName ] = Array(
		'version' => '1.0'                                  , ///<このモジュールのバージョン情報。
		'summary' => '自動配信メール管理' , ///<このモジュールの説明。
	);


	
	include_once './module/' . $moduleName . '/custom/model/AutoMail.php';
	include_once './module/' . $moduleName . '/custom/view/autoMailView.php';
	include_once './module/' . $moduleName . '/custom/logic/autoMailLogic.php';
	include_once './module/' . $moduleName . '/custom/api/autoMailApi.php';


?>