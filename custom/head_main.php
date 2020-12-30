<?PHP

	$mobile_flag = false;
	$sp_flag = true;
	$charcode_flag = true;
	$euc_garble = false;

	include_once "./include/base/QueryParser.php";

	include_once "./custom/extends/debugConf.php";
	include_once './include/extends/CodeScheduler.php';

	include_once "./custom/conf.php";
	include_once "./custom/extends/initConf.php";
	include_once "./custom/extends/mobileConf.php";

	$magic_quotes_gpc = ini_get('magic_quotes_gpc');
	date_default_timezone_set( 'Asia/Tokyo' );


	if( $charcode_flag )
	{
		$TRUTH_INTERNAL_ENCODING = mb_internal_encoding();
		ini_set("output_buffering","Off"); // 出力バッファリングを指定します
		ini_set("default_charset",$LONG_OUTPUT_CHARACODE); // デフォルトの文字コードを指定します
		ini_set("extension","php_mbstring.dll"); // マルチバイト文字列を有効にします。
		ini_set("mbstring.language","uni"); // デフォルトをUnicodeに設定します。
		ini_set("mbstring.internal_encoding",$SYSTEM_CHARACODE); // 内部文字エンコーディングを設定します。
		ini_set("mbstring.http_input","auto"); // HTTP入力文字エンコーディング変換をautoに設定します。
		ini_set("mbstring.encoding_translation","Off");//自動エンコーディングを無効に出来る場合は無効にする
		ini_set("mbstring.detect_order","auto"); // 文字コード検出をautoに設定します。
		ini_set("mbstring.substitute_character","none"); // 無効な文字を出力しない。
		mb_internal_encoding($SYSTEM_CHARACODE);

		// ケータイの絵文字が表示されない場合は以下の2行をコメントアウト
		ini_set("mbstring.http_output",$OUTPUT_CHARACODE); // HTTP出力文字エンコーディング変換を設定します。
		mb_http_output($OUTPUT_CHARACODE);
	}

	include_once "./include/info/ActivateInfo.php";
	include_once "./include/info/DBLogInfo.php";
	include_once "./include/info/ModuleInfo.php";
	include_once "./include/info/PackageInfo.php";
	include_once "./include/info/SystemInfo.php";
	include_once "./include/info/TableInfo.php";
	include_once "./include/info/TemplateInfo.php";
	include_once "./include/info/UserInfo.php";
	include_once "./include/Weave.php";

	include_once "./custom/extends/conf.php";
	include_once "./custom/global.php";

	if( $CONFIG_SSL_ENABLE && $CONFIG_SSL_ALWAYS_HTTPS )
		{ ini_set( 'session.cookie_secure' , 1 ); }

	if( !isset($CRON_SESSION_FLAG) || !$CRON_SESSION_FLAG ){
		if( $terminal_type ) //携帯の場合
		{
			MobileUtil::setSessionID();
			session_start();
			MobileUtil::reloadSID();
		}
		else //携帯以外の端末の場合
		{
			session_start();
			if( $_GET[ ini_get( 'session.name' ) ] == session_id() || $_POST[ ini_get( 'session.name' ) ] == session_id() ) //GET/POSTからの設定は一度強制変更する
				{ session_regenerate_id(); }
		}
	}

	if ($magic_quotes_gpc) {
		$_GET = stripslashes_deep($_GET);
		$_POST = stripslashes_deep($_POST);
		$_COOKIE = stripslashes_deep($_COOKIE);
	}

//euc-jpで$charcode_flag=trueでpost,getが文字化ける場合に有効にする
	if( $euc_garble ){
		mb_convert_variables($SYSTEM_CHARACODE,$TRUTH_INTERNAL_ENCODING, $_POST);
		mb_convert_variables($SYSTEM_CHARACODE,$TRUTH_INTERNAL_ENCODING, $_GET);
	}

	include_once './include/base/apiClass.php';
	include_once './include/base/ccProcBase.php';
	include_once "./include/ccProc.php";
	include_once "./include/IncludeObject.php";
	include_once "./include/GUIManager.php";
	include_once "./include/Search.php";
	include_once "./include/Mail.php";
	include_once "./include/Template.php";
	include_once "./include/Command.php";
	include_once "./include/GMList.php";
	include_once "./custom/checkData.php";
	include_once "./custom/extension.php";
	include_once "./module/module.inc";
	include_once $system_path."System.php";

	include_once "./custom/extends/modelConf.php";
	include_once "./custom/extends/logicConf.php";
	include_once "./custom/extends/viewConf.php";

	include_once "./custom/extends/feedConf.php";
	include_once './include/templateCache.php';

	TemplateCache::Initialize();


	CleanGlobal::action();

	// データベースロード
	$gm		 = SystemUtil::getGM();

	//sytem data set
	$tdb = $gm['system']->getDB();
	$trec = $tdb->getRecord( $tdb->getTable() , 0 );

	//global変数の定義
	$HOME				= $tdb->getData( $trec , 'home' );
	$MAILSEND_ADDRES	= $tdb->getData( $trec , 'mail_address' );
	$MAILSEND_NAMES 	= $tdb->getData( $trec , 'mail_name' );
	$LOGIN_ID_MANAGE	= $tdb->getData( $trec , 'login_id_manage' );
	$css_name			= $tdb->getData( $trec , 'main_css' );

	$path   = SystemInfo::GetRealBaseURL();

	// ユーザIDを特定
	switch( $LOGIN_ID_MANAGE )
	{
		case 'SESSION':
			if(!$_SESSION[ $SESSION_NAME ]){
				if(AutoLoginLogic::checkValidate()){
					$id = AutoLoginLogic::getId();
					SystemUtil::login($id, AutoLoginLogic::getUserType($id));
				}else{
					AutoLoginLogic::deleteKey();
				}
			}
			$LOGIN_ID	 = $_SESSION[ $SESSION_NAME ];
			if( $path == $_SESSION[ $SESSION_PATH_NAME ] ){
				$LOGIN_ID						= $_SESSION[ $SESSION_NAME ];
				$LOGIN_TYPE                     = $_SESSION[ $SESSION_TYPE ];
			}
			break;
		case 'COOKIE':
		default:
			if(!$_COOKIE[ $COOKIE_NAME ]){
				if(AutoLoginLogic::checkValidate()){
					$_COOKIE[ $COOKIE_NAME ] = AutoLoginLogic::getId();
				}else{
					AutoLoginLogic::deleteKey();
				}
			}
			$LOGIN_ID	 = $_COOKIE[ $COOKIE_NAME ];

			if( $path == $_COOKIE[ $COOKIE_PATH_NAME ] ){
				$LOGIN_ID					  = $_COOKIE[ $COOKIE_NAME ];
				$LOGIN_TYPE                   = $_COOKIE[ $COOKIE_TYPE ];
			}
			break;
	}

	//LOGIN_IDが不正な値な場合
/*	if( preg_match( '/\W/' , $LOGIN_ID ) ){
		throw new InternalErrorException('$LOGIN_ID is illegal.');
	}
	*/
	// ログインしているユーザのユーザタイプ名とその権限の取得
	$loginUserType = $NOT_LOGIN_USER_TYPE;
	$loginUserRank = $ACTIVE_ACTIVATE;

	if(  isset( $LOGIN_ID ) && isset($LOGIN_TYPE) &&  $LOGIN_ID != '' )
	{
		$db		 = $gm[ $LOGIN_TYPE ]->getDB();
		$table	 = $db->searchTable( $db->getTable(), 'id', '=', $LOGIN_ID );
		if( $rec = $db->getFirstRecord($table) )
		{
			$loginUserType	 = $LOGIN_TYPE;
			$loginUserRank	 = $db->getData( $rec, 'activate' );
			$loginUserRec	 = $rec;

			mod_super_user::sudo( $db, $rec );
		}
	}

	// ログイン前ページ記憶情報をリフレッシュする
	$isNotRefresh = in_array( $controllerName, array( 'Activate', 'Login', 'Quick', 'Reminder') ); // リフレッシュ除外対象

	if( !$isNotRefresh )
	{
		unset($_SESSION[ 'previous_page' ]);
		unset($_SESSION[ 'previous_page_admin' ]);
	}

	viewMode::checkView();

	SSLUtil::ssl_check();

	if( TemplateCache::LoadCache() )
		{ exit(); }

	CodeScheduler::Run( 'BeforeMain' );
