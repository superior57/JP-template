<?php

/*****************************************************************************
 *
 * 定数宣言
 *
 ****************************************************************************/
	define( "WS_PACKAGE_ID", "jc2" );				//セッションキーprefixなどに使う、パッケージを識別する為のID

	$UPDATE_NOTICE									 = true;								// パッケージアップデート情報を表示するかどうか

	$BASE_TAG										 = true;								// ベースタグを表示するかどうか

	$NOT_LOGIN_USER_TYPE							 = 'nobody';							// ログインしていない状態のユーザ種別名
	$NOT_HEADER_FOOTER_USER_TYPE					 = 'nothf';								// ヘッダー・フッターを表示していない状態のユーザ種別名

	$LOGIN_KEY_FORM_NAME							 = 'mail';								// ログインフォームのキーを入力するフォーム名
	$LOGIN_PASSWD_FORM_NAME							 = 'passwd';							// ログインフォームのパスワードを入力するフォーム名

	$SESSION_NAME									 = WS_PACKAGE_ID.'loginid';				// ログイン情報を管理するSESSION の名前
	$COOKIE_NAME									 = WS_PACKAGE_ID.'loginid';				// ログイン情報を管理するCOOKIE の名前

	$SESSION_TYPE									 = WS_PACKAGE_ID.'logintype';			// ログイン情報(TYPE)を管理するSESSION の名前
	$COOKIE_TYPE									 = WS_PACKAGE_ID.'logintype';			// ログイン情報(TYPE)を管理するCOOKIE の名前

	$SESSION_PATH_NAME								 = WS_PACKAGE_ID.'__system_path__';		// システムの設置パス情報を管理するSESSION の名前
	$COOKIE_PATH_NAME								 = WS_PACKAGE_ID.'__system_path__';		// システムの設置パス情報を管理するCOOKIE の名前

	$ACTIVE_NONE									 = 1;									// アクティベートされていない状態を表す定数
	$ACTIVE_ACTIVATE	 							 = 2;									// アクティベートされている状態を表す定数
	$ACTIVE_ACCEPT		 							 = 4;									// 許可されている状態を表す定数
	$ACTIVE_DENY		 							 = 8;									// 拒否されている状態を表す定数
	$ACTIVE_ALL	 									 = 15;

	$template_path                                   = "./template/pc/";
	$system_path                          	         = "./custom/system/";
	$model_path 	                                 = "./custom/model/";
	$logic_path 	                                 = "./custom/logic/";
	$view_path                          	         = "./custom/view/";
	$page_path										 = "./file/page/";
	$lst_path										 = "./db/lst/";
	$tdb_path										 = "./db/tdb/";
	$index_path										 = "./db/indexs/";
	$template_tdb_path								 = "./db/template/";
	$sqlite_db_path									 = "./db/";

	$FORM_TAG_DRAW_FLAG	 							 = 'variable';					//  buffer/variable

	$DB_LOG_FILE									 = "./logs/dbaccess.log";				// データベースアクセスログファイル
	$COOKIE_PATH 									 = '/';

	$IMAGE_NOT_FOUND								= '<span>No Image</span>';
	$IMAGE_NOT_FOUND_SRC							= './common/img/noimage.gif';

	$CSS_PATH										= './common/css/';

	$terminal_type = isset($terminal_type)?$terminal_type:0;
	$sp_mode = false;
	$sid = "";

	$MAX_FILE_SIZE = 1048576;

	$DELETE_FILE_AUTO = false;
	$DELETE_FILE_TYPES = Array('image','file');
//	$DELETE_TABLE_TYPES = Array('image','cUser','item');

	$ITEM_STATUS_CLOSE							= 0;
	$ITEM_STATUS_COPEN							= 1;
	$ITEM_STATUS_AOPEN							= 2;
	$ITEM_STATUS_ALL_OPEN						= 3;

	//文字コード
	$SYSTEM_CHARACODE = "UTF-8";
	$OUTPUT_CHARACODE = $SYSTEM_CHARACODE;
	$LONG_OUTPUT_CHARACODE = $SYSTEM_CHARACODE;

	//一部静的URLで出力するかどうかのフラグ true = 出力する
	$STATIC_URL_FLG = false;

/*************************************************************
 ** アクセスコントロール（種別・権限別デザイン処理）用 定義 **
 *************************************************************/


/***************************
 ** LINK&JS IMPORT関連 **
 ****************************/

	/* 管理用 */
	$css_file_paths['admin']['import'] = './common/css/system/admin.css';
	$css_file_paths['admin']['manage'] = './common/css/system/manage.css';
	$css_file_paths['cUser']['import'] = './common/css/system/admin.css';
	$css_file_paths['cUser']['cuser'] = './common/css/system/cuser.css';
	$css_file_paths['nUser']['nuser'] = './common/css/nuser.css';
	//$css_file_paths['cUser']['manage'] = './common/css/system/manage.css';
	//$css_file_paths['nUser']['import'] = './common/css/system/admin.css';
	//$css_file_paths['nUser']['manage'] = './common/css/system/manage.css';

	/* フロント用 */
	$css_file_paths['nUser']['import'] = './common/css/style.css';
	$css_file_paths['cUser']['import'] = './common/css/style.css';
	$css_file_paths['nobody']['import'] = './common/css/style.css';
	$css_file_paths['nUser']['quickjob'] = './common/css/quickjob.css';
	$css_file_paths['nobody']['quickjob'] = './common/css/quickjob.css';

	/* スマホ用 */
	$sp_css_file_paths['nUser']['import'] = './common/css/sp/style.css';
	$sp_css_file_paths['nobody']['import'] = './common/css/sp/style.css';
	$sp_css_file_paths['nUser']['quickjob'] = './common/css/sp/quickjob.css';
	$sp_css_file_paths['nobody']['quickjob'] = './common/css/sp/quickjob.css';

	$css_file_paths['admin']['codemirror']="./common/js/codemirror/lib/codemirror.css";
	$js_file_paths['admin']['codemirror']="./common/js/codemirror/lib/codemirror.js";
	$js_file_paths['admin']['codemirror_css']="./common/js/codemirror/mode/css/css.js";
	$js_file_paths['admin']['codemirror_javascript']="./common/js/codemirror/mode/javascript/javascript.js";
	$js_file_paths['admin']['codemirror_xml']="./common/js/codemirror/mode/xml/xml.js";
	$js_file_paths['admin']['codemirror_htmlmixed']="./common/js/codemirror/mode/htmlmixed/htmlmixed.js";

	$js_file_paths['all']['jquery'] = './common/js/jquery.js';
	$js_file_paths['all']['promise-polyfills'] = './common/js/promise.js';
	$js_file_paths['all']['selectboxes'] = './common/js/jquery.selectboxes.pack.js';
	$css_file_paths['all']['lightbox2'] = './common/css/lightbox.css';
	$js_file_paths['all']['lightbox2'] = './common/js/lightbox.js';
	$js_file_paths['all']['common'] = './common/js/common.js';
	$js_file_paths['all']['adds'] = './common/js/adds.js';
	$js_file_paths['all']['items'] = './common/js/statusChange.js';
	$js_file_paths['all']['upload'] = './common/js/async_upload.js';

	$js_file_paths['all']['resume'] = './common/js/resume/resume.js';
	$js_file_paths['all']['cookie'] = './common/js/module/cookie/jquery.cookie.js';
	$js_file_paths['all']['checkditems'] = './common/js/checkedItems.js';
	$js_file_paths['all']['message'] = './common/js/message.js';
	$js_file_paths['cUser']['entry'] = './common/js/entry.js';
	$js_file_paths['admin']['entry'] = './common/js/entry.js';
	$js_file_paths['admin']['conf'] = './common/js/conf.js';
	$js_file_paths['admin']['manage'] = './common/js/manage.js';
	$js_file_paths['admin']['admin']	 = './common/js/admin.js';
	$js_file_paths['all']['assist']	 = './common/js/assist.js';
	$js_file_paths['all']['job']	 = './common/js/job.js';
	//$js_file_paths['cUser']['manage'] = './common/js/manage.js';

	$js_file_paths['admin']['page']		 = './common/js/page.js';

	$js_file_paths['admin']['stripe']		 = './common/js/table_stripe.js';
	$js_file_paths['cUser']['stripe']		 = './common/js/table_stripe.js';
	$js_file_paths['nUser']['stripe']		 = './common/js/table_stripe.js';
	$js_file_paths['nobody']['tile']		 = './common/js/jquery.tile.min.js';
	$js_file_paths['nUser']['tile']		 = './common/js/jquery.tile.min.js';

	$js_file_paths['nobody']['map']	 = './common/js/index_map.js';
	$js_file_paths['nUser']['map']	 = './common/js/index_map.js';



	$sp_js_file_paths['all']['jquery'] = './common/js/jquery.js';
	$sp_js_file_paths['all']['promise-polyfills'] = './common/js/promise.js';

	$sp_js_file_paths['all']['selectboxes'] = './common/js/jquery.selectboxes.pack.js';
	$sp_js_file_paths['all']['common'] = './common/js/common.js';
	$sp_js_file_paths['all']['adds'] = './common/js/adds.js';
	$sp_js_file_paths['all']['items'] = './common/js/statusChange.js';
	$sp_js_file_paths['all']['resume'] = './common/js/resume/resume.js';
	$sp_js_file_paths['all']['job']	 = './common/js/job.js';
	$sp_js_file_paths['all']['cookie'] = './common/js/module/cookie/jquery.cookie.js';

	$sp_js_file_paths['admin']['page']		 = './common/js/page.js';

	$sp_js_file_paths['nobody']['tab']		 = './common/js/tab.js';
	$sp_js_file_paths['nUser']['tab']		 = './common/js/tab.js';

	$sp_js_file_paths['nobody']['flick']		 = './common/js/jquery.flicksimple.js';
	$sp_js_file_paths['nUser']['flick']		 = './common/js/jquery.flicksimple.js';

	$sp_js_file_paths['all']['message'] = './common/js/message.js';
	$sp_js_file_paths['all']['assist']	 = './common/js/assist.js';

	$js_file_paths['admin']['steps']='./common/js/steps/jquery.steps.min.js';
	$css_file_paths['admin']['steps_css']='./common/js/steps/jquery.steps.css';
	//$head_link_object['all']['dummy']
