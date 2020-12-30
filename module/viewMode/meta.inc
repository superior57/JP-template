<?php
$moduleName = 'viewMode';

$MODULES[ $moduleName ] = Array(
	'version' => '0.0.1', ///<このモジュールのバージョン情報。
	'summary' => 'テーブルデータの表示モード切替モジュール' , ///<このモジュールの説明。
);

$js_file_paths['nUser']['viewMode']			= './module/viewMode/common/js/viewMode.js';
$js_file_paths['nobody']['viewMode']			= './module/viewMode/common/js/viewMode.js';

include_once './module/viewMode/viewMode.php';
include_once './module/viewMode/custom/api/viewModeapi.php';
include_once './module/viewMode/custom/model/ViewMode.php';
