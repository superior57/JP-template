<?php

	$moduleName = 'gmap';

	$MODULES[ $moduleName ] = Array(
		'version' => '1.0', ///<このモジュールのバージョン情報。
		'summary' => 'GoogleMapAPIキー設定モジュール' , ///<このモジュールの説明。
	);

	if(basename($_SERVER['PHP_SELF']) !== "tool.php") {
		$gkey = SystemUtil::getSystemData("gmap_key");
		
		if(!empty($gkey)) {
			$js_file_paths['all']['gmap'] = './common/js/map.js';
			$js_file_paths['all']['gmap2'] = './common/js/gmap.js';
			$js_file_paths['all']['gmap3'] = 'https://maps.googleapis.com/maps/api/js?libraries=places&language=ja&key='.$gkey;
		}
	}