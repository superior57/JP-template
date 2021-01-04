<?php


/*************************
 ** SQL DATABSE 用 定義 **
 *************************/

	$SQL											 = true;				// SQLを用いるかどうかのフラグ
	$SQL_SERVER										 = 'localhost';			// SQLのサーバ
//	$SQL_PORT										 = '';

	// SQLデーモンのクラス名
	$SQL_MASTER										 = 'SQLiteDatabase';
//	$SQL_MASTER										 = 'MySQLDatabase';

	$DB_NAME										 = 'kyukatsu_foodkyukatsu';			// データベース名
	$SQL_ID	 										 = 'kyukatsu_foodkkt';				// データベース管理ユーザーＩＤ
	$SQL_PASS  										 = 'wuVNH9SF97kiBaD';					// データベース管理ユーザーＰＡＳＳ

	$TABLE_PREFIX									 = '';

	$CONFIG_SQL_FILE_TYPES = Array('image','file');

	//the 128 bit key value for crypting
	$CONFIG_SQL_PASSWORD_KEY = 'derhymqadbrheng';
?>