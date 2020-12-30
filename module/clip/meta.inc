<?php

	$moduleName = 'clip';

	$MODULES[ $moduleName ] = Array(
		'version' => '1.0'                                  , ///<このモジュールのバージョン情報。
		'summary' => 'お気に入り管理' , ///<このモジュールの説明。
	);
	
	/**********************************
	 ** クリップ定義ファイル関連 **
	 **********************************/
	
	$EDIT_TYPE								 = 'clip';									// 設定名。
	$TABLE_NAME[]							 = $EDIT_TYPE;								// テーブル名として登録。
	$THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ]	 = false;									// このテーブルがユーザデータかを登録。
	$LOGIN_KEY_COLUM[ $EDIT_TYPE ]			 = null;									// ユーザデータの場合はログインに用いるキー（メールアドレスなど）を保存しているカラム名を指定する。
	$LOGIN_PASSWD_COLUM[ $EDIT_TYPE ]		 = null;									// ユーザデータの場合はログインに用いるパスワードを保存しているカラム名を指定する。
	$LOGIN_PASSWD_COLUM2[ $EDIT_TYPE ]		 = null;									// 確認用Pass name
	
	$LST[ $EDIT_TYPE ]						 = '[clip]/clip.csv';						// DB情報定義ファイル
	$TDB[ $EDIT_TYPE ]						 = '[clip]/clip.csv';						// DBファイル
	
	$ID_HEADER[ $EDIT_TYPE ]				 = 'CL';									// 会員IDの頭に付加する文字列
	$ID_LENGTH[ $EDIT_TYPE ]				 = 9;										// ヘッダを含めたIDの長さ（例:'C1234567' = 8）
	
	include_once './module/' . $EDIT_TYPE . '/clip.php';
	include_once './module/' . $EDIT_TYPE . '/custom/logic/clipLogic.php';
	
	$js_file_paths[ 'all' ][ 'clip' ] = './module/clip/common/js/clip.js';
	$sp_js_file_paths[ 'all' ][ 'clip' ] = './module/clip/common/js/clip.js';
