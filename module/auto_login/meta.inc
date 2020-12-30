<?php

	$moduleName = 'auto_login';

	$MODULES[ $moduleName ] = Array(
		'version' => '1.0'                                  , ///<このモジュールのバージョン情報。
		'summary' => '自動ログイン処理' , ///<このモジュールの説明。
	);

	$REMEMBER_COOKIE								 = WS_PACKAGE_ID.'remember_cookie';

	$EDIT_TYPE									 = 'auto_login';						// 設定名。
	$TABLE_NAME[]								 = $EDIT_TYPE;					// テーブル名として登録。
	$THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ]		 = false;						// このテーブルがユーザデータかを登録。
	$THIS_TABLE_IS_NOHTML[ $EDIT_TYPE ]			 = false;						// このテーブルがWEB上からデータを登録・変更等しないのかを指定。
	$LOGIN_KEY_COLUM[ $EDIT_TYPE ]				 = '';							// ユーザデータの場合はログインに用いるキー（メールアドレスなど）を保存しているカラム名を指定する。
	$LOGIN_PASSWD_COLUM[ $EDIT_TYPE ]			 = '';							// ユーザデータの場合はログインに用いるパスワードを保存しているカラム名を指定する。
	$ID_HEADER[ $EDIT_TYPE ]				 = 'AL';
	$ID_LENGTH[ $EDIT_TYPE ]				 = 12;


	$LST[ $EDIT_TYPE ]						 = '[auto_login]/auto_login.csv';						// DB情報定義ファイル
	$TDB[ $EDIT_TYPE ]						 = '[auto_login]/auto_login.csv';						// DBファイル


	include_once './module/' . $moduleName . '/custom/logic/AutoLoginLogic.php';
