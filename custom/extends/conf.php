<?php

/***************************
 ** 設定ファイルの読み込み**
 ***************************/

	include_once "./custom/extends/sqlConf.php";
	include_once "./custom/extends/tableConf.php";
	include_once "./custom/extends/exceptionConf.php";
	include_once "./custom/extends/sslConf.php";
	include_once "./custom/extends/systemConf.php";
	include_once "./custom/extends/cookieConf.php";
	include_once "./custom/extends/formConf.php";
	include_once "./custom/extends/filebaseConf.php";

/*************************
 *  拡張クラスの読み込み *
 *************************/

	include_once "./include/extends/DateUtil.php";
	include_once "./include/extends/fiscalDateUtil.php";

	//include_once "./include/extends/";
	//include_once "./include/extends/MobileUtil.php";