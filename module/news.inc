<?php

	//テーブル定義
	$EDIT_TYPE                            = 'news';
	$TABLE_NAME[]                         = $EDIT_TYPE;
	$THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ] = false;
	$THIS_TABLE_IS_NOHTML[ $EDIT_TYPE ]   = false;
	$LOGIN_KEY_COLUM[ $EDIT_TYPE ]        = null;
	$LOGIN_PASSWD_COLUM[ $EDIT_TYPE ]     = null;
	$LOGIN_PASSWD_COLUM2[ $EDIT_TYPE ]    = null;
	$LST[ $EDIT_TYPE ]                    = 'module/news.csv';
	$TDB[ $EDIT_TYPE ]                    = 'module/news.csv';
	$ID_HEADER[ $EDIT_TYPE ]              = 'N';
	$ID_LENGTH[ $EDIT_TYPE ]              = 8;

    $js_file_paths['admin']['mod_news'] = './common/js/module/news/news.js';

	include_once './custom/model/News.php';
	include_once './custom/view/NewsView.php';

?>
