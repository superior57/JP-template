<?php

    $moduleName = 'csvExport';

    $MODULES[ $moduleName ] = Array(
        'version' => '1.0'         , ///<このモジュールのバージョン情報。
        'summary' => 'csv取込み、出力' , ///<このモジュールの説明。
    );

    /**********************************
     ** excel出力定義ファイル関連 **
     **********************************/

	include_once 'module/'.$moduleName.'/csvExport.php';
	include_once 'module/'.$moduleName.'/custom/api/csvExportApi.php';
	include_once 'module/'.$moduleName.'/custom/logic/csvExportLogic.php';

	$API_ALLOW_LIST['mod_csvExportApi']      = array('all');
