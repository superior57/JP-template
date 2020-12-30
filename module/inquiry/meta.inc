<?php

	$moduleName = 'inquiry';

	$MODULES[ $moduleName ] = Array(
		'version' => '1.0'                                  , ///<このモジュールのバージョン情報。
		'summary' => '問合せ管理' , ///<このモジュールの説明。
	);
	
	//inquiryRequestFormをincludeして要望フォームを表示可能

	/**********************************
	 ** メール問合せ定義ファイル関連 **
	 **********************************/

	$EDIT_TYPE								 = 'inquiry';					 // 設定名。
	$TABLE_NAME[]							 = $EDIT_TYPE;					 // テーブル名として登録。
	$THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ]	 = false;						 // このテーブルがユーザデータかを登録。
	$THIS_TABLE_REGIST_USER[ $EDIT_TYPE ]	 = Array( $NOT_LOGIN_USER_TYPE, 'nUser', 'cUser', 'aUser' );
	$LOGIN_KEY_COLUM[ $EDIT_TYPE ]			 = '';							 // ユーザデータの場合はログインに用いるキー（メールアドレスなど）を保存しているカラム名を指定する。
	$LOGIN_PASSWD_COLUM[ $EDIT_TYPE ]		 = '';							 // ユーザデータの場合はログインに用いるパスワードを保存しているカラム名を指定する。


	$LST[ $EDIT_TYPE ]						 = '[inquiry]/inquiry.csv';	 // DB情報定義ファイル
	$TDB[ $EDIT_TYPE ]						 = '[inquiry]/inquiry.csv';	 // DBファイル

	$ID_HEADER[ $EDIT_TYPE ] 				 = 'I';							 // 会員IDの頭に付加する文字列
	$ID_LENGTH[ $EDIT_TYPE ]				 = 8;							 // ヘッダを含めたIDの長さ（例:'C1234567' = 8）
	
	$INQUIRY_SPAM_CHECK						 = true;							 // スパムメールの送信を遮断するならtrue
	$INQUIRY_SPAM_REGEX                      = '/^[a-zA-Z0-9:\/\._~\s\!\?]*$/';	 // スパム判定用の正規表現

	include_once './module/' . $EDIT_TYPE . '/custom/api/inquiryApi.php';
	
?>