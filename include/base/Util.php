<?PHP

include_once './include/base/CommandBase.php';

/*******************************************************************************************************
 * <PRE>
 *
 * 汎用関数群
 *
 * @version 1.0.0
 *
 * </PRE>
 *******************************************************************************************************/


class SystemUtilBase{

	static function includeModule( $path )
	{
		if( file_exists($path) ) { include_once $path; }
	}

	/**
	 * 特定のテーブルの特定レコードの1カラムのデータが欲しい時のラッパー関数
	 *
	 * @param tableName 対象テーブル
	 * @param id 対象レコードID
	 * @param colum 対象カラム
	 * @return 指定されたカラムの値
	 */
	static function getTableData( $tableName, $id, $colum )
	{
		$gm	 = GMList::getGM($tableName);
		$db	 = $gm->getDB();
		$rec = $db->selectRecord($id);

		$result	 = null;
		if(isset($rec)) { $result = $db->getData($rec, $colum); }

		return	$result;
	}

	/**
	* 特定のテーブルの特定レコードの1カラムのデータが欲しい時のラッパー関数
	*
	* @param tableName 対象テーブル
	* @param id 対象レコードID
	* @param colum 対象カラム
	* @return 指定されたカラムの値
	*/
	static function getDeleteTableData( $tableName, $id, $colum )
	{
		$gm	 = GMList::getGM($tableName);
		$db	 = $gm->getDB();
		$rec = $db->selectRecord($id,"delete");

		$result	 = null;
		if(isset($rec)) { $result = $db->getData($rec, $colum); }

		return	$result;
	}

	/**
	 * systemテーブルのデータが欲しい時のラッパー関数
	 *
	 * @param colum 対象カラム
	 * @return 指定されたカラムの値
	 */
	static function getSystemData( $colum ) { return SystemUtil::getTableData( 'system', 'ADMIN', $colum ); }

	/**
		@brief     search.phpと同様の検索結果のテーブルを得る。
		@param[in] $iQuery 検索クエリ。
		@return    検索結果のテーブル。
	*/
	static function getSearchResult( $iQuery )
	{
		global $gm;
		global $magic_quotes_gpc;
		global $loginUserType;
		global $loginUserRank;

		$GetSwap = $_GET;
		$_GET    = $iQuery;

		$db  = $gm[ $_GET[ 'type' ] ]->getDB();
		$sr  = new Search( $gm[ $_GET[ 'type' ] ] , $_GET );
		$sys = SystemUtil::getSystem( $_GET[ 'type' ] );

		if( $magic_quotes_gpc || 'sjis' != $db->char_code ) //エスケープが不要な場合
			{ $sr->setParamertorSet( $_GET ); }
		else //エスケープが必要な場合
			{ $sr->setParamertorSet( addslashes_deep( $_GET ) ); }

		$sys->searchResultProc( $gm , $sr , $loginUserType , $loginUserRank );

		$table = $sr->getResult();

		$sys->searchProc( $gm , $table , $loginUserType , $loginUserRank );

		$_GET = $GetSwap;

		return $table;
	}

	/**
		@brief     指定のレコードがテーブル内の何行目に存在するか調べる。
		@remarks   この関数は $iRec が $iTable に存在しない場合でもエラーは返しません。
		@param[in] $iDB    検索に使用するDB。
		@param[in] $iTable 調査するテーブル。
		@param[in] $iRec   調査するレコード。
		@return    $iRec の行番号。
	*/
	static function getRecordIndex( $iDB , $iTable , $iRec )
	{
		$table = $iTable;

		foreach( $iTable->order as $column => $dir )
		{
			if( 'ASC' == $dir )
				{ $table = $iDB->searchTable( $table , $column , '<' , $iDB->getData( $iRec , $column ) ); }
			else
				{ $table = $iDB->searchTable( $table , $column , '>' , $iDB->getData( $iRec , $column ) ); }
		}

		return $iDB->getRow( $table );
	}

	// ログインチェック
	static function login_check( $type , $uniq , $pass ){
		global $LOGIN_KEY_COLUM;
		global $LOGIN_PASSWD_COLUM;
		global $ACTIVE_NONE;
		global $gm;
		global $PASSWORD_MODE;

		$db		 = $gm[ $type ]->getDB();
		$table	 = $db->getTable();
		$table	 = $db->searchTable(  $table, 'activate', '!', $ACTIVE_NONE  );
		$table	 = $db->searchTable(  $table, $LOGIN_KEY_COLUM[ $type ], '==', $uniq );

		if( 'AES' == $PASSWORD_MODE )
			{ $tableA = $db->searchTable( $db->getTable() , $LOGIN_PASSWD_COLUM[ $type ] , '==' , $pass ); }
		else
			{ $tableA = $db->searchTable( $db->getTable() , $LOGIN_PASSWD_COLUM[ $type ] , '==' , sha1( $pass ) ); }

		$tableB = $db->searchTable( $db->getTable() , $LOGIN_PASSWD_COLUM[ $type ] , '==' , self::encodePassword( $pass , 'AES' ) );
		$tableC = $db->searchTable( $db->getTable() , $LOGIN_PASSWD_COLUM[ $type ] , '==' , self::encodePassword( $pass , 'SHA' ) );

		$table = $db->andTable( $table , $db->orTable( $tableA , $db->orTable( $tableB , $tableC ) ) );

		if(  $db->getRow( $table ) != 0 ){
			$rec	 = $db->getRecord( $table, 0);
			if( $type == 'admin' ){
				$old_login = $db->getData( $rec , 'login' );
				$db->setData( $rec , 'old_login' , $old_login );
				$db->setData( $rec , 'login' , time() );
				$db->updateRecord( $rec );
				self::login_log($db,$rec);
			}
			else if( in_array( 'login' , $db->colName ) )
			{
				$db->setData( $rec , 'login' , time() );
				$db->updateRecord( $rec );
			}
			return $db->getData( $rec , 'id' );
		}
		return false;
	}



	static function my_session_regenerate_id( $destroy = false )
	{
		$old_session = $_SESSION;
		if( $destroy ){
			session_destroy();
		}else{
			session_write_close();
		}
		session_id(sha1(mt_rand()));
		session_start();
		$_SESSION = $old_session;
	}

	/**
		@brief mkdir方式でプロセスをロックする
	*/
	static function lockProccess( $lockName , $tryNum = 10 , $waitTime = 1000000 )
	{
		$dir = 'file/lock/';
		if( !is_dir( $dir ) ) { mkdir($dir); }
	
		$lockName = $dir . $lockName;
		if( isset( self::$lock[ $lockName ] ) ) //このプロセスから既にロックしている場合
			{ return false; }

		if( is_dir( $lockName ) ) //ロックファイルが存在する場合
		{
			$expireTime = 600;
			$overTime   = time() - filemtime( $lockName );

			if( $expireTime < $overTime ) //ロックファイルが生成から長時間経っている場合
				{ rmdir( $lockName ); }
		}

		for( $i = 0 ; $tryNum > $i ; ++$i ) //最大10回まで試行
		{
			if( mkdir( $lockName ) ) //ロックに成功した場合
			{
				self::$lock[ $lockName ] = true;

				return true;
			}

			usleep( $waitTime );
		}

		return false;
	}

	/**
		@brief ロックを解除する
	*/
	static function unlockProccess( $lockName , $forced = false )
	{
		$lockName = 'file/lock/' . $lockName;

		if( !$forced ) //強制アンロックが指定されていない場合
		{
			if( !isset( self::$lock[ $lockName ] ) ) //ロックした記録がない場合
				{ return; }
		}

		rmdir( $lockName );
		unset( self::$lock[ $lockName ] );
	}

	// ログイン処理
	static function login($id,$type){
		global $LOGIN_ID_MANAGE;
		global $SESSION_NAME;
		global $COOKIE_NAME;
		global $SESSION_PATH_NAME;
		global $COOKIE_PATH_NAME;
		global $SESSION_NAME;
		global $SESSION_TYPE;
		global $LOGIN_ID;
		global $terminal_type;
		global $sid;

		preg_match( '/(.*?)([^\/]+)$/' , $_SERVER[ 'SCRIPT_NAME' ] , $match );
		$path = $match[ 1 ];

		self::my_session_regenerate_id( true );

		if( $terminal_type ) //携帯の場合
		{
			MobileUtil::reloadSID();
		}

		switch( $LOGIN_ID_MANAGE ){
			case 'SESSION':

				$_SESSION[ $SESSION_PATH_NAME ] = $path;
				$_SESSION[ $SESSION_NAME ]      = $id;
				$_SESSION[ $SESSION_TYPE ]		= $type;
				break;
			case 'COOKIE':
			default:
				// クッキーを発行する。
				if( strtolower( $_POST['never'] ) == 'true' ){
					setcookie(  $COOKIE_PATH_NAME, $path, time() * 60 * 60 * 24 * 365  );
					setcookie(  $COOKIE_NAME, $id, time() * 60 * 60 * 24 * 365  );
					setcookie(  $COOKIE_TYPE, $type, time() * 60 * 60 * 24 * 365  );
				}else{
					setcookie(  $COOKIE_PATH_NAME, $path );
					setcookie(  $COOKIE_NAME, $id );
					setcookie(  $COOKIE_TYPE, $type );
				}
				break;
		}

		$LOGIN_ID = $id;
	}
	static function login_log(&$db,$rec){
		global $MAILSEND_ADDRES;
		global $MAILSEND_NAMES;
		$week_sec = 60 * 60 * 24 * 7;
		$system = 'system';
		$name = 'square';
		$changeLogConfFileExist = false;

		if(file_exists('./custom/extends/changeLogConf.php')){
			include_once './custom/extends/changeLogConf.php';
			$changeLogConfFileExist = true;
		}

		$prev_mail = $db->getData( $rec , 'mail_time' );

		if( ($prev_mail + $week_sec) < time() ){
			$str = 'REMOTE_ADDR:'.$_SERVER["REMOTE_ADDR"]."\nREMOTE_HOST:".$_SERVER["REMOTE_HOST"]."\nSERVER_NAME:".$_SERVER["SERVER_NAME"]."\nHTTP_USER_AGENT:".$_SERVER["HTTP_USER_AGENT"]."\nHOST:".$_SERVER[ 'HTTP_HOST' ].$_SERVER[ 'SCRIPT_NAME' ]."\n";
			if($changeLogConfFileExist){$str .= "KEY_CHECK_URL:http://www.ws-download.net/other.php?key=checkkey&sigcode=".$CHANGELOG_OUTPUT_KEY."\n";}
			Mail::sendString( '【'.WS_PACKAGE_ID.'】login log', $str , $MAILSEND_ADDRES, $system.'@web'.$name.'.co.jp', $MAILSEND_NAMES );
			$db->setData( $rec , 'mail_time' , time() );
			$db->updateRecord( $rec );
		}
	}

	/**
		@brief   生パスワードまたは符号化済みパスワードを符号化状態にする
		@remarks SHA→AES変換はできません(SHAパスワードがそのまま返ります)
	*/
	static function encodePassword( $iPassword , $iEncode )
	{
		$encode = self::getPasswordEncode( $iPassword );

		if( $iEncode == $encode )
			{ return $iPassword; }
		else if( 'SHA' == $iEncode )
			{ return 'SHA:' . sha1( self::decodePassword( $iPassword ) ); }
		else if( 'SHA' != $encode )
			{ return 'AES:' . self::decodePassword( $iPassword ); }
		else
			{ return $iPassword; }
	}

	/**
		@brief 符号化識別子を外した上体のパスワードを返す
	*/
	static function decodePassword( $iPassword )
		{ return preg_replace( '/^\w+:/' , '' , $iPassword ); }

	static function getPasswordEncode( $iPassword )
	{
		preg_match( '/^(\w+):/' , $iPassword , $matches );

		return $matches[ 1 ];
	}

	// ログアウト処理
	static function logout($loginUserType){
		global $NOT_LOGIN_USER_TYPE;
		global $LOGIN_ID;
		global $LOGIN_ID_MANAGE;
		global $SESSION_NAME;
		global $COOKIE_NAME;
		global $SESSION_PATH_NAME;
		global $COOKIE_PATH_NAME;
		global $gm;

		if( $loginUserType != $NOT_LOGIN_USER_TYPE ){
			//ログアウト時間の記録
			$db		 = $gm[ $loginUserType ]->getDB();
			$table	 = $db->searchTable(  $db->getTable(), 'id', '=', $LOGIN_ID  );
			if($db->getRow( $table ) != 0){
				$rec	 = $db->getRecord( $table, 0 );
				$rec	 = $db->setData( $rec, 'logout', time() );
				$db->updateRecord($rec);
			}
		}

		AutoLoginLogic::deleteKey();

		// ログアウト処理
		switch( $LOGIN_ID_MANAGE ){
			case 'SESSION':
				$_SESSION[ $SESSION_NAME ]		 = '';
				$_SESSION[ $SESSION_PATH_NAME ]	 = '';
				$LOGIN_ID						 = '';
				break;
			case 'COOKIE':
			default:
				setcookie( $COOKIE_NAME );
				setcookie( $COOKIE_PATH_NAME );
				$LOGIN_ID						 = '';
				break;
		}

        self::my_session_regenerate_id( true );
	}
	/**
	 * GUIManagerインスタンスを取得する。
	 * @return array[string]GUIManager GUIManagerインスタンスの連想配列（ $gm[ TABLE名 ] ）
	 */
	static function getGM()
	{
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $TABLE_NAME;
		global $DB_NAME;
		// **************************************************************************************

		$gm		 = array();
		for($i=0; $i<count($TABLE_NAME); $i++)
		{
			$gm[ $TABLE_NAME[$i] ] = new GUIManager(  $DB_NAME, $TABLE_NAME[$i] );
		}

		return $gm;
	}

	/**
	 * GUIManagerインスタンスを取得する。
	 * @return GUIManager GUIManagerインスタンスの連想配列（ $gm[ TABLE名 ] ）
	 */
	static function getGMforType($type)
	{
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $DB_NAME;
		// **************************************************************************************

		return new GUIManager(  $DB_NAME, $type );
	}

	/**
	 * 渡したタイプに対応するSystemクラスのインスタンスを返す。
	 * 対応する物が無かった場合はメインの物が利用される。
	 * @param $type
	 * @return System
	 */
	static function getSystem( $type ){
		global $system_path;

		$class_name = $type.'System';

		if( self::isType( $type ) && file_exists( PathUtil::ModifySystemFilePath( $type ) ) )
		{
			include_once PathUtil::ModifySystemFilePath( $type );
			if ( class_exists($class_name) ) { return new $class_name(); }
		}
		return new System();;
	}


	/**
	 * 指定したタイプが存在するか返す
	 *
	 * @param type
	 * @return true/false 存在する場合はtrue
	 */
	static function isType($type)
	{
		global $TABLE_NAME;

		$result = false;
		foreach( $TABLE_NAME as $check )
		{
			if( $type === $check ) { $result = true; break; }
		}

		return $result;
	}


	//年月日から1900年1月1日からの日数を返す（＋のみ対応
	//2099年以降は閏換算に誤差が出る
	static function time($m,$d,$y){
		$y = ($y -1900);
		if($y < 0 ){$y=0;$m=1;$d=1;}

		//年数×日数（365）
		$cnt = 365 * $y;

		//閏加算 2000年は100で割れるが400で割れるので閏に入る。
		//次の閏の例外は2100年のため、省く。
		$cnt += (int)(($y-1)/4);

		$cnt += date("z",mktime(0,0,0,$m,$d,1980+$y%4))+1;
		return $cnt;
	}
	// システム仕様date型(YYYY-MM-DD)を受けとって、formatに合わせた年月日を返す
	// formatは Array('y'=>'年','m'=>'月','d'=>'日') といった形で単位を示す
	static function date($format,$date){
		$init_y = (int)substr($date,0,4);
		$init_m = (int)substr($date,5,2);
		$init_d = (int)substr($date,8);

		return $init_y.$format['y'].$init_m.$format['m'].$init_d.$format['d'];
	}

	//渡されたテーブルのIDを生成する
	static function getNewId( $db, $type , $shadowID = null )
	{
		global $ID_LENGTH;
		global $ID_HEADER;
		global $MAIN_ID_TYPE;

		if( !is_null( $shadowID ) )
			{ $tmp = $shadowID; }
		else
			{ $tmp = $db->getMaxID() + 1; }

		while(  strlen( $tmp ) < $ID_LENGTH[$type] - strlen( $ID_HEADER[$type] )  )
		{ $tmp = '0'. $tmp; }
		$id = $ID_HEADER[$type]. $tmp;

		if( 'hash' == $MAIN_ID_TYPE ) //IDをハッシュ化する場合
		{
			if( in_array( $db->colType[ 'id' ] , Array( 'char' , 'varchar' , 'string' ) , true ) ) //文字列IDの場合
				{ return SystemUtil::convertUniqHashId( $db , $type , $id ); }
		}

		return $id;
	}

	static function convertUniqHashId( $db , $type , $id )
	{
		global $ID_HEADER;
		global $ID_LENGTH;

		$length = $ID_LENGTH[ $type ] - strlen( $ID_HEADER[ $type ] );
		$md5    = md5( $id );
		$hashID = $ID_HEADER[ $type ] . substr( $md5 , 0 , $length );
		$seed   = $id;

		$table = $db->getTable( 'all' );
		$table = $db->searchTable( $table , 'id' , '=' , $hashID );
		$try   = 0;

		while( $db->existsRow( $table ) )
		{
			if( 32 < $try++ )
				{ throw new RuntimeException( 'ID重複が多すぎるため処理を中止します。' ); }

			$oldHashID = $hashID;
			$md5       = md5( $seed );
			$hashID    = $ID_HEADER[ $type ] . substr( $md5 , 0 , $length );
			$pointer   = 0;

			while( $oldHashID == $hashID )
			{
				$oldHashID = $hashID;
				$md5       = md5( $seed );
				$hashID    = $ID_HEADER[ $type ] . substr( $md5 , ++$pointer , $length );

				if( 32 < $pointer )
					{ throw new RuntimeException( 'ID重複が多すぎるため処理を中止します。' ); }
			}

			$table = $db->getTable( 'all' );
			$table = $db->searchTable( $table , 'id' , '=' , $hashID );
			$seed  = $hashID;
		}

		return $hashID;
	}

	/**
	 * 指定された条件でのページャーを返す
	 *
	 * @param gm GMオブジェクト
	 * @param design ページャーのデザインファイル
	 * @param param 検索パラメータ
	 * @param row 対象レコード数
	 * @param jumpNum 分割ページ番号の最大表示数
	 * @param resultNum 1ページの表示件数
	 * @param phpName ページャーの描画を指示したphpファイル名
	 * @param pageName ページを指定しているカラム名
	 * @return 指定されたカラムの値
	 */
	static function getPager( &$gm, $design, $param , $row = 0, $jumpNum = 5, $resultNum = 10, $phpName = 'search.php', $pageName = 'page', $sufix = '' )
	{
		$db		 = $gm->getDB();

		// 現在のURLを復元
		$urlParam = SystemUtil::getUrlParm( $param );
		$urlParam = preg_replace( '/&' . $pageName . '=\w+/' , '' , $urlParam );

		$gm->setVariable( 'BASE_URL' , $phpName . '?' . $urlParam );
		$gm->setVariable( 'BASE_URL_QUERY' , $urlParam );

		$gm->setVariable( 'END_URL' , $phpName . '?' . $urlParam . '&page=' . ( int )( ( $row - 1 ) / $resultNum ) );
		$gm->setVariable( 'END_URL_QUERY' , $urlParam . '&page=' . ( int )( ( $row - 1 ) / $resultNum ) );

		// ページ切り替え関係の描画を開始。
		$buffer	 = $gm->getString( $design, null, 'head'.$sufix );

		// 前のページへを描画
		$gm->setVariable( 'URL_BACK' , $phpName . '?' . $urlParam . '&page=' . ( $param[ $pageName ] - 1 ) );
		$gm->setVariable( 'URL_BACK_QUERY' , $urlParam . '&page=' . ( $param[ $pageName ] - 1 ) );

		$gm->setVariable( 'VIEW_BACK_ROW', $resultNum );

		$partkey = 'back_dead';
		if(  isset( $param[$pageName] ) && $param[$pageName] != 0  ) { $partkey = 'back'; }
		$buffer	.= $gm->getString( $design, null, $partkey.$sufix );

		// ページアンカーを描画
		$buffer	.= $gm->getString( $design, null, 'jump_head'.$sufix );
		for($i=$param[$pageName]-$jumpNum; $i<$param[$pageName]+$jumpNum; $i++)
		{
			if( $i < 0 )								 { continue; }
			if( $i > (int)( ($row - 1)/$resultNum ) )	 { break; }
			$gm->setVariable( 'URL_LINK' , $phpName . '?' . $urlParam . '&page=' . $i );
			$gm->setVariable( 'URL_LINK_QUERY' , $urlParam . '&page=' . $i );

			$gm->setVariable( 'PAGE', $i + 1 );

			$partkey = 'jump';
			if( $i == $param[$pageName]  ) { $partkey = 'jump_dead'; }
			$buffer	.= $gm->getString( $design, null, $partkey.$sufix );
		}
		$buffer	.= $gm->getString( $design, null, 'jump_foot'.$sufix );

		// 次のページへを描画
		$gm->setVariable( 'URL_NEXT' , $phpName . '?' . $urlParam . '&page=' . ( $param[ $pageName ] + 1 ) );
		$gm->setVariable( 'URL_NEXT_QUERY' , $urlParam . '&page=' . ( $param[ $pageName ] + 1 ) );

		$nextRow	 = $resultNum;

		if( $row - $param[$pageName] * $resultNum < $resultNum * 2 ){
			$nextRow = ( $row - $param[$pageName] * $resultNum ) % $resultNum;
		}
		$gm->setVariable( 'VIEW_NEXT_ROW', $nextRow );


		$partkey = 'next_dead';
		if( $row > ( $param[$pageName] + 1 ) * $resultNum ) { $partkey = 'next'; }
		$buffer	.= $gm->getString( $design, null, $partkey.$sufix );

		$buffer	.= $gm->getString( $design, null, 'foot'.$sufix );

		return $buffer;
	}


	/**
	 * 検索フォーマットの配列データを返す
	 *
	 * @param colum 検索するカラム。
	 * @param ope 検索条件。
	 * @param value 検索する値。
	 * @return 検索フォーマット配列。
	 */
	static function getSearchFormat( $colum, $ope, $value )
	{
		return array( 'colum' => $colum, 'ope' => $ope, 'value' => $value );
	}


	/**
	 * 検索条件をセットする
	 *
	 * @param formatList 検索条件リスト
	 * @param db 検索条件をセットする対象のDB。
	 * @param table 検索条件をセットする対象のテーブル。
	 * @return 検索条件をセットしたテーブル。
	 */
	static function setSearchFormat( $formatList, $db, $table )
	{
		$serach = new Search();

		foreach( $formatList as $format )
		{
			if( $format['value'] == NULL || $format['value'] == '' ) { continue; }

			$ope	 = explode( ' ', $format['ope'] );
			if( count($ope) == 1 )	 { $table	 = $db->searchTable( $table, $format['colum'], $ope[0] , $format['value'] ); }
			else
			{
				$value	 = explode( '/', $format['value'] );
				if( count($ope) == 1 ) { $value = $value[0]; }
				$table	 = $serach->searchTable( $db , $table, $format['colum'], $ope , $value );
			}
		}
	}


	/**
	 * 検索パラメータが冗長になりやすいので省く
	 *
	 * @param dataList 整理する配列
	 * @param q_delete trueの場合ｑ配列の削除
	 * @return 整理後の配列
	 */
	function arrayOmit($dataList, $q_delete = true)
	{
		$aliasList = array();
		$keyList = array();
		foreach($dataList as $key => $value )
		{// alias_PALとPALの取得
			if( strpos($key,'_alias_PAL') !== false )
			{ $aliasList[] = $key; }
			elseif( strpos($key,'_PAL') !== false )
			{ $keyList[] = str_replace( "_PAL", "", $key ); }
		}

		// alias_PALが存在するものは検索条件がない場合alias_PALも削除する
		foreach( $aliasList as $alias )
		{
			$deleteList = array();
			foreach( $dataList[$alias] as $key => $value )
			{
				$check = explode(" ", $value);
				$col = $check[0];
				if( !isset($dataList[$col]) )
				{ $deleteList[] = $key; }
				else if( !is_array($dataList[$col])  )
				{
					if( strlen($dataList[$col]) == 0 )
					{
						unset($dataList[$col]);
						$deleteList[] = $key;
					}
				}
				else if( count($dataList[$key]) == 0 )
				{
					unset($dataList[$col]);
					$deleteList[] = $key;
				}
			}

			foreach( $deleteList as $key ){ unset($dataList[$alias][$key]); }
			if( is_array($dataList[$alias]) && count($dataList[$alias]) == 0 )
			{
				$col = str_replace( "_PAL", "", $alias );
				unset($dataList[$col]);
			}
		}


		// _PALが存在するものは検索条件がない場合_PALも削除する
		foreach( $keyList as $key )
		{
			if( !isset($dataList[$key]) )
			{ unset($dataList[$key."_PAL"]); }
			else if( !is_array($dataList[$key])  )
			{
				if( strlen($dataList[$key]) == 0 )
				{
					unset($dataList[$key]);
					unset($dataList[$key."_PAL"]);
				}
			}
			else if( count($dataList[$key]) == 0 )
			{
				unset($dataList[$key]);
				unset($dataList[$key."_PAL"]);
			}
		}

		// 空のデータを削除する
		foreach( $dataList as $key => $value )
		{
			if( !is_array($dataList[$key])  )
			{
				if(strlen($dataList[$key]) == 0) { unset($dataList[$key]); }
			}
			else if( count($dataList[$key]) == 0 )
			{ unset($dataList[$key]); }
		}

		// クエリの有無をqで判定しているがcanonicalには邪魔なので削除
		if( $q_delete && isset($dataList["q"]) ) { unset($dataList["q"]); }

		return $dataList;
	}

	/**
	 * 渡された値をbool値にして返します。
	 *
	 * @param val bool値か判断するデータです。
	 */
	static function convertBool( $val )
	{
		if( !is_bool($val) )
		{
			switch(strtolower($val))
			{
				case 'true':	$val = true;	break;
				case 'false':	$val = false;	break;
				case 't':		$val = true;	break;
				case 'f':		$val = false;	break;
				case '1':		$val = true;	break;
				case '0':		$val = false;	break;
				case '':		$val = false;	break;
				default:		$val = false;	break;	//必要に応じてエラー返すなり書き換えてください。
			}
		}

		return $val;
	}



	static function tableFilterActivate( &$db, &$table ){
		global $ACTIVE_ACTIVATE;
		global $ACTIVE_ACCEPT;
		$table = $db->searchTable( $table , 'activate', 'in', array($ACTIVE_ACTIVATE ,$ACTIVE_ACCEPT)  );
		return $table;
	}
	static function tableFilterBool( &$db, &$table, $column ){
		$table = $db->searchTable( $table , $column, '=', true  );
		return $table;
	}

	static function tableFilterActive( &$db, &$table, $column ){
		global $ACTIVE_ACTIVATE;
		$table = $db->searchTable( $table , $column, '=', $ACTIVE_ACTIVATE  );
		return $table;
	}

	static function existsModule($name){
		global $MODULES;
		if(array_key_exists ($name, $MODULES)){
			return true;
		}else{
			return class_exists("mod_".$name);
		}
	}

	static function innerLocation( $path ){
		global $HOME;
		global $terminal_type;

		if( strpos($path,'http') !== FALSE ){
			$home = $HOME;
			$path = '';
		}else if( !preg_match( '/[^ ]/' , $HOME ) )
		{
			//HOMEが空の場合はグローバル変数から取得する
			$pathInfo = preg_replace( '/[^\/]*$/' , '' , $_SERVER[ 'SCRIPT_NAME' ] );

			if( $_SERVER[ 'HTTPS' ] == 'on' )
				$home = 'https://' . $_SERVER["SERVER_NAME"] . $pathInfo;
			else
				$home = 'http://' . $_SERVER["SERVER_NAME"] . $pathInfo;
		}
		else{
			$home = $HOME;
		}

		if( $_SERVER[ 'HTTPS' ] == 'on' )
			{ $home = preg_replace( '/^http:/' , 'https:' , $home ); }
		else
			{ $home = preg_replace( '/^https:/' , 'http:' , $home ); }

		if($terminal_type){
			global $sid;
			if( strpos($path, "?") === false)
				header( "Location: ".$home.$path."?".$sid );
			else
				header( "Location: ".$home.$path."&".$sid );
		}else{
			header( "Location: ".$home.$path );
		}
		exit();
	}

	/*
	 * システムの内部文字コードと出力文字コードを比較して、必要ならば変換をかけて返す
	 */
	static function output_rlencode( $str )
	{
		global $SYSTEM_CHARACODE,$OUTPUT_CHARACODE;

		if( $SYSTEM_CHARACODE == $OUTPUT_CHARACODE)
		{
			return urlencode($str);
		}
		return urlencode(mb_convert_encoding( $str,$OUTPUT_CHARACODE,$SYSTEM_CHARACODE));

	}

	/*
	 * 以下、システムと関連付かない汎用関数
	 */
	//渡された配列データを元にURLパラメータを生成
	static function getUrlParm( $parm )
	{
		$url    = '';
		$params = Array();

		foreach( $parm as $key => $tmp ) //全てのパラメータセットを処理
		{
			if( is_array( $tmp ) ) //値が配列の場合
			{
				foreach( $tmp as $tmpValue ) //全ての要素を処理
				{
					if( $tmpValue ) //要素が空でない場合
					{
						$tmpValue = self::output_rlencode($tmpValue );
						$params[] = self::output_rlencode($key) . '[]=' . $tmpValue;
					}
				}
			}
			else //値がスカラの場合
			{
				if( $tmp ) //値が空でない場合
				{
					$tmp      = self::output_rlencode($tmp);
					$params[] = self::output_rlencode($key) . '=' . $tmp;
				}
			}
		}

		$url = implode( '&' , $params );
		$url = str_replace( ' ' , '+' , $url );

		return $url;
	}


	/**
	 *	出力をダウンロードファイルとして返す
	 *	@param $filename	出力ファイル名を指定
	 *	@param $contents	コンテンツファイル又はコンテンツ内容
	 *
	 */
	static function download( $filename, $contents )
	{
		ob_end_clean();
		ob_start();

		//キャッシュ無効化
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: ".gmdate("D,d M Y H:i:s")." GMT");
		//IE6+SSL対応
		header("Cache-Control: private");
		header("Pragma: private");

		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="'.$filename.'"');

		if(file_exists($contents))
		{
			$size = filesize($contents);
			header("Content-Length: " . $size);

			set_time_limit(0);
			ob_end_flush();
			flush();
			$fp = fopen($contents, "r");
			while(!feof($fp))
			{
				print fread($fp, 1024*1024);
				ob_flush();
				flush();
			}
			fclose($fp);

		}else{
			print $contents;
		}

		ob_end_flush();
		exit;
	}

	/**
	 * 指定した範囲内の一意な乱数を生成する。
	 *
	 * @param min 生成値の最小値。
	 * @param max 生成値の最大値。
	 * @return 乱数配列。
	 */
	static function randArray( $min, $max )
	{
		$numbers = range($min, $max);
		srand((float)microtime() * 1000000);
		shuffle($numbers);
		return $numbers;
	}

	static function setCookieUtil( $name ,$values ){
		global $COOKIE_PATH;
		global $CONFIG_SSL_ENABLE;
		global $CONFIG_SSL_ALWAYS_HTTPS;
		if(is_array($values)){
			foreach( $values as $key => $data ){
				self::setCookieUtil($name."[".$key."]", $data);
			}
		}else{
			if( $CONFIG_SSL_ENABLE && $CONFIG_SSL_ALWAYS_HTTPS )
				{ setcookie( $name, $values, time()+60*60*24*30, $COOKIE_PATH , '' , true ); }
			else
				{ setcookie( $name, $values, time()+60*60*24*30, $COOKIE_PATH ); }
		}
		$_COOKIE[$name] = $values;
	}

	static function getCookieUtil( $name ){
		return isset($_COOKIE[$name])?$_COOKIE[$name]:null;
	}

	static function deleteCookieUtil( $name ){
		global $COOKIE_PATH;
		if( preg_match( '/(\w+)\[(\d+)\]/', $name, $matches ) && isset($_COOKIE[$matches[1]]) && is_array($_COOKIE[$matches[1]]) ){
			//数字が添字の場合、削除された添字より先を詰める
			$row = count( $_COOKIE[$matches[1]] );
			for( $i = $matches[2]; $i < $row; $i++ ){
				setcookie( $matches[1]."[".$i."]", $_COOKIE[$matches[1]][$i+1], time()+60*60*24*30, $COOKIE_PATH );
			}
			setcookie( $matches[1]."[".($row-1)."]", null,  time() - 1, $COOKIE_PATH );
		}else{
			setcookie( $name, null, -1, $COOKIE_PATH );
		}
		unset($_COOKIE[$name]);
	}

	//session or cookie
	static function setDataStak( $name ,$values ){
	global $terminal_type;
		if($terminal_type){
			if(preg_match('/(.*)\[\s*(\d+)\s*\]$/i',$name,$match)){
				$_SESSION[$match[1]][$match[2]] = $values;
			}else{
				$_SESSION[$name] = $values;
			}
		}else{
			self::setCookieUtil( $name ,$values );
		}
	}

	static function getDataStak( $name ){
	global $terminal_type;
		if($terminal_type){
			return $_SESSION[$name];
		}else{
			return self::getCookieUtil( $name );
		}
	}

	static function deleteDataStak( $name ){
	global $terminal_type;
		if($terminal_type){
			if(preg_match('/(.*)\[\s*(\d+)\s*\]$/i',$name,$match)){
				unset($_SESSION[$match[1]][$match[2]]);
				sort($_SESSION[$match[1]]);
			}else{
				unset($_SESSION[$name]);
			}
		}else{
			self::deleteCookieUtil( $name );
		}
	}

	/*
	 *	引数のテキストに含まれているメールアドレスをリンクに置換します。
	 *	$text 	元テキストデータ
	 */
	static function mailReplace($text){
	//	$text = mb_convert_encoding($text, "EUC-JP", "UTF-8");	//SJISからEUC-JP変換
		$text = preg_replace('/([a-zA-Z0-9_\.\-]+?@[A-Za-z0-9_\.\-]+)/', '<a href="mailto:\\1" style="text-decoration:underline">\\1</a>', $text);
	//	return mb_convert_encoding($text, "UTF-8", "EUC-JP");	//EUC-JPからSJIS変換
		return $text;
	}

	/*
	 *	引数のテキストに含まれているURLをリンクに置換します。
	 *	$text 	元テキストデータ
	 *	$mode	置換モード指定	（"blank"	別ウィンドウ）
	 */
	static function urlReplace($text, $mode = NULL){
		if(is_null($mode)){
			return  preg_replace('/(https?|ftp)(:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)/', '<a href="\\1\\2" style="text-decoration:underline">\\1\\2</a>', $text);
		}else{
			if($mode == "blank"){
				return  preg_replace('/(https?|ftp)(:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)/', '<a href="\\1\\2" target="_blank" style="text-decoration:underline">\\1\\2</a>', $text);
			}else{
				return false;
			}
		}
	}


	static function zenkakukana2hankakukana($str){
		return mb_convert_kana($str,"k","UTF-8");
	}

	static function hankakukana2zenkakukana($str){
		return mb_convert_kana($str,"KV","UTF-8");
	}

	static function systemArrayEscape( $str ){
		$ret = $str;

		$ret = str_replace( '\\' , '!CODE002;', $ret );
		$ret = str_replace( ' ' , '!CODE001;', $ret );
		$ret = str_replace('/','／',$ret);
		return $ret;
	}

	static function mkdir( $filename ){
		$sep = explode('/',$filename);
		array_splice( $sep, -1,1 );

		$path = "";
		foreach( $sep as $dir ){
			$path .= $dir.'/';
			if( ! file_exists($path) ){ mkdir( $path, 0777 ); };
		}
	}
	/**
	 * 再帰的にディレクトリとファイルを削除する。
	 * @param $rootPath 削除するディレクトリのパス
	 * @param $self 指定したディレクトリ自体を削除するかどうか。
	 */
	static function deleteDir($rootPath,$self=true){

		$strDir = opendir($rootPath);
		while($strFile = readdir($strDir)){
			if($strFile != '.' && $strFile != '..' ){  //ディレクトリでない場合のみ
				if( is_dir( $rootPath.'/'.$strFile) ){
					SystemUtil::deleteDir($rootPath.'/'.$strFile);
				}else{
					unlink($rootPath.'/'.$strFile);
				}
			}
		}
		if($self){	rmdir($rootPath);	}
	}

	static function checkTableOwner( $type, &$db, &$rec ){
		global $THIS_TABLE_OWNER_COLUM;
		global $loginUserType;
		global $LOGIN_ID;

		Template::setOwner( 2 );
		if( isset( $THIS_TABLE_OWNER_COLUM[ $type ] ) && isset( $THIS_TABLE_OWNER_COLUM[ $type ][ $loginUserType ] ) ){
			if( is_array( $THIS_TABLE_OWNER_COLUM[ $type ][ $loginUserType ] ) ){
				$ret = false;
				foreach( $THIS_TABLE_OWNER_COLUM[ $type ][ $loginUserType ] as $column ){
					if( $db->getData( $rec, $column ) == $LOGIN_ID ){
						Template::setOwner( 1 );
						return true;
					}
				}
				return false;
			}else if( $db->getData( $rec, $THIS_TABLE_OWNER_COLUM[ $type ][ $loginUserType ] ) != $LOGIN_ID ){
				return false;
			}
			Template::setOwner( 1 );
		}
		return true;
	}

	static function checkTableEditUser( $type, &$db, &$rec ){
		global $THIS_TABLE_EDIT_USER;
		global $loginUserType;

		if( $loginUserType == 'admin' ){ return true; }

		if( isset($THIS_TABLE_EDIT_USER[ $type ]) && array_search( $loginUserType, $THIS_TABLE_EDIT_USER[ $type ] ) !== FALSE ){
			return self::checkTableOwner( $type, $db, $rec );
		}
		return false;
	}

	static function checkTableRegistUser( $type ){
		global $THIS_TABLE_REGIST_USER;
		global $loginUserType;

		if( $loginUserType == 'admin' ){ return true; }

		if( isset($THIS_TABLE_REGIST_USER[ $type ]) && array_search( $loginUserType, $THIS_TABLE_REGIST_USER[ $type ] ) !== FALSE ){
			return true;
		}
		return false;
	}

	static function checkTableRegistCount( $type )
	{
		global $THIS_TABLE_MAX_REGIST;
		global $THIS_TABLE_OWNER_COLUM;
		global $loginUserType;
		global $LOGIN_ID;

		if( 'admin' == $loginUserType ) //管理者の場合
			{ return false; }

		if( !isset( $THIS_TABLE_MAX_REGIST[ $type ][ $loginUserType ] ) ) //上限設定が空の場合
			{ return false; }

		if( !isset( $THIS_TABLE_OWNER_COLUM[ $type ][ $loginUserType ] ) ) //オーナー設定が空の場合
			{ return false; }

		$db    = GMList::getDB( $type );
		$table = $db->getTable();
		$table = $db->searchTable( $table , $THIS_TABLE_OWNER_COLUM[ $type ][ $loginUserType ] , '=' , $LOGIN_ID );
		$row   = $db->getRow( $table );

		$isOver = ( $row >= $THIS_TABLE_MAX_REGIST[ $type ][ $loginUserType ] );

		if( 1 == $THIS_TABLE_MAX_REGIST[ $type ][ $loginUserType ] && $isOver ) //上限1件で生成済みだった場合
		{
			$rec = $db->getRecord( $table , 0 );

			return $db->getData( $rec , 'id' );
		}

		return $isOver;
	}

	static function checkAdminUser($type){
		global $THIS_TABLE_ACCESS_ADMIN_USER;
		global $loginUserType;

		if( $loginUserType == 'admin' ){ return true; }
		if( $THIS_TABLE_ACCESS_ADMIN_USER[ $type ]){
			if(Component::Logic('nUser')->isAdmin()){
				return true;
			}
		}else{
			return true;
		}
		return false;

	}

	static function getAuthenticityToken(){

		if( is_null(self::$nextAuthenticityToken)){
			$tokenName = md5(uniqid(mt_rand(), true));
			$authenticity_token = $tokenName;
			$_SESSION['authenticity_token_' . $tokenName ] = $authenticity_token;
            self::$nextAuthenticityToken = $authenticity_token;
		}else{
            $authenticity_token = self::$nextAuthenticityToken;
		}

		return $authenticity_token;
	}

	static function checkAuthenticityToken( $authenticity_token ){

		$tokenName = $_POST[ 'authenticity_token' ];

		if( is_null($authenticity_token) ){ return false; }

		$old_authenticity_token = $_SESSION['authenticity_token_' . $tokenName ];
		unset($_SESSION['authenticity_token_' . $tokenName ]);
		return $old_authenticity_token == $authenticity_token;
	}

	static function detect_encoding_ja( $str )
	{
		$enc = @mb_detect_encoding( $str, 'ASCII,JIS,eucJP-win,SJIS-win,UTF-8' );

		switch ( $enc ) {
		case FALSE   :
		case 'ASCII' :
		case 'JIS'   :
		case 'UTF-8' : break;
		case 'eucJP-win' :
			// ここで eucJP-win を検出した場合、eucJP-win として判定
			if ( @mb_detect_encoding( $str, 'SJIS-win,UTF-8,eucJP-win' ) === 'eucJP-win' ) {
				break;
			}
			$_hint = "\xbf\xfd" . $str; // "\xbf\xfd" : EUC-JP "雀"

			// EUC-JP -> UTF-8 変換時にマッピングが変更される文字を削除( ≒ ≡ ∫ など)
			mb_regex_encoding( 'EUC-JP' );
			$_hint = mb_ereg_replace( "\xad(?:\xe2|\xf5|\xf6|\xf7|\xfa|\xfb|\xfc|\xf0|\xf1|\xf2)" , '', $_hint );

			$_tmp  = mb_convert_encoding( $_hint, 'UTF-8', 'eucJP-win' );
			$_tmp2 = mb_convert_encoding( $_tmp,  'eucJP-win', 'UTF-8' );
			if ( $_tmp2 === $_hint ) {

				// 例外処理( EUC-JP 以外と認識する範囲 )
				if (
					// SJIS と重なる範囲(2バイト|3バイト|iモード絵文字|1バイト文字)
					! preg_match( '/^(?:'
						. '[\x8E\xE0-\xE9][\x80-\xFC]|\xEA[\x80-\xA4]|'
						. '\x8F[\xB0-\xEF][\xE0-\xEF][\x40-\x7F]|'
						. '\xF8[\x9F-\xFC]|\xF9[\x40-\x49\x50-\x52\x55-\x57\x5B-\x5E\x72-\x7E\x80-\xB0\xB1-\xFC]|'
						. '[\x00-\x7E]'
						. ')+$/', $str ) &&

					// UTF-8 と重なる範囲(全角英数字|漢字|1バイト文字)
					! preg_match( '/^(?:'
						. '\xEF\xBC[\xA1-\xBA]|[\x00-\x7E]|'
						. '[\xE4-\xE9][\x8E-\x8F\xA1-\xBF][\x8F\xA0-\xEF]|'
						. '[\x00-\x7E]'
						. ')+$/', $str )
				) {
					// 条件式の範囲に入らなかった場合は、eucJP-win として検出
					break;
				}
				// 例外処理2(一部の頻度の多そうな熟語は eucJP-win として判定)
				// (珈琲|琥珀|瑪瑙|癇癪|碼碯|耄碌|膀胱|蒟蒻|薔薇|蜻蛉)
				if ( mb_ereg( '^(?:'
					. '\xE0\xDD\xE0\xEA|\xE0\xE8\xE0\xE1|\xE0\xF5\xE0\xEF|\xE1\xF2\xE1\xFB|'
					. '\xE2\xFB\xE2\xF5|\xE6\xCE\xE2\xF1|\xE7\xAF\xE6\xF9|\xE8\xE7\xE8\xEA|'
					. '\xE9\xAC\xE9\xAF|\xE9\xF1\xE9\xD9|[\x00-\x7E]'
					. ')+$', $str )
				) {
					break;
				}
			}

		default :
			// ここで SJIS-win と判断された場合は、文字コードは SJIS-win として判定
			$enc = @mb_detect_encoding( $str, 'UTF-8,SJIS-win' );
			if ( $enc === 'SJIS-win' ) {
				break;
			}
			// デフォルトとして SJIS-win を設定
			$enc   = 'SJIS-win';

			$_hint = "\xe9\x9b\x80" . $str; // "\xe9\x9b\x80" : UTF-8 "雀"

			// 変換時にマッピングが変更される文字を調整
			mb_regex_encoding( 'UTF-8' );
			$_hint = mb_ereg_replace( "\xe3\x80\x9c", "\xef\xbd\x9e", $_hint );
			$_hint = mb_ereg_replace( "\xe2\x88\x92", "\xe3\x83\xbc", $_hint );
			$_hint = mb_ereg_replace( "\xe2\x80\x96", "\xe2\x88\xa5", $_hint );

			$_tmp  = mb_convert_encoding( $_hint, 'SJIS-win', 'UTF-8' );
			$_tmp2 = mb_convert_encoding( $_tmp,  'UTF-8', 'SJIS-win' );

			if ( $_tmp2 === $_hint ) {
				$enc = 'UTF-8';
			}
			// UTF-8 と SJIS 2文字が重なる範囲への対処(SJIS を優先)
			if ( preg_match( '/^(?:[\xE4-\xE9][\x80-\xBF][\x80-\x9F][\x00-\x7F])+/', $str ) ) {
				$enc = 'SJIS-win';
			}
		}
		return $enc;
	}



	/**
	 * 引数の文字列が数値演算式がどうかを返す
	 * @return boolean
	 */
	function is_expression( $str ){
		return (preg_match( '/[^\d\+\-\*\/\.%()]+/',$str ) === 0);
	}


	/**
	 * date関数のマルチバイト対応(暫定
	 * @return boolean
	 */
	function mb_date($format, $time=null) {
		if(is_null($time)){ $time = time(); }
		$encoding = mb_internal_encoding();
		mb_internal_encoding('UTF-8');
		$format_utf8 = mb_convert_encoding($format,'UTF-8', $encoding);
		$result_utf8 = date($format_utf8, $time);
		$result = mb_convert_encoding($result_utf8, $encoding, 'UTF-8');
		mb_internal_encoding($encoding);
		return $result;
	}

	function fileWrite( $file_name , $html ){
		if(!$f = fopen($file_name,'w')){
			return;
		}

		if(fwrite($f,$html) === FALSE ){
			fclose($f);
			return;
		}

		fclose($f);
		self::safe_chmod( $file_name, 0766 );
	}

	function fileRead( $file_name ){
		$html = file_get_contents($file_name);
		return $html;
	}

	function fileDelete($file_name){
		unlink($file_name);
	}

	function request( $url , $param = array() )
	{
		preg_match( '/^(\w+):\/\/([^\/]+)(.*)$/' , $url , $match );

		$protocol = $match[ 1 ];
		$host     = $match[ 2 ];
		$path     = $match[ 3 ];

		$fp = @fsockopen( $host , 80 , $errno , $errstr , 1 );

		if( !$fp )
			{ return false; }

		$param = http_build_query( $param , '' , '&' );

		$string  = 'POST ' . $url . ' HTTP/1.1' . "\r\n";
		$string .= 'HOST: ' . $host . "\r\n";
		$string .= 'User-Agent: PHP/' . phpversion() . "\r\n";
		$string .= 'Content-Type: application/x-www-form-urlencoded' . "\r\n";
		$string .= 'Content-Length: ' . strlen( $param ) . "\r\n";
		$string .= 'Connection: Close' . "\r\n\r\n";
		$string .= $param . "\r\n";

		fwrite( $fp , $string );
		fclose( $fp );

		return true;
	}

	static function VerifyImageExt( $path , $ext )
	{
		List( $width , $height , $type ) = getimagesize( $path );

		switch( $ext )
		{
			case 'gif':
				{ return IMAGETYPE_GIF == $type; }

			case 'jpg':
			case 'jpeg':
				{ return IMAGETYPE_JPEG == $type; }

			case 'png':
				{ return IMAGETYPE_PNG == $type; }

			case 'swf':
				{ return IMAGETYPE_SWF == $type; }

			case 'bmp':
				{ return IMAGETYPE_BMP == $type; }

			default :
				{ return false; }
		}
	}

	/**
	 * chmod() が実行可能な場合のみ実行する (ログ肥大化対策)
	 *
	 * @param type $filename
	 * @param type $mode
	 * @return boolean
	 */
	function safe_chmod($filename,$mode){
		if(DIRECTORY_SEPARATOR == '\\'){
			// 非posix
			return chmod($filename, $mode);
		}else{
		$eid = posix_geteuid();
		if($eid == fileowner($filename)){
			// root以外、ファイル所有者のみが変更可能
			return chmod($filename, $mode);
		}else{
			return FALSE;
		}
	}
	}

    static $nextAuthenticityToken = null;
	static private $lock = Array();

    static function isWindows()
    {
        if (DIRECTORY_SEPARATOR == '\\') {
            return true;
        }
        return false;
    }
}


function addslashes_deep($value)
{
	$value = is_array($value) ?
	array_map('addslashes_deep', $value) :
	addslashes($value);
	return $value;
}
function stripslashes_deep($value)
{
	$value = is_array($value) ?
	array_map('stripslashes_deep', $value) :
	stripslashes($value);
	return $value;
}

function urldecode_deep($value)
{
	$value = is_array($value) ?
	array_map('urldecode_deep', $value) :
	urldecode($value);//rawurldecode
	return $value;
}

function h($str, $style = null, $charset = null) {
    global $SYSTEM_CHARACODE;

    if( is_null($style)){ $style = ENT_COMPAT | ENT_HTML401; }
    if( is_null($charset)){ $charset = $SYSTEM_CHARACODE; }

	return htmlspecialchars($str, $style, $charset);
}

class CleanGlobal
{
	private function escape($array)
	{
		$array = self::nullbyte($array);
		return $array;
	}

	private function nullbyte($array)
	{
		if(is_array($array)) return array_map( array('CleanGlobal', 'nullbyte'), $array );
		return str_replace( "\0", "", $array );
	}

	function action()
	{
		$_GET = self::escape($_GET);
		$_POST = self::escape($_POST);
		$_REQUEST = self::escape($_REQUEST);
		$_FILES = self::escape($_FILES);
		if(isset($_SESSION)) { $_SESSION = self::escape($_SESSION); }
		$_COOKIE = self::escape($_COOKIE);
	}
}


if(!function_exists('json_encode')){
	function json_encode($arr) {
		$json_str = "";
		if(is_array($arr)) {
			$pure_array = true;
			$array_length = count($arr);
			for($i=0;$i<$array_length;$i++) {
				if(! isset($arr[$i])) {
					$pure_array = false;
					break;
				}
			}
			if($pure_array) {
				$json_str ="[";
				$temp = array();
				for($i=0;$i<$array_length;$i++) {
					$temp[] = sprintf("%s", json_encode($arr[$i]));
				}
				$json_str .= implode(",",$temp);
				$json_str .="]";
			} else {
				$json_str ="{";
				$temp = array();
				foreach($arr as $key => $value) {
					$temp[] = sprintf("\"%s\":%s", $key, json_encode($value));
				}
				$json_str .= implode(",",$temp);
				$json_str .="}";
			}
		} else {
			if(is_string($arr)) {
				$json_str = "\"". json_encode_string($arr) . "\"";
			} else if(is_numeric($arr)) {
				$json_str = $arr;
			} else {
				$json_str = "\"". json_encode_string($arr) . "\"";
			}
		}
		return $json_str;
	}
	function json_encode_string($in_str) {
		$in_str = str_replace('\\','\\\\',$in_str);
		$in_str = str_replace("\n",'\\n',$in_str);
		$in_str = str_replace('"','\\"',$in_str);
		mb_internal_encoding("UTF-8");
		$convmap = array(0x80, 0xFFFF, 0, 0xFFFF);
		$str = "";
		for($i=mb_strlen($in_str)-1; $i>=0; $i--) {
			$mb_char = mb_substr($in_str, $i, 1);
			if(mb_ereg("&#(\\d+);", mb_encode_numericentity($mb_char, $convmap, "UTF-8"), $match)) {
				$str = sprintf("\\u%04x", $match[1]) . $str;
			} else {
				$str = $mb_char . $str;
			}
		}
		return $str;
	}

}

if (!defined('PHP_VERSION_ID')) {
    $version = explode('.', PHP_VERSION);

    define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
}

