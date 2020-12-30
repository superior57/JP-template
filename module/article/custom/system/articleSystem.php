<?php
//★クラス //

/**
	@brief systemクラス。
*/
class articleSystem extends System
{

	public static $image = null;
	static $drawPartsNum = 20;
	static $partsJumpNum = 5;
	static $drawParts = '';
	static $partsPager = '';

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
	 * 登録前段階処理。
	 * フォーム入力以外の方法でデータを登録する場合は、ここでレコードに値を代入します。
	 *
	 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
	 * @param rec フォームのからの入力データを反映したレコードデータ。
	 */
	function registProc( &$gm, &$rec, $loginUserType, $loginUserRank ,$check = false)
	{
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $LOGIN_ID;
		global $ACTIVE_NONE;
		// **************************************************************************************

		parent::registProc( $gm, $rec, $loginUserType, $loginUserRank, $check );

		$db	 = $gm[ $_GET['type'] ]->getDB();
		$db->setData( $rec, 'owner', $LOGIN_ID );
		$db->setData( $rec, 'activate', $ACTIVE_NONE );
	}

	function editProc(&$gm, &$rec, $loginUserType, $loginUserRank, $check = false)
	{
		$db = $gm[articleLogic::$type ]->getDB();

		$open_y = $_POST['open_y'] ? (int)$_POST['open_y'] : date('Y');
		$open_m = $_POST['open_m'] ? (int)$_POST['open_m'] : date('n');
		$open_d = $_POST['open_d'] ? (int)$_POST['open_d'] : date('j');
		$open_h = $_POST['open_h'] ? (int)$_POST['open_h'] : 0;
		$open_i = $_POST['open_i'] ? (int)$_POST['open_i'] : 0;
		$open = mktime($open_h, $open_i, 0,  $open_m, $open_d, $open_y);
		$db->setData($rec,'open', $open);

		$db->setData( $rec,'edit', time() );

		if(!$check) { $this->uplodeComp($gm,$db,$rec); } // ファイルのアップロード完了処理
	}

	function editComp(&$gm, &$rec, &$old_rec, $loginUserType, $loginUserRank)
	{
		$db = $gm[articleLogic::$type ]->getDB();
		if( isset($_POST['category']) && is_array($_POST['category']) ) {
			article_categoryLogic::update($db->getData($rec,'id'), $_POST['category']);
		}else{
			// カテゴリのチェックが全部外された場合
			article_categoryLogic::update($db->getData($rec,'id'), array());
		}
	}

	function drawEditForm(&$gm, &$rec, $loginUserType, $loginUserRank) {
		$db = $gm[ $_GET['type'] ]->getDB();
		$id = $db->getData($rec,'id');
		$open = $db->getData($rec,'open');
		if( $open > 0 ) {
			$_POST['open_y'] = date('Y', $open);
			$_POST['open_m'] = date('n', $open);
			$_POST['open_d'] = date('j', $open);
			$_POST['open_h'] = date('G', $open);
			$_POST['open_i'] = (int)date('i', $open);
		}

		$tag = article_tagLogic::getDataList( $id);
		$gm[ $_GET['type'] ]->setVariable( 'jsonTag', json_encode($tag) );

		$category = article_categoryLogic::getDataList($id);
		if( count($category)>0 ) {
			$_POST['category'] = [];
			foreach( $category as $key => $caData) {
				$_POST['category'][] = $caData['id'];
			}
		}

		parent::drawEditForm($gm, $rec, $loginUserType, $loginUserRank);
	}

	function feedProc( $table = null )
	{
		$db = GMList::getDB( articleLogic::$type );

		if( Conf::checkData('article', 'feed', 'on') ){
			if( !$table )
				{ $table = $db->getTable(); }

			$table = $db->searchTable( $table , 'open' , '<' , time() );
			$table = $db->searchTable( $table , 'activate' , '=' , 4 );
			$table = $db->sortTable( $table , 'open' , 'desc' );
			$table = $db->limitOffset( $table , 0 , 10 );
		}else{
			$table = $db->getEmptyTable();
		}

		return parent::feedProc( $table );
	}

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
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $ACTIVE_ACCEPT;
		global $LOGIN_ID;
		// **************************************************************************************

		$db	 = $gm[ $_GET['type'] ]->getDB();

		$result = false;
		switch($loginUserType)
		{
			case 'admin':
				$result = true;
				break;
			case 'cUser':
			case 'nUser':
				if( self::drawCheck($db,$rec) ) { $result = true; }
				else  if( $db->getData( $rec , 'owner' ) == $LOGIN_ID ) {
					$result = true;
				}
				break;
			default:
				if( self::drawCheck($db,$rec) ) { $result = true; }
				break;
		}
		return $result;
	}

	/*
	 * データから表示可能可確認
	 */
	function drawCheck( $db, $rec )
	{
		global $ACTIVE_ACCEPT;

		$act = $db->getData( $rec , 'activate' );
		$open = $db->getData( $rec , 'open' );

		if( $act == $ACTIVE_ACCEPT && $open < time()) {
			return true;
		}
		return false ;
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
		// 簡易情報変更（情報ページからの内容変更処理）
		if(  isset( $_POST['post'] ) ){
			if( $loginUserType == 'admin' ){
				$db		 = $gm[ $_GET['type'] ]->getDB();

				for( $i=0; $i<count($db->colName); $i++ ){
					if(   isset(   $_POST[ $db->colName[$i] ]  )   ){
						$db->setData( $rec, $db->colName[$i], $_POST[ $db->colName[$i] ] );
					}
				}
				$db->updateRecord( $rec );
			}
		}
	}
	
	function drawInfo(&$gm, &$rec, $loginUserType, $loginUserRank) {
		$user = self::drawUserType($loginUserType);
		parent::drawInfo($gm, $rec, $user, $loginUserRank);
	}
	
	function registCompCheck(&$gm, &$rec, $loginUserType, $loginUserRank, $edit = false) {
		global $FileBase;

		parent::registCompCheck( $gm, $rec, $loginUserType, $loginUserRank ,$edit );

		$db = $gm[ articleLogic::$type ]->getDB();
		self::$image = $db->getData($rec, 'image');

		// サムネイルのURL画像のチェック
		if(strlen($_POST['url_img']) && strlen($_POST['url_img_used']) && !strlen($_POST['image_DELETE']))
		{
			$name = 'image';
			$file = $db->getData( $rec, $name );
			// POSTで送信されたファイルを削除
			if(isset($_FILE[$name]))
			{ $FileBase->delete($file); }

			if( strlen($_POST['url_img'])>0) {
				$data = article_partsLogic::getUrlImage(articleLogic::$upload_dir.$db->getData($rec,'id'), $_POST['url_img']);
				if( isset($data['path']) ){ $file = $data['path']; }
			}

			if( $FileBase->file_exists($file)) {
				$db->setData($rec,$name, $file);
			}
			else {
				self::$checkData->addError( $name.'_URL_ERR', null, $name );
				return self::$checkData->getCheck();
			}

		}
		// エラー内容取得
		return self::$checkData->getCheck();
	}
	
	/////////////////////////////////////////////////////////////////////////////////////////////////////////
	// 検索関係
	/////////////////////////////////////////////////////////////////////////////////////////////////////////
	/**
	 * 検索処理。
	 * フォーム入力以外の方法で検索条件を設定したい場合に利用します。
	 *
	 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
	 * @param table フォームのからの入力内容に一致するレコードを格納したテーブルデータ。
	 */
	function searchProc( &$gm, &$table, $loginUserType, $loginUserRank )
	{
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $LOGIN_ID;
		global $ACTIVE_ACCEPT;
		// **************************************************************************************

		$type = SearchTableStack::getType();
			
		$db		 = $gm[ $type ]->getDB();

		$relation = [];

		switch($loginUserType)
		{
		case 'admin':
			if( isset($_GET['tag_word']) && strlen($_GET['tag_word'])) {
				$relation['tag'] = $_GET['tag_word'];
			}
			if( isset($_GET['category_word']) && strlen($_GET['category_word'])) {
				$relation['category'] = $_GET['category_word'];
			}
			$table = articleLogic::setRelationSearchTable( $table, $relation, 'word');
			break;
		case 'cUser':
		case 'nUser':
			if( isset($_GET['self']) ) { 
				$table = $db->searchTable(  $table, 'owner', '=', $LOGIN_ID  );
				break;
			}
		default:
			if( isset($_GET['tag']) ) {
				$relation['tag'] = (array)$_GET['tag'];
			}
			if( isset($_GET['category']) ) {
				$relation['category'] = (array)$_GET['category'];
			}
			$table = articleLogic::setRelationSearchTable( $table, $relation, 'id');
			$table = articleLogic::getOpenConditionTable($table);
			break;
		}
	}

	function drawSearch( &$gm, &$sr, $table, $loginUserType, $loginUserRank ){
		SearchTableStack::pushStack($table);
		$user = self::drawUserType($loginUserType);
		parent::drawSearch($gm, $sr, $table, $user, $loginUserRank);
		
	}

	function drawSearchNotFound(&$gm, $loginUserType, $loginUserRank) {
		$user = self::drawUserType($loginUserType);
		
		parent::drawSearchNotFound($gm, $user, $loginUserRank);
	}

	public function getSearchResult(&$_gm, $table, $loginUserType, $loginUserRank)
	{
		$user = self::drawUserType($loginUserType);
		return parent::getSearchResult($_gm, $table, $user, $loginUserRank);
	}

	function getEmbedSearchResult(&$_gm, $table, $loginUserType, $loginUserRank) {
		$user = self::drawUserType($loginUserType);
		return parent::getEmbedSearchResult($_gm, $table, $loginUserType, $loginUserRank);
		
	}

	function drawUserType( $iUser )
	{
		$user = 'nobody';
		switch($iUser)
		{
		case 'admin':
			$user = $iUser;
			break;
		case 'cUser':
		case 'nUser':
			if( isset($_GET['self']) )
			{ $user = $iUser; }
			break;
		}
		return $user;
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
					$directory = articleLogic::$upload_dir.$db->getData($rec,'id').'/';
					if(!is_dir($directory)) { mkdir( $directory, 0777 ); } //ディレクトリが存在しない場合は作成

					if( $FileBase->file_exists($before) && $FileBase->copy($before, $directory.$after) ){
						if(file_exists($before)) { unlink($before); }
					}
					$db->setData( $rec, $colum, $directory.$after );
				}
			}
		}
	}


	function drawParts( &$gm, $rec, $args )
	{
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $loginUserType;
		global $loginUserRank;
		// **************************************************************************************

		switch( $args[0] )
		{
			case 'setResultNum':
				self::$drawPartsNum = $args[1];
				break;
			case 'setPagejumpNum':
				self::$partsJumpNum = $args[1];
				break;
			case 'result':
				$this->addBuffer(self::$drawParts);
				break;
			case 'pager':
				$this->addBuffer(self::$partsPager);
				break;
			case 'info':
			default:
				$db = $gm->getDB();
				$id = $db->getData($rec, 'id');
				$pager = $db->getData($rec, 'peger');

				$resultNum = self::$drawPartsNum>0 ? self::$drawPartsNum : 20;
				$pagejumpNum = self::$partsJumpNum >0 ? self::$partsJumpNum : 5;
				if( $pager == 'off') { $resultNum = count(explode('/',$db->getData($rec,'parts'))); }

				// partsとそのページャのソースを取得
				$parts = article_partsLogic::getDrawParts($gm, $loginUserType, $loginUserRank, $id, $db->getData($rec,'parts'), $resultNum,$pagejumpNum,'');
				self::$drawParts = $parts['list'] ;
				self::$partsPager = $parts['pager'] ;
				if( $pager == 'off') { self::$partsPager = ""; }

		}
	}


}