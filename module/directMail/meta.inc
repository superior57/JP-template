<?php

	$moduleName = 'directMail';

	$MODULES[ $moduleName ] = Array(
		'version' => '1.0', ///<このモジュールのバージョン情報。
		'summary' => 'DMモジュール。'  ///<このモジュールの説明。
	);

	$EDIT_TYPE									 = 'list';						// 設定名。
	$TABLE_NAME[]								 = $EDIT_TYPE;					// テーブル名として登録。
	$THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ]		 = false;						// このテーブルがユーザデータかを登録。
	$THIS_TABLE_IS_NOHTML[ $EDIT_TYPE ]			 = false;						// このテーブルがWEB上からデータを登録・変更等しないのかを指定。
	$LOGIN_KEY_COLUM[ $EDIT_TYPE ]				 = '';							// ユーザデータの場合はログインに用いるキー（メールアドレスなど）を保存しているカラム名を指定する。
	$LOGIN_PASSWD_COLUM[ $EDIT_TYPE ]			 = '';							// ユーザデータの場合はログインに用いるパスワードを保存しているカラム名を指定する。

	$LST[ $EDIT_TYPE ]							 = '[directMail]/list.csv';			// DB情報定義ファイル
	$TDB[ $EDIT_TYPE ]							 = '[directMail]/list.csv';			// DBファイル

	$ADD_LST[ "nUser" ][ 'list' ]    			 = '[directMail]/add/nUser.csv';
	$ADD_LST[ "cUser" ][ 'list' ]   			 = '[directMail]/add/cUser.csv';
	$ADD_LST[ "system" ][ 'list' ]   			 = '[directMail]/add/system.csv';

	$ID_HEADER[ $EDIT_TYPE ] 					 = 'ML';						// 会員IDの頭に付加する文字列
	$ID_LENGTH[ $EDIT_TYPE ]					 = 8;							// ヘッダを含めたIDの長さ（例:'C1234567' = 8）

	$css_file_paths[ 'admin' ][ 'directMail' ] = './module/directMail/common/css/directMail.css';


	$js_file_paths['admin']['dm_list']			= './module/directMail/common/js/list.js';

	//リスト登録の最大数
	$LIST_MAX_REGIST = 1000;	//0で無制限

	//送信リストの1ページあたりの表示数
	$LIST_OFFSET = 50;

	include_once './module/directMail/list.php';
	include_once './module/directMail/custom/model/List.php';
	include_once './module/directMail/custom/view/ListView.php';
	include_once './module/directMail/custom/api/listApi.php';


	$EDIT_TYPE								=	'mailSend';					 // 設定名。
	$TABLE_NAME[]							=	$EDIT_TYPE;				 // テーブル名として登録。
	$THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ]		 = false;						// このテーブルがユーザデータかを登録。
	$THIS_TABLE_IS_NOHTML[ $EDIT_TYPE ]			 = false;						// このテーブルがWEB上からデータを登録・変更等しないのかを指定。
	$LOGIN_KEY_COLUM[ $EDIT_TYPE ]				 = '';							// ユーザデータの場合はログインに用いるキー（メールアドレスなど）を保存しているカラム名を指定する。
	$LOGIN_PASSWD_COLUM[ $EDIT_TYPE ]			 = '';							// ユーザデータの場合はログインに用いるパスワードを保存しているカラム名を指定する。

	$LST[ $EDIT_TYPE ]						=	'[directMail]/mail_send.csv';	 	// DB情報定義ファイル
	$TDB[ $EDIT_TYPE ]						=	'[directMail]/mail_send.csv';	 	// DBファイル
	$ID_HEADER[ $EDIT_TYPE ] 				=	'MS';					// 会員IDの頭に付加する文字列
	$ID_LENGTH[ $EDIT_TYPE ]				=	10;						// ヘッダを含めたIDの長さ（例:'C1234567' = 8）

	$css_file_paths['admin']['progless'] 	= './module/directMail/common/css/progress.css';

	$js_file_paths['admin']['dm_progress']		= './module/directMail/common/js/progress.js';

	include_once './module/directMail/custom/model/mailSend.php';
	include_once './module/directMail/mail_send.php';
	include_once './module/directMail/custom/api/mailSendApi.php';
	include_once './module/directMail/custom/cron/mailSend.php';

	$EDIT_TYPE								=	'mailTemplate';					 // 設定名。
	$TABLE_NAME[]							=	$EDIT_TYPE;				 // テーブル名として登録。
	$THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ]		 = false;						// このテーブルがユーザデータかを登録。
	$THIS_TABLE_IS_NOHTML[ $EDIT_TYPE ]			 = false;						// このテーブルがWEB上からデータを登録・変更等しないのかを指定。
	$LOGIN_KEY_COLUM[ $EDIT_TYPE ]				 = '';							// ユーザデータの場合はログインに用いるキー（メールアドレスなど）を保存しているカラム名を指定する。
	$LOGIN_PASSWD_COLUM[ $EDIT_TYPE ]			 = '';							// ユーザデータの場合はログインに用いるパスワードを保存しているカラム名を指定する。

	$LST[ $EDIT_TYPE ]						=	'[directMail]/mail_template.csv';	 	// DB情報定義ファイル
	$TDB[ $EDIT_TYPE ]						=	'[directMail]/mail_template.csv';	 	// DBファイル
	$ID_HEADER[ $EDIT_TYPE ] 				=	'MT';					// 会員IDの頭に付加する文字列
	$ID_LENGTH[ $EDIT_TYPE ]				=	8;						// ヘッダを含めたIDの長さ（例:'C1234567' = 8）

	$js_file_paths['admin']['dm_common']		= './module/directMail/common/js/common.js';

	include_once './module/directMail/mail_template.php';
	include_once './module/directMail/custom/api/mailTemplateApi.php';

