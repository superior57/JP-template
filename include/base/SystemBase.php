<?php

/**
 * システムコールクラス
 *
 * @author 丹羽一智
 * @version 1.0.0
 *
 */
class SystemBase extends command_base
{
	/**********************************************************************************************************
	 * 汎用システム用メソッド
	 **********************************************************************************************************/

	// アップロードファイルの格納フォルダ指定
	// extで拡張子（jpg等）catで種類（image等）、その他timeformatを指定可能。複数階層の場合は/で区切る。
	var $fileDir = 'cat/Ym'; // 記述例) cat/ext/Y/md -> 格納フォルダ image/jpg/2009/1225

	//getHeadとgetFootの呼び出し管理
	static $head = false;
	static $foot = false;

	static $title = "";
	static $description = "";
	static $keywords = "";
	static $robots = "";

	static $ogTitle       = "";
	static $ogType        = "";
	static $ogDescription = "";
	static $ogURL         = "";
	static $ogImage       = "";

	static $CallMode = 'normal';

	static $ValidateColumnCache = Array();

	/////////////////////////////////////////////////////////////////////////////////////////////////////////
	// ヘッダー関係
	/////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * タイトルを出力。
	 * 特定の条件で出力内容を変更したい場合は$bufferにその内容を指定してください
	 *
	 * @param gm GUIManagerオブジェクトです。
	 * @param rec 登録情報のレコードデータです。
	 * @param args コマンドコメント引数配列です。　第一引数にIDを渡します。 第二引数にリンクするかを真偽値で渡します。
	 */
	function drawTitle( &$gm, $rec, $args )
	{
		$buffer = SystemUtil::getSystemData('site_title');
		if( strpos($_SERVER['REQUEST_URI'], "info.php") !== false)
		{// 詳細ページの場合

		}
		else if( strpos($_SERVER['REQUEST_URI'], "search.php") !== false)
		{// 検索ページの場合

		}

		$this->addBuffer( $buffer );
	}


	/**
	 * 説明を出力。
	 * 特定の条件で出力内容を変更したい場合は$bufferにその内容を指定してください
	 *
	 * @param gm GUIManagerオブジェクトです。
	 * @param rec 登録情報のレコードデータです。
	 * @param args コマンドコメント引数配列です。　第一引数にIDを渡します。 第二引数にリンクするかを真偽値で渡します。
	 */
	function drawDescription( &$gm, $rec, $args )
	{
		$buffer = SystemUtil::getSystemData('description');
		if( strpos($_SERVER['REQUEST_URI'], "info.php") !== false)
		{// 詳細ページの場合

		}
		else if( strpos($_SERVER['REQUEST_URI'], "search.php") !== false)
		{// 検索ページの場合

		}

		$this->addBuffer( $buffer );
	}


	/**
	 * キーワードを出力。
	 * 特定の条件で出力内容を変更したい場合は$bufferにその内容を指定してください
	 *
	 * @param gm GUIManagerオブジェクトです。
	 * @param rec 登録情報のレコードデータです。
	 * @param args コマンドコメント引数配列です。　第一引数にIDを渡します。 第二引数にリンクするかを真偽値で渡します。
	 */
	function drawKeywords( &$gm, $rec, $args )
	{
		$buffer = SystemUtil::getSystemData('keywords');
		if( strpos($_SERVER['REQUEST_URI'], "info.php") !== false)
		{// 詳細ページの場合

		}
		else if( strpos($_SERVER['REQUEST_URI'], "search.php") !== false)
		{// 検索ページの場合

		}

		$this->addBuffer( $buffer );
	}


	/**
	 * canonicalタグを出力
	 * 特定の条件で出力内容を変更したい場合は$bufferにその内容を指定してください
	 *
	 * @param gm GUIManagerオブジェクトです。
	 * @param rec 登録情報のレコードデータです。
	 * @param args コマンドコメント引数配列です。　第一引数にIDを渡します。 第二引数にリンクするかを真偽値で渡します。
	 */
	function drawCanonical( &$gm, $rec, $args )
	{
		global $HOME;

		$buffer = "";
		if( strpos($_SERVER['REQUEST_URI'], "index.php") !== false)
		{
			switch($_GET["app_controller"])
			{
			case 'info':
				$buffer = $HOME.'index.php?app_controller='.$_GET["app_controller"].'&type='.$_GET["type"].'&id='.$_GET['id'];
				break;
			case 'register':
				$buffer = $HOME.'index.php?app_controller='.$_GET["app_controller"].'&type='.$_GET["type"];
				break;
			case 'search':
				$param = SystemUtil::arrayOmit($_GET);
				$buffer = $HOME."index.php?".SystemUtil::getUrlParm($param);
				break;
			}
		}
		else if( strpos($_SERVER['REQUEST_URI'], "info.php") !== false)
		{// 詳細ページの場合
			$buffer = $HOME.'info.php?type='.$_GET["type"].'&id='.$_GET['id'];
		}
		else if( strpos($_SERVER['REQUEST_URI'], "regist.php") !== false)
		{// 検索ページの場合
			$buffer = $HOME.'regist.php?type='.$_GET["type"];
		}
		else if( strpos($_SERVER['REQUEST_URI'], "search.php") !== false)
		{// 検索ページの場合
			$param = SystemUtil::arrayOmit($_GET);
			$buffer = $HOME."search.php?".SystemUtil::getUrlParm($param);
		}

		if(strlen($buffer) > 0) { $this->addBuffer( '<link rel="canonical" href="'.$buffer.'">' ); }
	}
	/////////////////////////////////////////////////////////////////////////////////////////////////////////
	// 登録関係
	/////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * 登録内容確認。
	 *
	 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
	 * @param edit 編集なのか、新規追加なのかを真偽値で渡す。
	 * @return エラーがあるかを真偽値で渡す。
	 */
	function registCheck( &$gm, $edit, $loginUserType, $loginUserRank )
	{
		// チェック処理
		self::$checkData->generalCheck($edit);
		$data = self::$checkData->getData();

		// エラー内容取得
		return self::$checkData->getCheck();
	}

	/**
	 * 複製登録条件確認。
	 *
	 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
	 * @param edit 編集なのか、新規追加なのかを真偽値で渡す。
	 * @return 複製登録が可能かを真偽値で返す。
	 */
	function copyCheck( &$gm, $loginUserType, $loginUserRank )
	{
		// 管理者は全て無条件に許可
		if( 'admin' == $loginUserType )
		return true;

		switch( $_GET[ 'type' ] )
		{
		default :
			return false;
		}
	}

	/**
	 * 削除内容確認。
	 *
	 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
	 * @param rec フォームのからの入力データを反映したレコードデータ。
	 * @return エラーがあるかを真偽値で渡す。
	 */
	function deleteCheck(&$gm, &$rec, $loginUserType, $loginUserRank )
	{
		self::$checkData->deleteCheck();

		return self::$checkData->getCheck();
	}

	/**
	 *
	 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
	 * @param rec フォームのからの入力データを反映したレコードデータ。
	 * @param edit 編集なのか、新規追加なのかを真偽値で渡す。
	 * @return エラーがあるかを真偽値で渡す。
	 */
	function registCompCheck( &$gm, &$rec, $loginUserType, $loginUserRank ,$edit=false)
	{
		// チェック処理
		$check			 = true;
		$db	 = $gm[ $_GET['type'] ]->getDB();

		if(!$edit){
			//重複登録チェック
			$table	 = $db->searchTable(  $db->getTable(), 'id', '=', $db->getData( $rec, 'id' )  );
			if($db->existsRow($table)){
				self::$checkData->addError('duplication_id');
			}
		}else{
			if( $_POST['id'] != $_GET['id'] ){
				return false;
			}
		}

		if( $edit )
		{
			//Const/AdminData/MailDupのチェック
			$options = $gm[ $_GET[ 'type' ] ]->colEdit;

			foreach( $options as $column => $validates )
			{
				$validates = explode( '/' , $validates );

				if( in_array( 'Const' , $validates ) )
					self::$checkData->checkConst( $column , null );

				if( in_array( 'AdminData' , $validates ) )
					self::$checkData->checkAdminData( $column , null );

				if( in_array( 'MailDup' , $validates ) )
					self::$checkData->checkMailDup( $column , null );
			}
		}

		// 固有のチェック処理
/*		switch( $_GET['type'] )
		{
		case 'xxxx':
			break;
		}
*/
		return self::$checkData->getCheck();
	}

	/**
	 * 登録前段階処理。
	 * フォーム入力以外の方法でデータを登録する場合は、ここでレコードに値を代入します。
	 *
	 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
	 * @param rec フォームのからの入力データを反映したレコードデータ。
	 */
	function registProc( &$gm, &$rec, $loginUserType, $loginUserRank ,$check = false)
	{
		global $THIS_TABLE_OWNER_COLUM;
		global $LOGIN_ID;

		$db	 = $gm[ $_GET['type'] ]->getDB();

		// IDと登録時間を記録。
		$db->setData( $rec, 'id',	  SystemUtil::getNewId( $db, $_GET['type']) );
		$db->setData( $rec, 'regist', time() );

		if( in_array( 'update_time' , $db->colName ) )
			{ $db->setData( $rec, 'update_time', time() ); }

		if( isset( $THIS_TABLE_OWNER_COLUM[ $_GET[ 'type' ] ] ) )
		{
			foreach( $THIS_TABLE_OWNER_COLUM[ $_GET[ 'type' ] ] as $type => $column )
			{
				if( $loginUserType != $type )
					{ continue; }

				if( in_array( $column , $db->colName ) )
					{ $db->setData( $rec, $column, $LOGIN_ID ); }
			}
		}

		// 固有のチェック処理
/*		switch( $_GET['type'] )
		{
		case 'xxxx':
			break;
		}
*/

		if(!$check) { $this->uplodeComp($gm,$db,$rec); } // ファイルのアップロード完了処理
	}

	/**
	 * 登録処理完了処理。
	 * 登録完了時にメールで内容を通知したい場合などに用います。
	 *
	 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
	 * @param rec レコードデータ。
	 */
	function registComp( &$gm, &$rec, $loginUserType, $loginUserRank )
	{
	}



	/////////////////////////////////////////////////////////////////////////////////////////////////////////
	// 編集関係
	/////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * 編集前段階処理。
	 * フォーム入力以外の方法でデータを登録する場合は、ここでレコードに値を代入します。
	 *
	 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
	 * @param rec フォームのからの入力データを反映したレコードデータ。
	 */
	function editProc( &$gm, &$rec, $loginUserType, $loginUserRank ,$check = false)
	{
		$db	 = $gm[ $_GET['type'] ]->getDB();

		if( in_array( 'edit' , $db->colName ) )
			{ $db->setData( $rec, 'edit', time() ); }

		if(!$check) { $this->uplodeComp($gm,$db,$rec); } // ファイルのアップロード完了処理
	}

	/**
	 * 編集完了処理。
	 * フォーム入力以外の方法でデータを登録する場合は、ここでレコードに値を代入します。
	 *
	 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
	 * @param rec フォームのからの入力データを反映したレコードデータ。
	 */
	function editComp( &$gm, &$rec, &$old_rec, $loginUserType, $loginUserRank )
	{
		//$this->doFileDelete( $gm, $rec, $old_rec );
	}


	/////////////////////////////////////////////////////////////////////////////////////////////////////////
	// 削除関係
	/////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * 削除処理。
	 * 削除を実行する前に実行したい処理があれば、ここに記述します。
	 * 例えばユーザデータを削除する際にユーザデータに紐付けられたデータを削除する際などに有効です。
	 *
	 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
	 * @param rec フォームのからの入力データを反映したレコードデータ。
	 */
	function deleteProc( &$gm, &$rec, $loginUserType, $loginUserRank )
	{
		$db		 = $gm[ $_GET['type'] ]->getDB();

		// 固有のチェック処理
/*		switch( $_GET['type'] )
		{
		case 'xxxx':
			break;
		}
*/

		// 削除実行処理
		switch( $_GET['type'] )
		{
		default:
			// レコードを削除します。
			$db->deleteRecord( $rec );
			break;
		}

	}



	/**
	 * 削除完了処理。
	 * 登録削除完了時に実行したい処理があればココに記述します。
	 * 削除完了メールを送信したい場合などに利用します。
	 *
	 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
	 * @param rec フォームのからの入力データを反映したレコードデータ。
	 */
	function deleteComp( &$gm, &$rec, $loginUserType, $loginUserRank )
	{
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $LOGIN_ID;
		global $DELETE_FILE_AUTO;
		// **************************************************************************************

		$db = $gm[$_GET['type']]->getDB();
		if( $_GET['type'] == $loginUserType && $LOGIN_ID == $db->getData( $rec , 'id' ) ){
			SystemUtil::logout($loginUserType);
		}

		if( $DELETE_FILE_AUTO ){
			$this->doFileDelete( $gm, $rec );
		}
	}

	function FeedProc( $table = null )
	{
		global $CONF_FEED_ENABLE;
		global $CONF_FEED_TABLES;
		global $CONF_FEED_MAX_ROW;
		global $CONF_FEED_OUTPUT_DIR;
		global $FileBase;

		if( $CONF_FEED_ENABLE && in_array( $_GET[ 'type' ] , $CONF_FEED_TABLES ) )
		{
			$gm = GMList::getGM( $_GET[ 'type' ] );
			$db = $gm->getDB();

			if( !$table )
			{
				$table = $db->getTable();
				$table = $db->sortTable( $table , 'shadow_id' , 'desc' );
				$table = $db->limitOffset( $table , 0 , $CONF_FEED_MAX_ROW );
			}

			$row = $db->getRow( $table );

			if( $CONF_FEED_MAX_ROW < $row )
			{
				$table = $db->limitOffset( $table , 0 , $CONF_FEED_MAX_ROW );
				$row   = $db->getRow( $table );
			}

			foreach( Array( Array( 'label' => 'FEED_RSS_DESIGN' , 'name' => '_rss.xml' ) , Array( 'label' => 'FEED_ATOM_DESIGN' , 'name' => '_atom.xml' ) ) as $config )
			{
				$template = Template::getTemplate( 'nobody' , 1 , $_GET[ 'type' ] , $config[ 'label' ] );

				if( !$template )
					{ continue; }

				$fp = fopen( $CONF_FEED_OUTPUT_DIR . $_GET[ 'type' ] . $config[ 'name' ] , 'wb' );

				if( $fp )
				{
					fputs( $fp , $gm->getString( $template , null , 'head' ) );

					for( $i = 0 ; $row > $i ; ++$i )
					{
						$rec     = $db->getRecord( $table , $i );
						fputs( $fp , $gm->getString( $template , $rec , 'list' ) );
					}

					fputs( $fp , $gm->getString( $template , null , 'foot' ) );

					fclose( $fp );
				}

				$file = $CONF_FEED_OUTPUT_DIR . $_GET[ 'type' ] . $config[ 'name' ];
				$FileBase->upload($file,$file);
			}
		}
	}

	/////////////////////////////////////////////////////////////////////////////////////////////////////////
	// 検索関係
	/////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * 検索前処理。
	 * 検索条件等を検索実行前に変更したい場合に利用します。
	 *
	 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
	 * @param sr 検索パラメータがセット済みなSearchオブジェクト
	 */
	function searchResultProc( &$gm, &$sr, $loginUserType, $loginUserRank )
	{
		$type = SearchTableStack::getType();
		$db = $gm[ $type ]->getDB();

		// 固有のチェック処理
/*		switch( $_GET['type'] )
		{
		case 'xxxx':
			break;
		}
*/
	}

	/**
	 * 検索処理。
	 * フォーム入力以外の方法で検索条件を設定したい場合に利用します。
	 *
	 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
	 * @param table フォームのからの入力内容に一致するレコードを格納したテーブルデータ。
	 */
	function searchProc( &$gm, &$table, $loginUserType, $loginUserRank )
	{
		$type = SearchTableStack::getType();

		$db = $gm[ $type ]->getDB();

		// 固有のチェック処理
/*		switch( $_GET['type'] )
		{
		case 'xxxx':
			break;
		}
*/
	}


	/////////////////////////////////////////////////////////////////////////////////////////////////////////
	// 詳細情報関係
	/////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * 詳細情報が閲覧されたときに表示して良い情報かを返すメソッド。
	 * activateカラムや公開可否フラグ、registやupdate等による表示期間の設定、アクセス権限によるフィルタなどを行います。
	 *
	 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
	 * @param rec アクセスされたレコードデータ。
	 * @return 表示して良いかどうかを真偽値で渡す。
	 */
	function infoCheck( &$gm, &$rec, $loginUserType, $loginUserRank )
	{
		$db	     = $gm[ $_GET['type'] ]->getDB();
		$isOwner = false;

		if( WS_SYSTEM_AUTO_INFO_CHECK_SEARCHABLE ) //検索可能性チェックが有効な場合
		{
			if( 'admin' != $loginUserType ) //管理者以外のユーザーの場合
				{ $isOwner = SystemUtil::checkTableOwner( $_GET[ 'type' ] , $db , $rec ); }

			if( !$isOwner ) //現在のユーザーがこのデータのオーナーではない場合
			{
				$table = SystemUtil::getSearchResult( Array( 'type' => $_GET[ 'type' ] , 'id' => $_GET[ 'id' ] , 'id_PAL' => Array( 'match comp' ) ) );
				$table = $db->limitOffset( $table , 0 , 1 );

				if( !$db->getRow( $table ) )
					{ return false; }
			}
		}

		// 固有のチェック処理
/*		switch( $_GET['type'] )
		{
		case 'xxxx':
			break;
		}
*/
		return true;
	}

	/**
	 * 詳細情報が閲覧されたときに呼び出される処理。
	 * 情報に対するアクセスログを取りたいときなどに有用です。
	 *
	 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
	 * @param rec アクセスされたレコードデータ。
	 */
	function doInfo( &$gm, &$rec, $loginUserType, $loginUserRank )
	{
	}

	/**
	 * 詳細情報前処理。
	 * 簡易情報変更で利用
	 *
	 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
	 * @param rec アクセスされたレコードデータ。
	 */
	function infoProc( &$gm, &$rec, $loginUserType, $loginUserRank )
	{

		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $PROGRESS_BEGIN;
		// **************************************************************************************

		// 簡易情報変更（情報ページからの内容変更処理）
		if(  isset( $_POST['post'] ) )
		{
			// 固有のチェック処理
/*			switch( $_GET['type'] )
			{
			case 'xxxx':
				break;
			}
*/
		}
	}



	/////////////////////////////////////////////////////////////////////////////////////////////////////////
	//   アクティベート関係
	/////////////////////////////////////////////////////////////////////////////////////////////////////////

	//activate判定及びアクティベート完了処理
	function activateAction( &$gm, &$rec, $loginUserType, $loginUserRank )
	{
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $ACTIVE_NONE;
		global $ACTIVE_ACTIVATE;
		// **************************************************************************************

		$db = $gm[ $_GET['type'] ]->getDB();

		if(  $db->getData( $rec, 'activate' ) == $ACTIVE_NONE  )
		{
			$db->setData( $rec, 'activate', $ACTIVE_ACTIVATE );
			$db->updateRecord( $rec );

			MailLogic::userRegistComp( $rec, $_GET['type'] );
		}

		return true;
	}


	/////////////////////////////////////////////////////////////////////////////////////////////////////////
	//   ログイン関係
	/////////////////////////////////////////////////////////////////////////////////////////////////////////

	//ログアウト中間処理
	//返り値をfalseにするとログアウトが中止される
	function logoutProc( $loginUserType ){

		if( $_SESSION['ADMIN_MODE'] ){
			unset($_SESSION['ADMIN_MODE']);
		}

		AutoLoginLogic::deleteKey();
		return true;
	}

	//ログイン中間処理
	//返り値をfalseにするとログインが中止される
	function loginProc( $check , &$loginType , &$id ){
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $gm;
		global $LOGIN_ID;
		global $loginUserType;
		global $REMEMBER_COOKIE;
		// **************************************************************************************

		if( $loginUserType == 'admin' && isset($_GET['type']) ){
			$loginType = $_GET['type'];
			$id	= $_GET['id'];
			$_SESSION['ADMIN_MODE'] = true;
			return true;
		}

		if( $_SESSION['ADMIN_MODE'] ){
			$loginType = 'admin';
			$id	= 'ADMIN';
			unset($_SESSION['ADMIN_MODE']);
			return true;
		}

		//falseをスルー
		if(!$check){return $check;}

		if(($_GET[$REMEMBER_COOKIE] == 1 || $_POST[$REMEMBER_COOKIE] == 1) && $loginType != 'admin'){
			AutoLoginLogic::registKey($id);
		}
		//ログイン対象によって分岐する場合、ここに記述する
/*		switch( $_GET['type'] )
		{
		case 'xxxx':
			break;
		}
*/

		return true;
	}

	/////////////////////////////////////////////////////////////////////////////////////////////////////////
	//   復元関係
	/////////////////////////////////////////////////////////////////////////////////////////////////////////


	/**
	 * 復元チェック処理。
	 * 復元チェック時に実行したい処理があればココに記述します。
	 *
	 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
	 * @param rec フォームのからの入力データを反映したレコードデータ。
	 */
	function restoreCheck( &$gm, &$rec, $loginUserType, $loginUserRank )
	{
		global $THIS_TABLE_IS_USERDATA;
		global $LOGIN_KEY_FORM_NAME;

		if( $THIS_TABLE_IS_USERDATA[ $_GET[ 'type' ] ] )
		{
			$db    = GMList::getDB( $_GET[ 'type' ] );
			$table = $db->getTable();
			$table = $db->searchTable( $table , $LOGIN_KEY_FORM_NAME , '=' , $db->getData( $rec , $LOGIN_KEY_FORM_NAME ) );
			$table = $db->limitOffset( $table , 0 , 1 );

			if( $db->getRow( $table ) )
				{ return false; }
		}

		return true;
	}

	/**
	 * 復元完了処理。
	 * 復元完了時に実行したい処理があればココに記述します。
	 *
	 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
	 * @param rec フォームのからの入力データを反映したレコードデータ。
	 */
	function restoreComp( &$gm, &$rec, &$old_rec, $loginUserType, $loginUserRank )
	{
	}


	/**********************************************************************************************************
	 * 汎用システム描画系用メソッド
	 **********************************************************************************************************/

	/////////////////////////////////////////////////////////////////////////////////////////////////////////
	// 登録関係
	/////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * 登録フォームを描画する。
	 *
	 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
	 * @param loginUserType ログインしているユーザの種別
	 * @param loginUserRank ログインしているユーザの権限
	 */
	function drawRegistForm( &$gm, $rec, $loginUserType, $loginUserRank )
	{
		$this->setErrorMessage($gm[ $_GET['type'] ]);

		switch(  $_GET['type']  )
		{
			default:
				// 汎用処理
				if($gm[$_GET['type']]->maxStep >= 2)
				Template::drawTemplate( $gm[ $_GET['type'] ] , $rec , $loginUserType , $loginUserRank , $_GET['type'] , 'REGIST_FORM_PAGE_DESIGN' . $_POST['step'] , SystemUtil::GetFormTarget( 'registForm' ) );
				else
				Template::drawTemplate( $gm[ $_GET['type'] ] , $rec , $loginUserType , $loginUserRank , $_GET['type'] , 'REGIST_FORM_PAGE_DESIGN' , SystemUtil::GetFormTarget( 'registForm' ) );

		}


	}



	/**
	 * 登録内容確認ページを描画する。
	 *
	 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
	 * @param rec 登録情報を格納したレコードデータ
	 * @param loginUserType ログインしているユーザの種別
	 * @param loginUserRank ログインしているユーザの権限
	 */
	function drawRegistCheck( &$gm, $rec, $loginUserType, $loginUserRank )
	{
		switch(  $_GET['type']  )
		{
			default:
				// 汎用処理
				Template::drawTemplate( $gm[ $_GET['type'] ] , $rec , $loginUserType , $loginUserRank , $_GET['type'] , 'REGIST_CHECK_PAGE_DESIGN' , SystemUtil::GetFormTarget( 'registCheck' ) );
		}
	}



	/**
	 * 登録完了ページを描画する。
	 *
	 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
	 * @param rec 登録情報を格納したレコードデータ
	 * @param loginUserType ログインしているユーザの種別
	 * @param loginUserRank ログインしているユーザの権限
	 */
	function drawRegistComp( &$gm, $rec, $loginUserType, $loginUserRank )
	{
		Template::drawTemplate( $gm[ $_GET['type'] ] , $rec , $loginUserType , $loginUserRank , $_GET['type'] , 'REGIST_COMP_PAGE_DESIGN' );
	}



	/**
	 * 登録失敗画面を描画する。
	 *
	 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
	 * @param loginUserType ログインしているユーザの種別
	 * @param loginUserRank ログインしているユーザの権限
	 */
	function drawRegistFaled( &$gm, $loginUserType, $loginUserRank )
	{
		$this->setErrorMessage($gm[ $_GET['type'] ]);

		Template::drawTemplate( $gm[ $_GET['type'] ] , null ,'' , $loginUserRank , '' , 'REGIST_FALED_DESIGN' );
	}


	/**
	 * 登録件数上限規制画面を描画する。
	 *
	 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
	 * @param loginUserType ログインしているユーザの種別
	 * @param loginUserRank ログインしているユーザの権限
	 */
	function drawRegistMaxCountOver( &$gm, $loginUserType, $loginUserRank )
	{
		$this->setErrorMessage($gm[ $_GET['type'] ]);

		switch(  $_GET['type']  )
		{
			default:
				// 汎用処理
				Template::drawTemplate( $gm[ $_GET['type'] ] , null ,$loginUserType , $loginUserRank , $_GET['type'] , 'REGIST_MAX_COUNT_OVER_DESIGN' );
		}
	}

	/////////////////////////////////////////////////////////////////////////////////////////////////////////
	// 編集関係
	/////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * 編集フォームを描画する。
	 *
	 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
	 * @param rec 編集対象のレコードデータ
	 * @param loginUserType ログインしているユーザの種別
	 * @param loginUserRank ログインしているユーザの権限
	 */
	function drawEditForm( &$gm, &$rec, $loginUserType, $loginUserRank )
	{
		$this->setErrorMessage( $gm[ $_GET['type'] ] );

		switch(  $_GET['type']  )
		{
			default:
				// 汎用処理
				Template::drawTemplate( $gm[ $_GET['type'] ] , $rec , $loginUserType , $loginUserRank , $_GET['type'] , 'EDIT_FORM_PAGE_DESIGN' , SystemUtil::GetFormTarget( 'editForm' ), Template::getOwner() );
		}

	}

	/**
	 * 編集内容確認ページを描画する。
	 *
	 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
	 * @param rec 編集対象のレコードデータ
	 * @param loginUserType ログインしているユーザの種別
	 * @param loginUserRank ログインしているユーザの権限
	 */
	function drawEditCheck( &$gm, &$rec, $loginUserType, $loginUserRank )
	{
		switch(  $_GET['type']  )
		{
			default:
				// 汎用処理
				Template::drawTemplate( $gm[ $_GET['type'] ] , $rec , $loginUserType , $loginUserRank , $_GET['type'] , 'EDIT_CHECK_PAGE_DESIGN' , SystemUtil::GetFormTarget( 'editCheck' ), Template::getOwner() );
		}
	}

	/**
	 * 編集完了ページを描画する。
	 *
	 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
	 * @param rec 編集対象のレコードデータ
	 * @param loginUserType ログインしているユーザの種別
	 * @param loginUserRank ログインしているユーザの権限
	 */
	function drawEditComp( &$gm, &$rec, $loginUserType, $loginUserRank )
	{
		switch(  $_GET['type']  )
		{
			default:
				// 汎用処理
				Template::drawTemplate( $gm[ $_GET['type'] ] , $rec , $loginUserType , $loginUserRank , $_GET['type'] , 'EDIT_COMP_PAGE_DESIGN' , false, Template::getOwner() );
		}
	}

	/**
	 * 編集失敗画面を描画する。
	 *
	 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
	 * @param loginUserType ログインしているユーザの種別
	 * @param loginUserRank ログインしているユーザの権限
	 */
	function drawEditFaled( &$gm, $loginUserType, $loginUserRank )
	{
		header( 'HTTP/1.0 400 Bad Request' );
		Template::drawErrorTemplate();
	}


	/////////////////////////////////////////////////////////////////////////////////////////////////////////
	// 削除関係
	/////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * 削除編集フォームを描画する。
	 *
	 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
	 * @param rec 編集対象のレコードデータ
	 * @param loginUserType ログインしているユーザの種別
	 * @param loginUserRank ログインしているユーザの権限
	 */
	function drawDeleteForm( &$gm, &$rec, $loginUserType, $loginUserRank )
	{
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		// **************************************************************************************

		$this->setErrorMessage($gm[ $_GET['type'] ]);

		switch(  $_GET['type']  )
		{
			default:
				// 汎用処理
				Template::drawTemplate( $gm[ $_GET['type'] ] , $rec , $loginUserType , $loginUserRank , $_GET['type'] , 'DELETE_FORM_PAGE_DESIGN' , SystemUtil::GetFormTarget( 'deleteForm' ), Template::getOwner() );
		}
	}

	/**
	 * 削除確認ページを描画する。
	 *
	 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
	 * @param rec 編集対象のレコードデータ
	 * @param loginUserType ログインしているユーザの種別
	 * @param loginUserRank ログインしているユーザの権限
	 */
	function drawDeleteCheck( &$gm, &$rec, $loginUserType, $loginUserRank )
	{
		switch(  $_GET['type']  )
		{
			default:
				// 汎用処理
				Template::drawTemplate( $gm[ $_GET['type'] ] , $rec , $loginUserType , $loginUserRank , $_GET['type'] , 'DELETE_CHECK_PAGE_DESIGN' , SystemUtil::GetFormTarget( 'deleteCheck' ), Template::getOwner() );
		}
	}

	/**
	 * 削除完了ページを描画する。
	 *
	 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
	 * @param rec 編集対象のレコードデータ
	 * @param loginUserType ログインしているユーザの種別
	 * @param loginUserRank ログインしているユーザの権限
	 */
	function drawDeleteComp( &$gm, &$rec, $loginUserType, $loginUserRank )
	{
		switch(  $_GET['type']  )
		{
			default:
				// 汎用処理
				Template::drawTemplate( $gm[ $_GET['type'] ] , $rec , $loginUserType , $loginUserRank , $_GET['type'] , 'DELETE_COMP_PAGE_DESIGN', false, Template::getOwner() );
				break;
		}
	}


	/**
	 * 削除失敗画面を描画する。
	 *
	 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
	 * @param loginUserType ログインしているユーザの種別
	 * @param loginUserRank ログインしているユーザの権限
	 */
	function drawDeleteFaled( &$gm, $loginUserType, $loginUserRank )
	{
		header( 'HTTP/1.0 400 Bad Request' );
		Template::drawErrorTemplate();
	}

	/////////////////////////////////////////////////////////////////////////////////////////////////////////
	// 復元関係
	/////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * 復元確認ページを描画する。
	 *
	 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
	 * @param rec 編集対象のレコードデータ
	 * @param loginUserType ログインしているユーザの種別
	 * @param loginUserRank ログインしているユーザの権限
	 */
	function drawRestoreCheck( &$gm, &$rec, $loginUserType, $loginUserRank )
	{
		switch(  $_GET['type']  )
		{
			default:
				// 汎用処理
				Template::drawTemplate( $gm[ $_GET['type'] ] , $rec , $loginUserType , $loginUserRank , $_GET['type'] , 'RESTORE_CHECK_PAGE_DESIGN' , SystemUtil::GetFormTarget( 'restoreForm' ) );
		}
	}



	/**
	 * 復元完了ページを描画する。
	 *
	 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
	 * @param rec 編集対象のレコードデータ
	 * @param loginUserType ログインしているユーザの種別
	 * @param loginUserRank ログインしているユーザの権限
	 */
	function drawRestoreComp( &$gm, &$rec, $loginUserType, $loginUserRank )
	{
		switch(  $_GET['type']  ){
			default:
				// 汎用処理
				Template::drawTemplate( $gm[ $_GET['type'] ] , $rec , $loginUserType , $loginUserRank , $_GET['type'] , 'RESTORE_COMP_PAGE_DESIGN'  );
				break;
		}
	}

	/**
	 * 復元画面を描画する。
	 *
	 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
	 * @param loginUserType ログインしているユーザの種別
	 * @param loginUserRank ログインしているユーザの権限
	 */
	function drawRestoreFaled( &$gm, $loginUserType, $loginUserRank )
	{
		header( 'HTTP/1.0 400 Bad Request' );
		Template::drawErrorTemplate();
	}


	/////////////////////////////////////////////////////////////////////////////////////////////////////////
	// 検索関係
	/////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * 検索フォームを描画する。
	 *
	 * @param sr Searchオブジェクト。
	 * @param loginUserType ログインしているユーザの種別
	 * @param loginUserRank ログインしているユーザの権限
	 */
	function drawSearchForm( &$sr, $loginUserType, $loginUserRank )
	{
		$sr->addHiddenForm( 'type', $_GET['type'] );

		switch( $_GET['type'] )
		{
			default:
				$file = Template::getTemplate( $loginUserType , $loginUserRank , $_GET['type'] , self::ModifyTemplateLabel( 'SEARCH_FORM_PAGE_DESIGN' ) );

				if( !is_file( $file ) )
					{ Template::getTemplate( $loginUserType , $loginUserRank , $_GET['type'] , 'SEARCH_FORM_PAGE_DESIGN' ); }

				if( strlen( $file ) )	{ print $sr->getFormString( $file , 'search.php'  ); }
				else
				{
					header( 'HTTP/1.0 400 Bad Request' );
					Template::drawErrorTemplate();
				}
				break;
		}
	}

	function drawSearch( &$gm, &$sr, $table, $loginUserType, $loginUserRank ){
		SearchTableStack::pushStack($table);

		/*		一括メール送信へリダイレクト		*/
		if( isset( $_GET[ 'multimail' ] ) ){
			$db		= $gm[ $_GET[ 'type' ] ]->getDB();
			$row	= $db->getRow( $table );

			for( $i=0 ; $i<$row ; $i++ ){
				$rec	 = $db->getRecord( $table, $i );
				$_GET['pal'][] = $db->getData( $rec, 'id' );
			}
			$_GET['type'] = 'multimail';

			if( is_array( $_GET[ 'pal' ] ) ){
				Header( 'Location: index.php?app_controller=register&type=multimail&pal[]=' . implode( '&pal[]=' , $_GET[ 'pal' ] ) );
			}else{
				Header( 'Location: index.php?app_controller=register&type=multimail' );
			}
		}else{
			$file = Template::getTemplate( $loginUserType , $loginUserRank , $_GET['type'] , self::ModifyTemplateLabel( 'SEARCH_RESULT_DESIGN' ) );

			if( !is_file( $file ) )
				{ $file = Template::getTemplate( $loginUserType , $loginUserRank , $_GET['type'] , 'SEARCH_RESULT_DESIGN' ); }

			if( strlen($file) ){
				$sr->addHiddenForm('type',$_GET['type']);
				print $sr->getFormString( $file , 'search.php' , null , 'v' );
			}else{
				header( 'HTTP/1.0 400 Bad Request' );
				Template::drawErrorTemplate();
			}
		}
	}

	/**
	 * 検索結果、該当なしを描画。
	 *
	 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
	 * @param loginUserType ログインしているユーザの種別
	 * @param loginUserRank ログインしているユーザの権限
	 */
	function drawSearchNotFound( &$gm, $loginUserType, $loginUserRank )
	{
		$file = Template::getTemplate( $loginUserType , $loginUserRank , $_GET['type'] , self::ModifyTemplateLabel( 'SEARCH_NOT_FOUND_DESIGN' ) );

		if( !is_file( $file ) )
			{ $file = Template::getTemplate( $loginUserType , $loginUserRank , $_GET['type'] , 'SEARCH_NOT_FOUND_DESIGN' ); }

		if( strlen($file) ){
			print $gm[ $_GET['type'] ]->getString( $file , null , null );
		}else{
			header( 'HTTP/1.0 400 Bad Request' );
			Template::drawErrorTemplate();
		}
	}

	/**
	 * 検索エラーを描画。
	 *
	 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
	 * @param loginUserType ログインしているユーザの種別
	 * @param loginUserRank ログインしているユーザの権限
	 */
	function drawSearchError( &$gm, $loginUserType, $loginUserRank )
	{
		header( 'HTTP/1.0 400 Bad Request' );
		Template::drawErrorTemplate();
	}


	/////////////////////////////////////////////////////////////////////////////////////////////////////////
	// 詳細ページ関係
	/////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * 詳細情報表示エラーを描画。
	 *
	 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
	 * @param loginUserType ログインしているユーザの種別
	 * @param loginUserRank ログインしているユーザの権限
	 */
	function drawInfoError( &$gm, $loginUserType, $loginUserRank )
	{
		header( 'HTTP/1.0 403 Forbidden' );
		Template::drawErrorTemplate();
	}

	/**
	 * 詳細情報ページを描画する。
	 *
	 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
	 * @param rec 編集対象のレコードデータ
	 * @param loginUserType ログインしているユーザの種別
	 * @param loginUserRank ログインしているユーザの権限
	 */
	function drawInfo( &$gm, &$rec, $loginUserType, $loginUserRank )
	{
		switch(  $_GET['type']  )
		{
			default:
				// 汎用処理
				$file = Template::getTemplate( $loginUserType , $loginUserRank , $_GET['type'] , self::ModifyTemplateLabel( 'INFO_PAGE_DESIGN' ), Template::getOwner() );

				if( !is_file( $file ) )
					{ $file = Template::getTemplate( $loginUserType , $loginUserRank , $_GET['type'] , 'INFO_PAGE_DESIGN' ); }

				print $gm[ $_GET['type'] ]->getFormString( $file , $rec , SystemUtil::GetFormTarget( 'infoPage' ) );
		}
	}

	/**
	 * テンプレートの失敗画面を描画する。
	 *
	 * @param gm templateのGUIManager
	 * @param error_name error名  デザインのパーツ名
	 */
	function getTemplateFaled( $gm, $lavel , $error_name  ){
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $loginUserType;
		global $loginUserRank;
		// **************************************************************************************

		$h = Template::getTemplateString( $gm , null , $loginUserType, $loginUserRank , 'stemplate' , $lavel , null , null , 'head' );
		$h .=Template::getTemplateString( $gm , null , $loginUserType, $loginUserRank , 'stemplate' , $lavel , null , null , $error_name );
		$h .=Template::getTemplateString( $gm , null , $loginUserType, $loginUserRank , 'stemplate' , $lavel , null , null , 'foot' );
		return $h;
	}


	/////////////////////////////////////////////////////////////////////////////////////////////////////////
	//   アクティベート関係
	/////////////////////////////////////////////////////////////////////////////////////////////////////////

	function drawActivateComp( &$gm, &$rec, $loginUserType, $loginUserRank ){
		$gm[ $_GET['type'] ]->draw( Template::getLabelFile('ACTIVATE_DESIGN_HTML'), $rec );
	}

	function drawActivateFaled( &$gm, &$rec, $loginUserType, $loginUserRank ){
		$gm[ $_GET['type'] ]->draw( Template::getLabelFile('ACTIVATE_FALED_DESIGN_HTML'), $rec );
	}

	/////////////////////////////////////////////////////////////////////////////////////////////////////////
	// ファイルアップロード関係
	/////////////////////////////////////////////////////////////////////////////////////////////////////////


	/**
	 * ファイルアップロードが行われた場合の一時処理。
	 *
	 * @param db Databaseオブジェクト
	 * @param rec レコードデータ
	 * @param colname アップロードが行われたカラム
	 * @param file ファイル配列
	 */
	function doFileUpload( &$db, &$rec, $colname, &$file )
	{
		global $FileBase;

		$delete_flag = isset($_POST[ $colname . '_DELETE' ]) && $_POST[ $colname . '_DELETE' ] == "true";
		if( $delete_flag )
		{
			if( isset( $_POST[ $colname.'_filetmp' ] ) ){
				// この時点で filetmp を無効化してしまうと、check から戻った場合に消滅する為、filetmp は残す
				global $gm;
				$gm[ $db->tablePlaneName ]->addHiddenForm( $colname.'_filetmp', $_POST[ $colname.'_filetmp' ] );
			}
		}

		if( !$delete_flag &&  $file[ $colname ]['name'] != "" ){
			$fileName = $this->systemFileUpload($file[ $colname ],$colname);
			if($fileName){ $db->setData( $rec, $colname, $fileName );}
			//TODO: false だった場合の例外処理が必要では？
		}else if( !$delete_flag &&  $_POST[ $colname . '_filetmp' ] != "" && $FileBase->file_exists($_POST[ $colname . '_filetmp' ]) ){
			$db->setData( $rec, $colname, $_POST[ $colname.'_filetmp' ] );
			return;
		}else if( !$delete_flag &&  $_POST[ $colname ] != "" && $FileBase->file_exists($_POST[ $colname ])){
			$db->setData( $rec, $colname, $_POST[ $colname ] );
		}else {
			$multi_colname = rtrim($colname, '0123456789');
			if (isset($file[$multi_colname]) && is_array($file[$multi_colname]['name']) && count($file[$multi_colname]['name'])>0) {
				$keyList = Array('name','type','tmp_name','error','size');
				$topFile = array();
				foreach( $keyList as $key) {
					$topFile[$key] = array_shift($file[$multi_colname][$key]);
				}
                $fileName = $this->systemFileUpload($topFile, $colname);
                if ($fileName) {
                    $db->setData($rec, $colname, $fileName);
                }
                //TODO: false だった場合の例外処理が必要では？
            }
		}
	}

	function systemFileUpload($upFile,$colname)
	{
		global $MAX_FILE_SIZE;
		global $UPLOAD_FILE_EXT;
		global $FileBase;

		if( isset( $_POST['MAX_FILE_SIZE'] ) ){
			$max_size = $_POST['MAX_FILE_SIZE'];
		}else{
			$max_size = $MAX_FILE_SIZE;
		}

		if(self::$checkData->getError($colname)){
			return;
		}

		if( $upFile['size'] > $max_size ){ return false; }

		// 拡張子の取得
		preg_match( '/(\.\w*$)/', $upFile['name'], $tmp );
		$ext		 = strtolower(str_replace( ".", "", $tmp[1] ));

		// ディレクトリの指定
		$directory	 = 'file/tmp/';
		if( mb_strpos( $this->fileDir, 'lock' ) !== false ) { $directory .= 'lock/'; }
		if(!is_dir($directory)) { mkdir( $directory, 0777 );chmod($directory, 0777); } //ディレクトリが存在しない場合は作成

		// ファイルパスの作成
		$fileName	 = $directory.md5( time().$colname.$upFile['name'] ).'.'.$ext;

		// 許可拡張子のみファイルのアップロード
		if( !in_array( $ext , $UPLOAD_FILE_EXT ) )
		{ return false; }

		switch($ext)
		{
			case 'gif'  :
			case 'jpg'  :
			case 'jpeg' :
			case 'png'  :
			case 'swf'  :
			case 'bmp'  :
				if( !SystemUtil::VerifyImageExt( $upFile[ 'tmp_name' ] , $ext ) )
				{ return false; }

				break;
		}

		if( file_exists($upFile['tmp_name'])){ $FileBase->upload($upFile['tmp_name'], $fileName) ; }

		$FileBase->fixRotate( $fileName );

		return $fileName;
	}


		function doFileDelete( &$gm, &$rec, &$old_rec = null ){
			global $DELETE_FILE_TYPES;
			global $DELETE_TABLE_TYPES;
			global $FileBase;

			if( !isset($_GET['type'] ) || isset($DELETE_TABLE_TYPES) && is_array($DELETE_TABLE_TYPES) && !isset($DELETE_TABLE_TYPES[$_GET['type']]) ){
				return;
			}

			$db = $gm[ $_GET['type'] ]->getDB();

			for( $i=0; $i<count( $db->colName ); $i++ ){

				if( in_array( $db->colType[ $db->colName[$i] ], $DELETE_FILE_TYPES )  ){
					$file_name = $db->getData( $rec, $db->colName[$i] );
					if( !is_null($old_rec) ){
						$old_file_name = $db->getData( $old_rec, $db->colName[$i] );
						if( $old_file_name == $file_name ){
							continue;
						}
						$file_name = $old_file_name;
					}
					if( !is_null($file_name) && strlen($file_name) ){
						$FileBase->delete(($file_name));
						if( $db->colType[ $db->colName[$i] ] == 'image' ){
							mod_Thumbnail::DeleteAll( $file_name );
						}
					}
				}
			}
		}

	/**
	 * ファイルアップロードの完了処理。
	 * 一時アップロードとしていたファイルを正式アップロードへと書き換える。
	 *
	 * @param gm GUIManagerオブジェクト
	 * @param db Databaseオブジェクト
	 * @param rec レコードデータ
	 */
	function uplodeComp( &$gm, &$db, &$rec )
	{
		global $FileBase;
		// カラムのうちファイルアップロードタイプのみ内容を確認する
		foreach( $db->colName as $colum )
		{
			if( $gm[$_GET['type']]->colType[$colum] == 'image' ||  $gm[$_GET['type']]->colType[$colum] == 'file' )
			{
				$before	 = $db->getData( $rec, $colum );
				$after	 = preg_replace( '/(file\/tmp\/|file\/tmp\/lock\/)(\w*\.\w*)$/', '\2', $before );
				if( $before != $after )
				{// ファイルのアップロードが行われていた場合データを差し替える。
					// 拡張子の取得
					preg_match( '/(\.\w*$)/', $after, $tmp );
					$ext		 = strtolower(str_replace( ".", "", $tmp[1] ));
					// ディレクトリの指定
					$dirList	 = explode( '/', $this->fileDir );
					$directory	 = 'file/';
					foreach( $dirList as $dir )
					{
						switch($dir)
						{
							case 'ext': // 拡張子
								$directory .= $ext.'/';
								break;
							case 'cat':	// 種類別
								switch($ext)
								{
									case 'gif':
									case 'jpg':
									case 'jpeg':
									case 'png':
										$cat = 'image';
										break;
									case 'swf':
										$cat = 'flash';
										break;
									case 'lzh':
									case 'zip':
										$cat = 'archive';
										break;
									default:
										$cat = 'category';
										break;
								}
								$directory .= $cat.'/';
								break;
							case 'lock': // htaccessでアクセス拒否を設定したディレクトリ
								$directory .= 'lock/';
								break;
							default:	// timeformat
								$directory .= date($dir).'/';
								break;
						}
						if(!is_dir($directory)) { mkdir( $directory, 0777 ); } //ディレクトリが存在しない場合は作成
					}
					if(!is_dir($directory)) { mkdir( $directory, 0777 ); } //ディレクトリが存在しない場合は作成

					if( $FileBase->file_exists($before) && $FileBase->copy($before, $directory.$after) ){
						if(file_exists($before)) { unlink($before); }
					}
					$db->setData( $rec, $colum, $directory.$after );
				}
			}
		}
	}

	/////////////////////////////////////////////////////////////////////////////////////////////////////////
	// 検索関係
	/////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * 検索結果描画。
	 *
	 * @param gm GUIManagerオブジェクトです。
	 * @param rec 登録情報のレコードデータです。
	 * @param args コマンドコメント引数配列です。　第一引数にIDを渡します。 第二引数にリンクするかを真偽値で渡します。
	 */
	function searchResult( &$gm, $rec, $args )
	{
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $loginUserType;
		global $loginUserRank;

		global $resultNum;
		global $pagejumpNum;
		global $phpName;
		// **************************************************************************************

		$db		 = $gm->getDB();

		$table   = SearchTableStack::getCurrent();
		$row   = SearchTableStack::getCurrentRow();
		$page	 = $_GET['page'];

		$resultNumLocal = ( 0 < $_GET[ 'num' ] ? $_GET[ 'num' ] : $resultNum );

		if( 0 < WS_SYSTEM_SEARCH_RESULT_NUM_MAX && WS_SYSTEM_SEARCH_RESULT_NUM_MAX < $resultNumLocal ) //表示件数の指定が多すぎる場合
			{ $resultNumLocal = WS_SYSTEM_SEARCH_RESULT_NUM_MAX; }

		// 変数の初期化。
		if(  !isset( $_GET['page'] )  ){ $page	 = 0; }

		else if( 0 < $page ) //ページが指定されている場合
		{
			$beginRow = $page * $resultNumLocal; //ページ内の最初のレコードの行数
			$tableRow = $row;        //テーブルの行数

			if( $tableRow <= $beginRow ) //テーブルの行数を超えている場合
			{
				$maxPage = ( int )( ( $tableRow - 1 ) / $resultNumLocal ); //表示可能な最大ページ

				$page = $maxPage;
			}
		}

		else if(  $page < 0 )
		{
			$page	 = 0;
		}
		// 検索結果情報を出力。
		$viewTable	 = $db->limitOffset(  $table, $page * $resultNumLocal, $resultNumLocal  );

		switch( $args[0] )
		{
			case 'info':
				// 検索結果情報データ生成
				$gm->setVariable( 'RES_ROW', $row );

				$gm->setVariable( 'VIEW_BEGIN', $page * $resultNumLocal + 1 );
				if( $row >= $page * $resultNumLocal + $resultNumLocal )
				{
					$gm->setVariable( 'VIEW_END', $page * $resultNumLocal + $resultNumLocal );
					$gm->setVariable( 'VIEW_ROW', $resultNumLocal );
				}
				else
				{
					$gm->setVariable( 'VIEW_END', $row );
					$gm->setVariable( 'VIEW_ROW', $row % $resultNumLocal );
				}
				$this->addBuffer( $this->getSearchInfo( $gm, $viewTable, $loginUserType, $loginUserRank ) );

				break;

			case 'result':
				// 検索結果をリスト表示
				for($i=0; $i<count((array)$TABLE_NAME); $i++)
				{
					$tgm[ $_GET['type'] ]->addAlias(  $TABLE_NAME[$i], $tgm[ $TABLE_NAME[$i] ]  );
				}

				if( 'embed' == self::$CallMode )
					{ $this->addBuffer( $this->getEmbedSearchResult( $gm, $viewTable, $loginUserType, $loginUserRank ) ); }
				else
					{ $this->addBuffer( $this->getSearchResult( $gm, $viewTable, $loginUserType, $loginUserRank ) ); }

				break;

			case 'pageChange':
				$this->addBuffer( $this->getSearchPageChange( $gm, $viewTable, $loginUserType, $loginUserRank, $row, $pagejumpNum, $resultNumLocal, $phpName, 'page' )  );
				break;

			case 'setResultNum':
				$resultNum				 = $args[1];
				break;

			case 'setPagejumpNum':
				$pagejumpNum			 = $args[1];
				break;

			case 'setPhpName': // ページャーのリンクphpファイルを指定(未設定時はsearch.php)
				$phpName				 = $args[1];
				break;

			case 'row':
				$this->addBuffer( $row );
				break;
		}
	}

	/**
	 * 検索結果描画。
	 *
	 * @param gm GUIManagerオブジェクトです。
	 * @param rec 登録情報のレコードデータです。
	 * @param args コマンドコメント引数配列です。　第一引数にIDを渡します。 第二引数にリンクするかを真偽値で渡します。
	 */
	function searchCreate( &$gm, $rec, $args )
	{
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $loginUserType;
		global $loginUserRank;
		// **************************************************************************************
		global $resultNum;
		global $pagejumpNum;
		// **************************************************************************************

		switch($args[0]){
			case 'new':
				if( isset( $args[1] ))
				$type = $args[1];
				else
				$type = $_GET['type'];
				SearchTableStack::createSearch( $type );
				break;
			case 'run':
				SearchTableStack::runSearch();
				break;
			case 'setPal':
			case 'setParam':
				SearchTableStack::setParam($args[1],array_slice($args,2));
				break;
			case 'setVal':
			case 'setValue':
				SearchTableStack::setValue($args[1],array_slice($args,2));
				break;
			case 'setAlias':
				SearchTableStack::setAlias($args[1],array_slice($args,2));
				break;
			case 'setAliasParam':
				SearchTableStack::setAliasParam($args[1],array_slice($args,2));
				break;
			case 'set'://予約
				break;
			case 'end':
				SearchTableStack::endSearch();
				break;
			case 'setPartsName':
				SearchTableStack::setPartsName($args[1],$args[2]);
				break;
			case 'sort':
				SearchTableStack::sort($args[1],$args[2]);
				break;
			case 'row':
				$this->addBuffer( SearchTableStack::getCurrentRow() );
				break;
		}
	}

	function getEmbedSearchResult( &$_gm, $table, $loginUserType, $loginUserRank )
	{
		global $gm;

		$type  = SearchTableStack::getType();

		if( SearchTableStack::getPartsName( 'list' ) )
		{
			$file = Template::getTemplate( $loginUserType , $loginUserRank , $_GET[ 'embedID' ] , self::ModifyTemplateLabel( 'SEARCH_EMBED_DESIGN' ) , false );

			if( is_file( $file ) )
				{ $html = Template::getListTemplateString( $gm[ $type ] , $table , $loginUserType , $loginUserRank , $_GET[ 'embedID' ] , self::ModifyTemplateLabel( 'SEARCH_EMBED_DESIGN' ) , false , 'list_' . SearchTableStack::getPartsName( 'list' ) ); }
			else
				{ $html = Template::getListTemplateString( $gm[ $type ] , $table , $loginUserType , $loginUserRank , $_GET[ 'embedID' ] , 'SEARCH_EMBED_DESIGN' , false , 'list_' . SearchTableStack::getPartsName( 'list' ) ); }
		}
		else
		{
			$file = Template::getTemplate( $loginUserType , $loginUserRank , $_GET[ 'embedID' ] , self::ModifyTemplateLabel( 'SEARCH_EMBED_DESIGN' ) , false );

			if( is_file( $file ) )
				{ $html = Template::getListTemplateString( $gm[ $type ] , $table , $loginUserType , $loginUserRank , $_GET[ 'embedID' ] , self::ModifyTemplateLabel( 'SEARCH_EMBED_DESIGN' ) , false , 'list' ); }
			else
				{ $html = Template::getListTemplateString( $gm[ $type ] , $table , $loginUserType , $loginUserRank , $_GET[ 'embedID' ] , 'SEARCH_EMBED_DESIGN' , false , 'list' ); }
		}

		return $html;
	}

	/**
	 * 検索結果をリスト描画する。
	 * ページ切り替えはこの領域で描画する必要はありません。
	 *
	 * @param gm GUIManagerオブジェクト
	 * @param table 検索結果のテーブルデータ
	 * @param loginUserType ログインしているユーザの種別
	 * @param loginUserRank ログインしているユーザの権限
	 */
	function getSearchResult( &$_gm, $table, $loginUserType, $loginUserRank )
	{
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $gm;
		// **************************************************************************************

		$type  = SearchTableStack::getType();

		switch( $type )
		{
			default:
				if(SearchTableStack::getPartsName('list'))
				{
					$file = Template::getTemplate( $loginUserType , $loginUserRank , $type , self::ModifyTemplateLabel( 'SEARCH_LIST_PAGE_DESIGN' ) , false );

					if( is_file( $file ) )
						{ $html = Template::getListTemplateString( $gm[ $type ] , $table , $loginUserType , $loginUserRank , $type , self::ModifyTemplateLabel( 'SEARCH_LIST_PAGE_DESIGN' ) , false , SearchTableStack::getPartsName('list') ); }
					else
						{ $html = Template::getListTemplateString( $gm[ $type ] , $table , $loginUserType , $loginUserRank , $type , 'SEARCH_LIST_PAGE_DESIGN' , false , SearchTableStack::getPartsName('list') ); }
				}
				else
				{
					$file = Template::getTemplate( $loginUserType , $loginUserRank , $type , self::ModifyTemplateLabel( 'SEARCH_LIST_PAGE_DESIGN' ) , false );

					if( is_file( $file ) )
						{ $html = Template::getListTemplateString( $gm[ $type ] , $table , $loginUserType , $loginUserRank , $type , self::ModifyTemplateLabel( 'SEARCH_LIST_PAGE_DESIGN' ) ); }
					else
						{ $html = Template::getListTemplateString( $gm[ $type ] , $table , $loginUserType , $loginUserRank , $type , 'SEARCH_LIST_PAGE_DESIGN' ); }
				}
				break;
		}

		return $html;
	}

	/**
	 * 検索結果ページ切り替え部を描画する。
	 *
	 * @param gm GUIManagerオブジェクト
	 * @param table 検索結果のテーブルデータ
	 * @param loginUserType ログインしているユーザの種別
	 * @param loginUserRank ログインしているユーザの権限
	 * @param partkey 分割キー
	 */
	function getSearchPageChange( &$gm, $table, $loginUserType, $loginUserRank, $row, $pagejumpNum, $resultNum, $phpName, $param )
	{
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $phpName;
		// **************************************************************************************

		$type = SearchTableStack::getType();

		switch( $type )
		{
			default:
				$design = Template::getTemplate( $loginUserType , $loginUserRank , '' , self::ModifyTemplateLabel( 'SEARCH_PAGE_CHANGE_DESIGN' ) );

				if( !is_file( $design ) )
					{ $design = Template::getTemplate( $loginUserType , $loginUserRank , '' , 'SEARCH_PAGE_CHANGE_DESIGN' ); }


				$query  = $_GET;

				if(!strlen($phpName))
				{
					if( 'index' == WS_SYSTEM_SYSTEM_FORM_ACTON )
					{
						$phpName                   = 'index.php';
						$query[ 'app_controller' ] = 'search';
					}
					else
						{ $phpName = 'search.php'; }

					$html    = SystemUtil::getPager( $gm, $design, $query, $row, $pagejumpNum, $resultNum, $phpName, $param , SearchTableStack::getPartsName('change') );
					$phpName = '';
				}
				else
					{ $html = SystemUtil::getPager( $gm, $design, $query, $row, $pagejumpNum, $resultNum, $phpName, $param , SearchTableStack::getPartsName('change') ); }

				break;

		}
		return $html;
	}

	/**
	 * 検索結果のページ切り替え情報を取得する。
	 *
	 * @param gm GUIManagerオブジェクト
	 * @param table 検索結果のテーブルデータ
	 * @param loginUserType ログインしているユーザの種別
	 * @param loginUserRank ログインしているユーザの権限
	 */
	function getSearchInfo( &$gm, $table, $loginUserType, $loginUserRank )
	{
		$type = SearchTableStack::getType();

		switch( $type )
		{
			default:
				if(SearchTableStack::getPartsName('info'))
				{
					$html = Template::getTemplateString( $gm , null , $loginUserType , $loginUserRank , '' , self::ModifyTemplateLabel( 'SEARCH_PAGE_CHANGE_DESIGN' ) , false , null, SearchTableStack::getPartsName('info') );

					if( !is_file( $html ) )
						{ $html = Template::getTemplateString( $gm , null , $loginUserType , $loginUserRank , '' , 'SEARCH_PAGE_CHANGE_DESIGN' , false , null, SearchTableStack::getPartsName('info') ); }
				}
				else
				{
					$html = Template::getTemplateString( $gm , null , $loginUserType , $loginUserRank , '' , self::ModifyTemplateLabel( 'SEARCH_PAGE_CHANGE_DESIGN' ) , false , null, 'info' );

					if( !is_file( $html ) )
						{ $html = Template::getTemplateString( $gm , null , $loginUserType , $loginUserRank , '' , 'SEARCH_PAGE_CHANGE_DESIGN' , false , null, 'info' ); }
				}
				break;

		}
		return $html;
	}

	/////////////////////////////////////////////////////////////////////////////////////////////////////////
	// 汎用情報出力関係
	/////////////////////////////////////////////////////////////////////////////////////////////////////////


	//main css output
	function css_load( &$gm, $rec, $args ){
		global $css_name;
		global $CSS_PATH;
		global $css_file_paths;
		global $sp_css_file_paths;
		global $sp_mode;
		global $loginUserType;

		if( is_file($CSS_PATH.$css_name) )
		{
			switch($loginUserType)
			{
			case 'admin':
			case 'cUser':
				break;
			default:
				$file = $CSS_PATH.$css_name;
				if( $sp_mode )
				{
					if( is_file($CSS_PATH.'sp/'.$css_name) )		 { $file = $CSS_PATH.'sp/'.$css_name; }
					elseif( is_file($CSS_PATH.'sp/standard.css') )	 { $file = $CSS_PATH.'sp/standard.css'; }
				}
				$this->addBuffer( '<link rel="stylesheet" type="text/css" href="'.$file.'" media="all" />'."\n" );
				break;
			}
		}

		if( $sp_mode ){
			$css_root = $sp_css_file_paths;
		}else{
			$css_root = $css_file_paths;
		}

		if( isset($css_root) ){
			foreach( array('all', $loginUserType) as $type )
			{
				if( isset($css_root[$type]) || is_array($css_root[$type]) ){
					foreach( $css_root[$type] as $css_file_path ){
						$this->addBuffer( '<link rel="stylesheet" type="text/css" href="'.$css_file_path.'" media="all" />'."\n" );
					}
				}
			}
		}
	}

	//main js output
	function js_load( &$gm, $rec, $args ){
		global $js_file_paths;
		global $sp_js_file_paths;
		global $sp_mode;
		global $loginUserType;


		if( $sp_mode ){
			$root_path = $sp_js_file_paths;
		}else{
			$root_path = $js_file_paths;
		}

		foreach( array('all', $loginUserType) as $type )
		{
			if( isset($root_path[$type]) || is_array($root_path[$type]) ){
				foreach( $root_path[$type] as $js_file_path ){
					$this->addBuffer( '<script type="text/javascript" src="'.$js_file_path.'"></script>'."\n" );
				}
			}
		}
	}

	function feed_load( &$gm , $rec , $args )
	{
		global $HOME;
		global $CONF_FEED_ENABLE;
		global $CONF_FEED_TABLES;
		global $CONF_FEED_TITLES;
		global $CONF_FEED_OUTPUT_DIR;

		if( !$CONF_FEED_ENABLE )
			{ return; }

		foreach( $CONF_FEED_TABLES as $tableName )
		{
			$rssPath = $CONF_FEED_OUTPUT_DIR . $tableName . '_rss.xml';

			if( is_file( $rssPath ) )
			{
				if( is_null($CONF_FEED_TITLES) || !isset($CONF_FEED_TITLES[$tableName]) ) {
					$gm = GMList::getGM($tableName);
					$template = Template::getTemplate('nobody', 1, $tableName, 'FEED_RSS_DESIGN');
					$title = $gm->getString($template, null, 'head_title');
				}else{
					$title = $CONF_FEED_TITLES[$tableName];
				}
				$this->addBuffer( '<link rel="alternate" href="' . $HOME . $rssPath . '" type="application/rss+xml" title="' . $title . '" />' . "\n" );
			}

			$atomPath = $CONF_FEED_OUTPUT_DIR . $tableName . '_atom.xml';

			if( is_file( $atomPath ) )
			{
				if( is_null($CONF_FEED_TITLES) || !isset($CONF_FEED_TITLES[$tableName]) ) {
					$gm = GMList::getGM($tableName);
					$template = Template::getTemplate('nobody', 1, $tableName, 'FEED_ATOM_DESIGN');
					$title = $gm->getString($template, null, 'head_title');
				}else{
					$title = $CONF_FEED_TITLES[$tableName];
				}

				$this->addBuffer( '<link rel="alternate" href="' . $HOME . $atomPath . '" type="application/atom+xml" title="' . $title . '" />' . "\n" );
			}
		}
	}

	//main link output
	function link_load( &$gm, $rec, $args ){
		global $head_link_object;

		if( is_null($head_link_object) || !is_array($head_link_object) )
		return;
		foreach( $head_link_object as $head_link ){
			$this->addBuffer( '<link rel="'.$head_link['rel'].'" type="'.$head_link['type'].'" href="'.$head_link['href'].'" />'."\n" );
		}
	}

	/*
	 * errorメッセージの個別表示用
	 */
	function validate( &$gm, $rec, $args ){

		if( !count( $args ) )
			{ $args = self::$ValidateColumnCache; }

		foreach( $args as $error ){
			$this->addBuffer( self::$checkData->getError( $error ) );
		}
	}

	/*
	 * errorメッセージの個別表示用
	 */
	function is_validate( &$gm, $rec, $args ){
		foreach( explode('/',$args[0]) as $l ){
			$ret = self::$checkData->isError( $l , $args[1] );
			if(strlen($ret)){ $this->addBuffer( $ret ); break; }
		}

		self::$ValidateColumnCache = explode( '/' , $args[ 0 ] );
	}

	/////////////////////////////////////////////////////////////////////////////////////////////////////////
	// 例外処理関係
	/////////////////////////////////////////////////////////////////////////////////////////////////////////

	/*
	 * 例外を自動の例外出力に回す前にキャッチして、内容によって別の処理に長し込む為のもの。
	 */
	static function manageExceptionView( $className ){
		global $gm;
		global $loginUserType;
		global $loginUserRank;

		if(is_null($gm)){
			// GUIManagerが生成される前のエラーなので、諦めて例外用のエラーを出している。
			return false;
		}

		//分岐などを記述する。
		/*
		switch($className){
			case "IllegalAccessException":
				//非ログインかどうか
				break;
		}
	 	*/

		return false;
	}


	/**********************************************************************************************************
	 * システム用メソッド
	 **********************************************************************************************************/

	static $checkData = null;

	/**
	 * コンストラクタ。
	 */
	function System()	{ $this->flushBuffer(); }

	/*
	 * エラーメッセージをGUIManagerのvariableにセットする
	 */
	function setErrorMessage(&$gm){
		if( self::$checkData && !self::$checkData->getCheck() ){
			$gm->setVariable( 'error_msg' , self::$checkData->getError() );
			$this->error_msg = "";
		}else{
			$gm->setVariable( 'error_msg' , '' );
		}
	}

	static $pageRecord = null;

	/**
	 * ページに関連付いたレコードを記憶する
	 * @param db Databaseオブジェクト
	 * @param table_type テーブルタイプ a(all)/n(nomal)/d(delete)
	 */
	static function setPageRecord( $db, $table_type ){
		global $loginUserType;
		global $LOGIN_ID;

	    if( !isset($_GET['id']) && $_GET['type'] == $loginUserType ){
	    	$_GET['id'] = $LOGIN_ID;
	    }

		self::$pageRecord = $db->selectRecord($_GET['id'],$table_type);

		ConceptSystem::CheckRecord(self::$pageRecord)->OrThrow('RecordNotFound');

		return self::$pageRecord;
	}

	/**
	 * プレビュー用にPOSTデータを仮レコードとして設定する
	 * @param db Databaseオブジェクト
	 * @param table_type テーブルタイプ a(all)/n(nomal)/d(delete)
	 */
	function setPreviewRecord( $db , $post , $previewMode ){
		global $loginUserType;
		global $loginUserRank;
		global $LOGIN_ID;
		global $gm;

		if( !isset( $_GET[ 'id' ] ) && $loginUserType == $_GET[ 'type' ] )
			{ $_GET[ 'id' ] = $LOGIN_ID; }

		$vRec = $db->selectRecord( $_GET[ 'id' ] , $table_type );

		if( !$vRec )
			{ $vRec = Array(); }

		foreach( $db->colName as $column )
		{
			if( is_array( $_POST[ $column ] ) )
				{ $vRec[ $column ] = implode( '/' , $_POST[ $column ] ); }
			else if( isset( $_POST[ $column ] ) )
				{ $vRec[ $column ] = $_POST[ $column ]; }
		}

		if( 'regist' == $previewMode )
			{ $this->registProc( $gm , $vRec , $loginUserType , $loginUserRank , true ); }
		else if( 'edit' == $previewMode )
			{ $this->editProc( $gm , $vRec , $loginUserType , $loginUserRank , true ); }

		self::$pageRecord = $vRec;

		ConceptSystem::CheckRecord( self::$pageRecord )->OrThrow( 'RecordNotFound' );

		return self::$pageRecord;
	}

	/*
	 * ページ全体で共通のheadを返する。
	 * 各種表示ページの最初に呼び出される関数
	 *
	 * 出力に制限をかけたい場合や分岐したい場合はここで分岐処理を記載する。
	 */
	static function getHead($gm,$loginUserType,$loginUserRank){
		global $NOT_LOGIN_USER_TYPE;

		if( self::$head || isset( $_GET['hfnull'] ) ){ return "";}

		self::$head = true;

		$html = "";

		if( $loginUserType == $NOT_LOGIN_USER_TYPE )	{ $html = Template::getTemplateString( $gm[ 'system' ] , null , $loginUserType , $loginUserRank , '' , 'HEAD_DESIGN' ); }
		else											{ $html = Template::getTemplateString( $gm[ $loginUserType ] , $rec , $loginUserType , $loginUserRank , '' , 'HEAD_DESIGN' ); }

		if( $_SESSION['ADMIN_MODE'] || $loginUserType == 'admin' ){
			$html .= Template::getTemplateString( $gm[ $loginUserType ] , $rec , $loginUserType , $loginUserRank , '' , 'HEAD_DESIGN_ADMIN_MODE' );
		}
		return $html;
	}

	/*
	 * ページ全体で共通のfootを返す。
	 * 各種表示ページの最後で呼び出される関数
	 *
	 * 出力に制限をかけたい場合や分岐したい場合はここで分岐処理を記載する。
	 */
	static function getFoot($gm,$loginUserType,$loginUserRank){
		global $NOT_LOGIN_USER_TYPE;

		if( self::$foot || isset( $_GET['hfnull'] ) ){ return "";}

		self::$foot = true;

		if( $loginUserType == $NOT_LOGIN_USER_TYPE )	{ return Template::getTemplateString( $gm[ 'system' ] , null , $loginUserType , $loginUserRank , '' , 'FOOT_DESIGN' ); }
		else											{ return Template::getTemplateString( $gm[ $loginUserType ] , $rec , $loginUserType , $loginUserRank , '' , 'FOOT_DESIGN' ); }
	}

	/*
	 * 最終的な出力を行なう為に画面描画の最後に呼ばれる。
	 * ここを書き換える事で、出力にフィルタをかけられる。
	 *
	 * mobile向けの文字コード変換も行なっている。
	 */
	static function flush(){
		global $terminal_type;
		global $OUTPUT_CHARACODE;
		global $ALL_DEBUG_FLAG;
		global $DEBUG_TYPE;
		global $DEBUG_BUFFER;

		$output = ob_get_clean();

		if( strlen(self::$title) > 0 )
			{
				$output = preg_replace( '/<title>(.*)<\/title>/i', '<title>'.self::$title.'</title>', $output );
				$output = preg_replace( '/<meta property="og:title" content="(.*)" \/>/', '<meta property="og:title" content="' . self::$title . '" />', $output );
			}
		if( strlen(self::$description) > 0 )
			{
				$output = preg_replace( '/<meta name="description" content=(.*)>/', '<meta name="description" content="'.self::$description.'">', $output );
				$output = preg_replace( '/<meta property="og:description" content="(.*)" \/>/', '<meta property="og:description" content="' . self::$description . '" />', $output );
			}

		if( strlen(self::$keywords) > 0 )	 { $output = preg_replace( '/<meta name="keywords" content=(.*)>/', '<meta name="keywords" content="'.self::$keywords.'">', $output ); }

		if( strlen(self::$robots) > 0 )	 { $output = preg_replace( '/<meta name="robots" content=(.*)>/', '<meta name="robots" content="'.self::$robots.'">', $output ); }

		if( 0 < strlen( self::$ogTitle ) )
			{ $output = preg_replace( '/<meta property="og:title" content="(.*)" \/>/', '<meta property="og:title" content="' . self::$ogTitle . '" />', $output ); }

		if( 0 < strlen( self::$ogType ) )
			{ $output = preg_replace( '/<meta property="og:type" content="(.*)" \/>/', '<meta property="og:type" content="' . self::$ogType . '" />', $output ); }

		if( 0 < strlen( self::$ogDescription ) )
			{ $output = preg_replace( '/<meta property="og:description" content="(.*)" \/>/', '<meta property="og:description" content="' . self::$ogDescription . '" />', $output ); }

		if( 0 < strlen( self::$ogURL ) )
			{ $output = preg_replace( '/<meta property="og:url" content="(.*)" \/>/', '<meta property="og:url" content="' . self::$ogURL . '" />', $output ); }

		if( 0 < strlen( self::$ogImage ) )
			{ $output = preg_replace( '/<meta property="og:image" content="(.*)" \/>/', '<meta property="og:image" content="' . self::$ogImage . '" />', $output ); }

		if( $terminal_type ){
			if( $OUTPUT_CHARACODE != 'UTF-8' ){
				print mb_convert_encoding( $output, $OUTPUT_CHARACODE, 'UTF-8' );
			}else{
				print $output;
			}
		}else{
				print $output;
		}

		if( $ALL_DEBUG_FLAG && 'subview' == $DEBUG_TYPE ) //デバッグモードがsubviewの場合
		{
            $controller = strtolower( $controllerName );
			$isAPI    = ( 'api' == $controller );
			$isCron   = ( 'cron' == $controller );
			$isKeyGen = ( 'update' == $controller );
			$isThumbs = ( 'thumbnail' == $controller );

			if( !$isAPI && !$isCron && !$isKeyGen && !$isThumbs && $DEBUG_BUFFER ) //デバッグ情報がある場合
				{ print '<script>$(function(){ InitializeDebugView();AddDebugInfo( ' . json_encode( $DEBUG_BUFFER ) . ' );});</script>'; }
		}

		TemplateCache::SaveCache( $output );
	}

	// title,descrption,keywordsを変更したい場合テンプレート上から呼び出す
	function setTitle( &$gm, $rec, $args)		  { self::$title		 = self::convertSpace($args); }
	function setDescription( &$gm, $rec, $args)	  { self::$description	 = self::convertSpace($args); }
	function setKeywords( &$gm, $rec, $args)	  { self::$keywords		 = self::convertSpace($args); }
	function setRobots( &$gm, $rec, $args)		  { self::$robots		 = self::convertSpace($args); }

	function setOGTitle( &$gm, $rec, $args)       { self::$ogTitle       = self::convertSpace($args); }
	function setOGType( &$gm, $rec, $args)        { self::$ogType        = self::convertSpace($args); }
	function setOGDescription( &$gm, $rec, $args) { self::$ogDescription = self::convertSpace($args); }
	function setOGURL( &$gm, $rec, $args)         { self::$ogURL         = self::convertSpace($args); }
	function setOGImage( &$gm, $rec, $args)       { self::$ogImage       = self::convertSpace($args); }

	function convertSpace( $text )
	{
		if( is_array($text) ) { $text = implode( " ", $text ); }
		return str_replace( array("!CODE001;","!CODE101;"), array(" ", " ") , $text );
	}

	function modifyTemplateLabel( $iTemplateLabel )
	{
		if( isset( $_GET[ 'design' ] ) )
			{ $design = $_GET[ 'design' ]; }
		else if( isset( $_POST[ 'design' ] ) )
			{ $design = $_POST[ 'design' ]; }

		if( !$design )
			{ return $iTemplateLabel; }

		if( preg_match( '/\W/' , $design ) )
			{ return $iTemplateLabel; }

		return $iTemplateLabel . '_' . $design;
	}
}


class SearchTableStack{
	private static $stack = Array();
	private static $row_stack = Array();
	private static $current_count = 0;
	private static $current_search = null;
	//private static $stack_search = Array();

	private static $list_parts = Array();
	private static $info_parts = Array();
	private static $change_parts = Array();

	static function pushStack(&$table){
		self::$stack[ self::$current_count ] = $table;
	}

	static function popStack(){
		$stack = self::$stack[ self::$current_count ];
		unset(self::$stack[ self::$current_count ]);
		unset(self::$row_stack[ self::$current_count ]);
		return $stack;
	}

	static function getCurrent(){
		return self::$stack[ self::$current_count ];
	}

	static function getCurrentCount(){
		return self::$current_count;
	}

	static function getCurrentRow(){
		global $gm;

		if( !isset(self::$row_stack[ self::$current_count ]) ){
			self::$row_stack[ self::$current_count ] = $gm[ self::getType() ]->getDB()->getRow( self::$stack[ self::$current_count ] );
		}
		return self::$row_stack[ self::$current_count ];
	}

	static function createSearch($type){
		global $gm;
		self::$current_count++;

		self::$current_search = new Search($gm[ $type ],$type);
		self::$current_search->paramReset();

		self::$list_parts[ self::$current_count ] = "";
		self::$info_parts[ self::$current_count ] = "";
		self::$change_parts[ self::$current_count ] = "";
	}

	static function setValue($coumn_name,$var){
		if( count($var) == 1 ){
			self::$current_search->setValue($coumn_name,$var[0]);
		}else{
			self::$current_search->setValue($coumn_name,$var);
		}
	}

	static function setParam($table_name,$var){
		self::$current_search->setParamertor($table_name,$var);
	}

	static function setAlias($table_name,$var){
		if( is_array($var) ){
			self::$current_search->setAlias($table_name,implode( ' ', $var ) );
		}else{
			self::$current_search->setAlias($table_name,$var);
		}
	}
	static function setAliasParam($coumn_name,$var){
		self::$current_search->setAliasParam($coumn_name,$var);
	}

	static function runSearch(){
		global $gm;
		global $loginUserType;
		global $loginUserRank;

		$sys	 = SystemUtil::getSystem( self::getType() );

		$sys->searchResultProc( $gm, self::$current_search, $loginUserType, $loginUserRank );

		$table = self::$current_search->getResult();

		$sys->searchProc( $gm, $table, $loginUserType, $loginUserRank );

		self::pushStack( $table );
	}

	static function endSearch(){
		self::popStack();
		unset(self::$row_stack[ self::$current_count ]);
		self::$current_count--;
	}

	static function setPartsName( $type, $parts ){
		switch($type){
			case 'list':
				self::$list_parts[ self::$current_count ] = $parts;
				break;
			case 'info':
				self::$info_parts[ self::$current_count ] = $parts;
				break;
			case 'change':
				self::$change_parts[ self::$current_count ] = $parts;
				break;
		}
	}

	static function getPartsName($type){
		$ret = '';
		switch($type){
			case 'list':
				if( isset( self::$list_parts[ self::$current_count ]) ){
					$ret = self::$list_parts[ self::$current_count ];
				}
				break;
			case 'info':
				if( isset( self::$info_parts[ self::$current_count ]) ){
					$ret = self::$info_parts[ self::$current_count ];
				}
				break;
			case 'change':
				if( isset( self::$change_parts[ self::$current_count ]) ){
					$ret = self::$change_parts[ self::$current_count ];
				}
				break;
		}
		return $ret;
	}

	static function getType(){
		if( self::$current_count == 0 )
		return $_GET['type'];
		else
		return self::$current_search->type;
	}

	static function sort($key,$param){
		self::$current_search->sort['key'] = $key;
		self::$current_search->sort['param'] = $param;
	}
}

?>