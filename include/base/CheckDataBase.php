<?PHP

/*******************************************************************************************************
 * <PRE>
 *
 * 入力内容チェック ベースクラス
 *
 * @original 丹羽一智
 * @author 吉岡幸一郎
 * @version 1.0.0
 *
 * </PRE>
 *******************************************************************************************************/

class CheckDataBase
{
	var $gm;
	var $type;
	var $error_design;
	var $check;
	var $data;
	var $error_name;
	var $error_msg;
	var $edit;
	var $_DEBUG	 = DEBUG_FLAG_CHECK_DATA;

	var $default_message = 'に問題が発生しています。';

	function __construct( &$gm, $edit, $loginUserType, $loginUserRank, $type = false )
	{
		$this->type			 = $type?$type:$_GET['type'];
		$this->gm			 = $gm[ $this->type ];
		$this->error_design	 = Template::getTemplate( $loginUserType , $loginUserRank , $this->type , 'REGIST_ERROR_DESIGN' );
		$this->check		 = true;
		$this->error_name  	 = Array();
		$this->error_msg   	 = Array();
		$this->edit			 = $edit;
	}

	function reset(  ){
		$this->check		 = true;
		$this->error_name  	 = Array();
		$this->error_msg   	 = Array();
	}

	function setErrorDesign( $design_file ){
		$this->error_design = $design_file;
	}

	function setType( $type ){
		$this->type = $type;
	}

	//データに置換処理をかける。
	function replaceData( $edit = false ){
		$row = count($this->gm->colName);

		for($i=0; $i<$row; $i++)
		{
			$name		 = $this->gm->colName[$i];
			if( !$edit )
			{
				if(	$this->gm->maxStep >= 2 &&
					$this->gm->colStep[ $name ] != $this->data['step'] ){
					continue;
				}
			}

			if( isset($this->data[ $name ]) && isset($this->gm->colExtend[ $name ]) ){
				$this->data[ $name ] = $this->gm->replaceString( $this->data[ $name ], $this->gm->colExtend[ $name ], $this->gm->colType[ $name ] );
			}
		}
	}

	// 正規表現による汎用的な構文チェック
	function checkRegex( $edit = false )
	{
		for($i=0; $i<count($this->gm->colName); $i++)
		{
			if( !$edit )
			{
				if( $this->gm->maxStep >= 2 && $this->gm->colStep[$this->gm->colName[$i]] != $this->data['step']){
					continue;
				}
			}

			$name		 = $this->gm->colName[$i];
			if( strlen( $this->gm->colRegex[ $name ] ) )
			{
				if( isset( $this->data[ $name ]) && $this->data[ $name ] != null )
				{
					if( !preg_match( $this->gm->colRegex[ $name ],$this->data[ $name ]) )
					{
						$this->addError( $name. '_REGEX', null, $name );
					}
				}
			}
		}
		return $this->check;
	}

	function checkIntRange( $edit = false ) //
	{
		foreach( $this->gm->colName as $column ) //全てのカラムを処理
		{
			if( !$edit ) //登録画面でのチェックの場合
			{
				if( $this->gm->maxStep >= 2 && $this->gm->colStep[ $column ] != $this->data[ 'step' ] ) //現在のステップの入力カラムではない場合
					{ continue; }
			}

			if( 'int' != $this->gm->colType[ $column ] ) //int型ではない場合
				{ continue; }

			if( !isset( $this->data[ $column ] ) || !$this->data[ $column ] ) //値が送信されていない場合
				{ continue; }

			if( is_numeric( $this->data[ $column ] ) && ( $this->data[ $column ]> 2147483647 || $this->data[ $column ] < -2147483647 ) ) //intの範囲を超えている場合
				{ $this->addError( $column . '_IntRange' , null , $column ); }
		}

		return $this->check;
	}

	function checkFileError( $edit = false ){
		global $CONFIG_SQL_FILE_TYPES ;
		global $MAX_FILE_SIZE;
		global $UPLOAD_FILE_EXT;

		$str = '';
		$max = $MAX_FILE_SIZE;
		if( isset( $this->data[ 'MAX_FILE_SIZE'] ) ){
			$max = $this->data[ 'MAX_FILE_SIZE'];
		}
		$row = count($this->gm->colName);
		for($i=0; $i<$row; $i++){
			$name = $this->gm->colName[ $i ];
			if( array_search( $this->gm->colType[ $name ], $CONFIG_SQL_FILE_TYPES ) !== FALSE && isset( $_FILES[ $name ]) ){
				if( $this->_DEBUG ){ d('checkFileError: column('.$name.')  code:'.$_FILES[ $name ][ 'error' ].'');}

				preg_match( '/(\.\w*$)/', $_FILES[ $name ][ 'name' ], $tmp );
				$ext		 = strtolower(str_replace( ".", "", $tmp[1] ));

				$str="";
				if( $_FILES[ $name ][ 'error' ] != UPLOAD_ERR_OK ){
					switch( $_FILES[ $name ][ 'error' ] ){
						case UPLOAD_ERR_INI_SIZE:
							$str = 'アップロードされたファイルのファイルサイズが制限をオーバーしています。(type:1)<br/>';
							break;
						case UPLOAD_ERR_FORM_SIZE:
							$str = 'アップロードされたファイルのファイルサイズが制限をオーバーしています。(type:2)<br/>';
							break;
						case UPLOAD_ERR_PARTIAL:
							$str = 'ファイルが一部しかアップロードされませんでした。';
							break;
						case UPLOAD_ERR_NO_FILE:
							//$str = 'ファイルがアップロードされていません。';
							break;
						case UPLOAD_ERR_NO_TMP_DIR:
						case UPLOAD_ERR_CANT_WRITE:
						case UPLOAD_ERR_EXTENSION:
							$str = 'ファイルのアップロードに失敗しました。(type:'.$_FILES[ $name ][ 'error' ].')<br/>';
							break;
					}
				}else if( $_FILES[ $name ][ 'size' ] > $max ){
					$str = 'アップロードされたファイルのファイルサイズが制限をオーバーしています。(type:3)<br/>';
				}else if( !in_array( $ext , $UPLOAD_FILE_EXT ) ){
					$str = $ext . 'ファイルはアップロードできません。';
				}else{
					switch($ext)
					{
						case 'gif'  :
						case 'jpg'  :
						case 'jpeg' :
						case 'png'  :
						case 'swf'  :
						case 'bmp'  :
						{
							if( !SystemUtil::VerifyImageExt( $_FILES[ $name ][ 'tmp_name' ] , $ext ) )
								{ $str = '不正なファイルデータです。拡張子が正しいか確認してください。'; }

							break;
						}

						default:
							{ break; }
					}
				}

				if( strlen($str) ){
					$this->addError( $name.'_UPLOAD_ERR', $str, $name );
				}
			}
		}

	}

	function checkOverflow( $edit = false )
	{

		for($i=0; $i<count($this->gm->colName); $i++)
		{
			$name		 = $this->gm->colName[$i];
			if( !$edit )
			{
				if( $this->gm->maxStep >= 2 && $this->gm->colStep[$name] != $this->data['step'])
				{
					continue;
				}
			}
			$y = $name.'_year';
			$m = $name.'_month';
			$d = $name.'_day';

			switch( $this->gm->colType[ $name ] )
			{
				case "date":
				case "timestamp":
					if( isset($this->data[ $name ]) && $this->data[ $name ] > 2147483647 )
					{
						$this->addError( $name. '_OVERFLOW', null, $name );
					}else if( isset($this->data[ $y ]) && $this->data[ $y ] > 2038 ){
						$this->addError( $name. '_OVERFLOW', null, $name );
					}else if( isset($this->data[ $y ]) && $this->data[ $y ] == 2038 && $this->data[ $m ] > 1 ){
						$this->addError( $name. '_OVERFLOW', null, $name );
					}else if( isset($this->data[ $y ]) && $this->data[ $y ] == 2038 && $this->data[ $m ] == 1 && $this->data[ $d ] >19 ){
						$this->addError( $name. '_OVERFLOW', null, $name );
					}
					break;
			}
		}
		return $this->check;
	}

	/*
	 * 以下、lstの設定からの呼出しメソッド
	 */

	// 空欄チェック
	function checkNull($name,$args)
	{
		global $CONFIG_SQL_FILE_TYPES;

		if(array_search( $this->gm->colType[ $name ], $CONFIG_SQL_FILE_TYPES ) !== FALSE ){
			if( ( $_FILES[  $name  ]['name'] || !empty($this->data[ $name . '_filetmp' ]) || !empty($this->data[ $name ]) ) && ( !isset($this->data[ $name . '_DELETE' ]) || $this->data[ $name . '_DELETE' ] != "true" ) ){
				return $this->check;
			}
		}else{
			if( isset( $this->data[ $name ] ) && !is_null($this->data[ $name ]) && $this->data[  $name  ] !== '' ){
				return $this->check;
			}
		}
		$this->addError( $name );
		return $this->check;
	}

	// PCだけチェックする
	function checkNullPC($name,$args)
	{
		global $terminal_type;

		if( !$terminal_type && ( !isset(   $_POST[ $name ]   ) || $_POST[  $name  ] == null ) )
		{
			if( $_FILES[  $name  ]['name'] )	 { return $this->check; }
			$this->addError( $name );
		}
		return $this->check;
	}

	// 携帯だけチェックする
	function checkNullMobile($name,$args)
	{
		global $terminal_type;

		if( $terminal_type && ( !isset(   $_POST[ $name ]   ) || $_POST[  $name  ] == null ) )
		{
			if( $_FILES[  $name  ]['name'] )	 { return $this->check; }
			$this->addError( $name );
		}
		return $this->check;
	}

	// 空欄チェック
	// Errorメッセージを指定したcolmと共通利用する
	function checkNullset($name,$args)
	{
    	if( isset($this->error_name[$args[0]]) && $this->error_name[$args[0]] ){
    		return $this->check;
    	}

		global $CONFIG_SQL_FILE_TYPES;
		if(array_search( $this->gm->colType[ $name ], $CONFIG_SQL_FILE_TYPES ) !== FALSE ){
			if( ( $_FILES[  $name  ]['name'] || !empty($this->data[ $name . '_filetmp' ]) ) && ( !isset($this->data[ $name . '_DELETE' ]) || $this->data[ $name . '_DELETE' ] != "true" ) ){
				return $this->check;
			}
		}else{
			if( isset( $this->data[ $name ] ) && !is_null($this->data[ $name ]) && $this->data[  $name  ] !== '' ){
				return $this->check;
			}
		}

		$this->addError( $args[0], null, $args[0] );
		return $this->check;
	}

	// 特定のユーザーの場合に空欄チェック
	function checkNullAuthority($name,$args)
	{
		global $loginUserType;

		if(!count($args)){return;}

		if( array_search($loginUserType,$args) !== FALSE && ( !isset(   $this->data[ $name ]   ) || $this->data[  $name  ] == null ) )
		{
			if( $_FILES[  $name  ]['name'] || $this->data[ $name . '_filetmp' ] )	 { return $this->check; }
			$this->addError( $name );
		}
		return $this->check;
	}

	function checkModNull($name,$args)
	{
		global $loginUserType;

		if(!count($args)){return $this->check;}

		if(!class_exists('mod_'.$args[0])){
			return $this->check;
		}

		return $this->checkNull($name,array());
	}

	// 引数で指定した条件を見たす場合にチェック(複数条件指定可能
	function checkNullFlag($name,$args)
	{
		if( !isset($args[0]) || !isset($args[1]) ){
			return $this->check;
		}else{
			for($i=0;isset($args[$i]);$i+=2){
				if( !isset( $this->data[$args[$i]] ) || $this->data[$args[$i]] != $args[$i+1] ){
					return $this->check;
				}
			}
		}
		return $this->checkNull($name,$args);
	}

	// 引数で指定した条件を満たさない場合にチェック(複数条件指定可能
	function checkNotNullFlag($name,$args)
	{
		if( !isset($args[0]) || !isset($args[1]) ){
			return $this->check;
		}else{
			for($i=0;isset($args[$i]);$i+=2){
				if( !isset( $this->data[$args[$i]] ) || $this->data[$args[$i]] == $args[$i+1] ){
					return $this->check;
				}
			}
		}
		return $this->checkNull($name,$args);
	}

	//削除時のみチェック
	function checkIfDelete($name,$args)
	{
        global $controllerName;
		$method = array_shift( $args );

		if( 'delete' == strtolower( $controllerName ) )
			{ return call_user_func( Array( $this , 'check' . $method ) , $name , $args ); }

		return $this->check;
	}

	function checkSize($name,$args){
		if( strlen( $this->data[ $name ] ) > $args[0] )
		{
			$this->addError( $name.'_size', null, $name );
		}
		return $this->check;
	}

	/**
		@brief 画像の横px数を調べる
	*/
	function checkWidth( $name , $args ) //
	{
		$file = $_FILES[ $name ][ 'tmp_name' ];

		if( !$this->data[ $name . '_DELETE' ] )
		{
			if( !$file || !file_exists( $file ) )
				{ $file = $this->data[ $name ]; }

			if( !$file || !file_exists( $file ) )
				{ $file = $this->data[ $name . '_filetmp' ]; }
		}

		if( !$file || !file_exists( $file ) )
			{ return $this->check; }

		$info     = getimagesize( $file );
		$width    = $info[ 0 ];
		$minWidth = $args[ 0 ];
		$maxWidth = $args[ 1 ];

		if( $minWidth && $minWidth > $width )
			{ $this->addError( $name . '_minWidth' , null , $name ); }

		if( $maxWidth && $maxWidth < $width )
			{ $this->addError( $name . '_maxWidth' , null , $name ); }

		return $this->check;
	}

	/**
		@brief 画像の縦px数を調べる。
	*/
	function checkHeight( $name , $args ) //
	{
		$file = $_FILES[ $name ][ 'tmp_name' ];

		if( !$this->data[ $name . '_DELETE' ] )
		{
			if( !$file || !file_exists( $file ) )
				{ $file = $this->data[ $name ]; }

			if( !$file || !file_exists( $file ) )
				{ $file = $this->data[ $name . '_filetmp' ]; }
		}

		if( !$file || !file_exists( $file ) )
			{ return $this->check; }

		$info      = getimagesize( $file );
		$height    = $info[ 1 ];
		$minHeight = $args[ 0 ];
		$maxHeight = $args[ 1 ];

		if( $minHeight && $minHeight > $height )
			{ $this->addError( $name . '_minHeight' , null , $name ); }

		if( $maxHeight && $maxHeight < $height )
			{ $this->addError( $name . '_maxHeight' , null , $name ); }

		return $this->check;
	}

	// 引数で指定した条件を見たす場合にチェック(複数条件指定可能
	function checkFlag($name,$args)
	{
		if( !isset($args[0]) || !isset($args[1]) ){
			return $this->check;
		}else{
			if( !isset( $this->data[$args[0]] ) || $this->data[$args[0]] != $args[1] ){
				return $this->check;
			}
		}

		if( !method_exists( $this , 'check' . $args[2] ) )
			{ return $this->callAppendCheckFunction( 'check' . $args[2] , $name , array_slice($args,3) ); }
		else
			{ return call_user_func(array($this,'check'.$args[2]), $name, array_slice($args,3) ); }
	}

	// ユーザータイプ一覧をカンマ区切りで渡し、一致するユーザーが居た場合、指定されたチェックを行なう。
	function checkAuthorityFlag($name,$args)
	{
		global $loginUserType;

		if(!count($args)){return;}

		if( array_search($loginUserType,explode('+',$args[0])) === FALSE ){
			return $this->check;
		}
		if( !method_exists( $this , 'check' . $args[1] ) )
			{ return $this->callAppendCheckFunction( 'check' . $args[1] , $name , array_slice($args,2) ); }
		else
			{ return call_user_func(array($this,'check'.$args[1]), $name, array_slice($args,2) ); }

	}

	// 値が変更されている時にチェック
	function checkChangeFlag($name,$args)
	{
		if( !isset( $_POST[$name] ) || !strlen($_POST[$name]) ){
			return $this->check;
		}

		if( isset($_GET['id']) && strlen( $_GET['id'] ) ){
			$db = $this->gm->getDB();

			$rec = $db->selectRecord( $_GET['id'] );
			if( $rec ){
				$old = $db->getData( $rec, $name );

				if( 'password' == $db->colType[ $name ] )
					{ $old = SystemUtil::decodePassword( $old ); }

				if( $_POST[$name] == $old )
					return $this->check;
			}

			if( !method_exists( $this , 'check' . $args[0] ) )
				{ return $this->callAppendCheckFunction( 'check' . $args[0] , $name , array_slice($args,1) ); }
			else
				{ return call_user_func(array($this,'check'.$args[0]), $name, array_slice($args,1) ); }
		}

		if( !method_exists( $this , 'check' . $args[0] ) )
			{ return $this->callAppendCheckFunction( 'check' . $args[0] , $name , array_slice($args,1) ); }
		else
			{ return call_user_func(array($this,'check'.$args[0]), $name, array_slice($args,1) ); }
	}

	//銀行口座名義の書式をチェック
	function checkBankAccount( $name , $args )
	{
		if( !isset( $_POST[ $name ] ) || !strlen( $_POST[ $name ] ) )
			{ return $this->check; }

		$jp       = 'アイウエオカキクケコサシスセソタチツテトナニヌネノハヒフヘホマミムメモヤユヨラリルレロワン';
		$jpDak    = 'ガギグゲゴザジズゼゾダヂヅデドバビブベボヴ';
		$jpHandak = 'パピプペポ';
		$jpSmall  = 'ァィゥェォヮャュョッ';
		$eng      = 'ＡＢＣＤＥＦＧＨＩＪＫＬＭＮＯＰＱＲＳＴＵＶＷＸＹＺ';
		$num      = '０１２３４５６７８９';
		$mark     = '（）．ー‐／　';

		$whiteList = $jp . $jpDak . $jpHandak . $jpSmall . $eng . $num . $mark;

		if( $args[ 1 ] ) //追加の許容文字がある場合
			{ $whiteList .= $args[ 1 ]; }

		$length = mb_strlen( $_POST[ $name ] );

		for( $i = 0 ; $length > $i ; ++$i )
		{
			if( FALSE === strpos( $whiteList , mb_substr( $_POST[ $name ] , $i , 1 ) ) )
			{
				$this->addError( $name . '_bankAccount' , null , $name );
				break;
			}
		}

		return $this->check;
	}

	// 任意のtableのidとして存在している
	function checkIntable($name,$args)
	{
		global $gm;

		$type = $args[0];

		if( isset( $this->data[ $name ] ) && $this->data[ $name ] != null )
		{
			$db = $gm[ $type ]->getDB();
			if( !$db->existsRow( $db->searchTable( $db->getTable(), 'id' , '=' , $this->data[ $name ] ) ) ){
				$this->addError( $args[0].'_in_table', null, $args[0] );
			}
		}
		return $this->check;
	}

	/**
		@brief   編集禁止カラムチェック。
		@details POSTデータがレコードの元の値と異なる場合、エラー情報を追加します。
	*/
	function checkConst( $name , $args )
	{
		if( !isset( $_POST[ $name ] ) ) //POSTされてないならチェック不要
			return $this->check;

		//オリジナルデータを取得
		$db     = SystemUtil::getGMforType( $_GET[ 'type' ] )->getDB();
		$rec    = $db->selectRecord( $_GET[ 'id' ] , 'all' );
		$origin = $db->getData( $rec , $name );

		if( $this->gm->colType[ $name ] == "boolean" )
		{
			$lhs = SystemUtil::convertBool( $origin );
			$rhs = SystemUtil::convertBool( $_POST[ $name ] );
		}
		else
		{
			$lhs = preg_replace( '/\r\n|\n|\r/' , "\n" , $origin );
			$rhs = preg_replace( '/\r\n|\n|\r/' , "\n" , $_POST[ $name ] );
		}

		if( $lhs != $rhs )
		{
			//個別メッセージ用エラーパート
			$this->addError( $name . '_isConst', null, $name );

			//単一メッセージ用エラーパート
			if( !$this->error_name[ 'Const' ] )
				$this->addError( 'Const' );
		}

		return $this->check;
	}

	/**
		@brief   管理者データチェック。
		@details 管理者以外のユーザーが編集しようとした場合、エラー情報を追加します。
	*/
	function checkAdminData( $name , $args )
	{
		global $loginUserType;

		if( 'admin' == $loginUserType ) //管理者はパス
			return $this->check;

		if( !isset( $_POST[ $name ] ) ) //POSTされてないならチェック不要
			return $this->check;

		//オリジナルデータを取得
		$db     = SystemUtil::getGMforType( $_GET[ 'type' ] )->getDB();
		$rec    = $db->selectRecord( $_GET[ 'id' ] , 'all' );
		$origin = $db->getData( $rec , $name );

		if( $this->gm->colType[ $name ] == "boolean" )
		{
			$lhs = SystemUtil::convertBool( $origin );
			$rhs = SystemUtil::convertBool( $_POST[ $name ] );
		}
		else
		{
			$lhs = preg_replace( '/\r\n|\n|\r/' , "\n" , $origin );
			$rhs = preg_replace( '/\r\n|\n|\r/' , "\n" , $_POST[ $name ] );
		}

		if( $lhs != $rhs )
		{
			//個別メッセージ用エラーパート
			$this->addError( $name . '_isAdminData', null, $name );

			//単一メッセージ用エラーパート
			if( !$this->error_name[ 'AdminData' ] )
				$this->addError( 'AdminData' );
		}

		return $this->check;
	}

	function is_uri($text,$level = 1){
		switch($level){
			case 0: default:
				//接頭と使用文字の一致
				if (!preg_match("/https?:\/\/[-_.!~*'()a-zA-Z0-9;\/?:@&=+$,%#]+/", $text)){ return FALSE; }
				break;
			case 1:
				//http URL の正規表現
				$re = "/\b(?:https?|shttp):\/\/(?:(?:[-_.!~*'()a-zA-Z0-9;:&=+$,]|%[0-9A-Fa-f" .
                      "][0-9A-Fa-f])*@)?(?:(?:[a-zA-Z0-9](?:[-a-zA-Z0-9]*[a-zA-Z0-9])?\.)" .
                      "*[a-zA-Z](?:[-a-zA-Z0-9]*[a-zA-Z0-9])?\.?|[0-9]+\.[0-9]+\.[0-9]+\." .
                      "[0-9]+)(?::[0-9]*)?(?:\/(?:[-_.!~*'()a-zA-Z0-9:@&=+$,]|%[0-9A-Fa-f]" .
                      "[0-9A-Fa-f])*(?:;(?:[-_.!~*'()a-zA-Z0-9:@&=+$,]|%[0-9A-Fa-f][0-9A-" .
                      "Fa-f])*)*(?:\/(?:[-_.!~*'()a-zA-Z0-9:@&=+$,]|%[0-9A-Fa-f][0-9A-Fa-f" .
                      "])*(?:;(?:[-_.!~*'()a-zA-Z0-9:@&=+$,]|%[0-9A-Fa-f][0-9A-Fa-f])*)*)" .
                      "*)?(?:\?(?:[-_.!~*'()a-zA-Z0-9;\/?:@&=+$,]|%[0-9A-Fa-f][0-9A-Fa-f])" .
                      "*)?(?:#(?:[-_.!~*'()a-zA-Z0-9;\/?:@&=+$,]|%[0-9A-Fa-f][0-9A-Fa-f])*)?/";

				if (!preg_match($re, $text)) { return FALSE; }
				break;
		}
		return TRUE;
	}

	function checkUri($name,$args){
		if( $this->error_name[$name] || !strlen( $this->data[$name] ) ){
			return $this->check;
		}

		if(count($args)){
			$level=$args[0];
		}else{
			$level=1;
		}

		if(!$this->is_uri($this->data[$name],$level)){
			$this->addError($name. '_URI', null, $name );
		}
		return $this->check;
	}

	function is_mail($text,$level = 3,$dns_check = false)
	{
		switch($level){
			case 0: default:
				if (!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $text)){ return FALSE; }
				break;
			case 1:
				//http://www.tt.rim.or.jp/~canada/comp/cgi/tech/mailaddrmatch/
				//「なるべく」おかしなアドレスを弾く正規表現
				if (!preg_match("/^[\x01-\x7F]+@(([-a-z0-9]+\.)*[a-z]+|\[\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\])/", $text)){ return FALSE; }
				break;
			case 2:
				//PEAR::Mail_RFC822
				if (!preg_match("/^([*+!.&#$|\'\\%\/0-9a-z^_`{}=??:-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,})$/i", $text)){ return FALSE; }
				break;
			case 3:
				//CakePHP
				if (!preg_match("/\\A(?:^([a-z0-9][a-z0-9_\\-\\.\\+]*)@([a-z0-9][a-z0-9\\.\\-]{0,63}\\.(com|org|net|biz|info|name|net|pro|aero|coop|museum|[a-z]{2,}))$)\\z/i", $text)){ return FALSE; }
				break;
			case 4:
				//symfony
				if (!preg_match("/^([^@\s]+)@((?:[-a-z0-9]+\.)+[a-z]{2,})$/i", $text)){ return FALSE; }
				break;
			case 5:
				//Cal Henderson: http://iamcal.com/publish/articles/php/parsing_email/pdf/
				//Parsing Email Adresses in PHP
				$re = '/^([^\\x00-\\x20\\x22\\x28\\x29\\x2c\\x2e\\x3a-\\x3c\\x3e\\x40\\x5b-'
				.'\\x5d\\x7f-\\xff]+|\\x22([^\\x0d\\x22\\x5c\\x80-\\xff]|\\x5c\\x00-'
				.'\\x7f)*\\x22)(\\x2e([^\\x00-\\x20\\x22\\x28\\x29\\x2c\\x2e\\x3a-'
				.'\\x3c\\x3e\\x40\\x5b-\\x5d\\x7f-\\xff]+|\\x22([^\\x0d\\x22\\x5c\\x80'
				.'-\\xff]|\\x5c\\x00-\\x7f)*\\x22))*\\x40([^\\x00-\\x20\\x22\\x28\\x29'
				.'\\x2c\\x2e\\x3a-\\x3c\\x3e\\x40\\x5b-\\x5d\\x7f-\\xff]+|\\x5b([^'
				.'\\x0d\\x5b-\\x5d\\x80-\\xff]|\\x5c\\x00-\\x7f)*\\x5d)(\\x2e([^\\x00-'
				.'\\x20\\x22\\x28\\x29\\x2c\\x2e\\x3a-\\x3c\\x3e\\x40\\x5b-\\x5d\\x7f-'
				.'\\xff]+|\\x5b([^\\x0d\\x5b-\\x5d\\x80-\\xff]|\\x5c\\x00-\\x7f)*'
				.'\\x5d))*$/';

				if (!preg_match($re, $text)) { return FALSE; }
				break;
		}

		//存在するメールアドレスかどうかを確認するためdnsのチェック
		if($dns_check){
			if (function_exists('checkdnsrr')) {
				$tokens = explode('@', $text);
				if (!checkdnsrr($tokens[1], 'MX') && !checkdnsrr($tokens[1], 'A'))
				{
					return FALSE;
				}
			}
		}
		return TRUE;
	}

	function checkMail($name,$args){
		if( isset($this->error_name[$name]) && $this->error_name[$name] || !strlen( $this->data[$name] ) ){
			return $this->check;
		}

		if(isset($args[0]) && strlen($args[0]) ){
			$level=$args[0];
		}else{ $level=3; }

		if(isset($args[1]) && strlen($args[1]) ){
			$dns_check=(boolean)$args[1];
		}else{ $dns_check=false; }

		if( !$this->is_mail($this->data[$name]) ){
			$this->addError($name. '_MAIL', null, $name );
		}
		return $this->check;
	}

	// 重複チェック処理
	function checkDuplication( $name ,$args){
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		// **************************************************************************************

		if(  isset( $this->data[$name] )  )
		{
			$db		 = $this->gm->getDB();
			$table	 = $db->getTable();
			if( isset( $this->data['id'] ) ) { $table	 = $db->searchTable($table, 'id', '!', $this->data['id']); }
			if( count($args) ){
				for( $i=0 ; isset($args[$i+1]); $i+=2 ){
					$table	 = $db->searchTable($table, $args[$i], $args[$i+1], $this->data[$args[$i]]);
				}
			}
			$table	 = $db->searchTable($table, $name, '=', $this->data[$name]);
			if( $db->existsRow($table) )
			{
				$this->addError($name.'_dup', null, $name );
			}
		}
		return $this->check;
	}

	// メールの重複チェック処理
	function checkMailDup($name,$args)
	{
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $THIS_TABLE_IS_USERDATA;
		global $TABLE_NAME;
		global $gm;
		// **************************************************************************************

		if( isset( $this->data[$name] ) )
		{// メールアドレス重複チェック

			$max	 = count($TABLE_NAME);
			for($i=0; $i<$max; $i++)
			{
				if(  $THIS_TABLE_IS_USERDATA[ $TABLE_NAME[$i] ]  )
				{
					$db		 = $gm[ $TABLE_NAME[$i] ]->getDB();
					$table	 = $db->getTable();
					if( isset( $this->data['id'] ) ) { $table	 = $db->searchTable($table, 'id', '!', $this->data['id']); }
					$table	 = $db->searchTable($table, 'mail', '=', $this->data[$name]);
					if( $db->existsRow($table) )
					{
						$this->addError('mail_dup',null, $name);
						break;
					}
				}
			}
		}
		return $this->check;
	}

	//日付の空チェック
	function checkDateNull($name,$args)
	{
		$y_key = $name.'_year';
		$m_key = $name.'_month';
		$d_key = $name.'_day';
		if(	( empty($this->data[$y_key]) || empty($this->data[$m_key]) || empty($this->data[$d_key]) )){
			$this->addError($name);
		}

		return $this->check;
	}

	//日付の整合チェック
	function checkDate($name,$args)
	{
		if( count( $args ) > 1 ){
			$y = $name;
			$m = $args[0];
			$d = $args[1];
		}else{
			$y = $name.'_year';
			$m = $name.'_month';
			$d = $name.'_day';
		}
		if( strlen($this->data[$y]) && strlen($this->data[$m]) && strlen($this->data[$d]) ){
			if( ! checkdate((int)$this->data[$m],(int)$this->data[$d] , (int)$this->data[$y]) ){
				$this->addError($name . '_date_format',null,$name);
			}
		}

		return $this->check;
	}

	//日付が現在時刻より過去かどうか(checkDataとセットで使う
	function checkOlddate($name,$args)
	{

		if( count( $args ) > 1 ){
			$y = $name;
			$m = $args[0];
			$d = $args[1];
			if(isset($args[2]))
				$h = $args[2];
			if(isset($args[3]))
				$min = $args[3];
		}else{
			$y = $name.'_year';
			$m = $name.'_month';
			$d = $name.'_day';
			$h = $name.'_hour';
			$min = $name.'_min';
		}

		if( strlen($this->data[$y]) && strlen($this->data[$m]) && strlen($this->data[$d]) ){
			if(!$this->error_name[ 'date_format' ]){

				if( mktime( $h?$this->data[$h]:0 , $min?$this->data[$min]:0 ,0,$this->data[$m],$this->data[$d] , $this->data[$y]) < time() ){
					$this->addError($name . '_old_date',null,$name);
				}
			}
		}

		return $this->check;
	}

	// 確認入力との一致チェック
	function checkConfirmInput($name,$args)
	{
		$val1 = $name;
		$val2 = $args[0];

		 if( isset( $this->data[ $val1 ] ) ){
			if( !isset( $this->data[ $val2 ] ) || !strlen( $this->data[ $val2 ] ) ){
				$this->addError($name.'_CONFIRM_NOT');
			}else if( $this->data[ $val1 ] != $this->data[ $val2 ]  )
			{
				$this->addError($name.'_CONFIRM_CHECK');
			}else{
				$this->gm->addHiddenForm( $val2, $this->data[ $val2 ] );
			}
		 }

		return $this->check;
	}

	//長すぎないかチェック
	function checkLong($name,$args){
	 	global $SYSTEM_CHARACODE;
		$max = $args[0];

		if( mb_strlen( $this->data[ $name ], $SYSTEM_CHARACODE ) > $max ){
			$this->addError($name.'_long',null,$name);
		}
		return $this->check;
	}

	//短かすぎないかチェック
	function checkShort($name,$args){
	 	global $SYSTEM_CHARACODE;
		$min = $args[0];

		$len = mb_strlen( $this->data[ $name ], $SYSTEM_CHARACODE );
		if( $len == 0 ){
			return $this->check;
		}else if( $len < $min ){
			$this->addError($name.'_short',null,$name);
		}
		return $this->check;
	}

	//文字数が一致しているかチェック
	function checkLength($name,$args){
	 	global $SYSTEM_CHARACODE;
		$checklen = $args[0];

		$len = mb_strlen( $this->data[ $name ], $SYSTEM_CHARACODE );
		if( $len == 0 ){
			return $this->check;
		}else if( $len != $checklen ){
			$this->addError($name.'_length',null,$name);
		}
		return $this->check;
	}

	// アップされたファイルがイメージファイルかどうかをチェック
	function checkImage($name,$args)
	{
		if( isset($_FILES[ $name ]['error']) && $_FILES[ $name ]['error'] == UPLOAD_ERR_OK && isset($_FILES[ $name ]['name']) &&$_FILES[ $name ]['name'] != "" ){
			switch( $_FILES[ $name ][ 'type' ] ){
				case 'image/gif':
				case 'image/jpg':
				case 'image/jpeg':
				case 'image/png':
					//IE独自仕様対応
				case 'image/x-png':
				case 'image/pjpeg':
					break;
				default:
					$this->addError( $name.'_NO_IMAGE',null,$name );
			}
		}

		return $this->check;
	}

	//指定tableの指定columnに自信のidが存在するかどうか
	function checkChild($id, $type, $column ){
		global $gm;

		$cdb = $gm[ $type ]->getDB();
		//		$ctable = $cdb->searchTable( $cdb->getTable(), $column, '=', '%'.$id.'%' );
		$ctable = $cdb->searchTable( $cdb->getTable(), $column, '=', $id );

		if( $cdb->existsRow( $ctable ) ){
			$this->addError($type.'_CHILD',null,$name);
		}
		return $this->check;
	}

	/**
	 * パラメータが負数であればエラーを追加します。
	 *
	 * @param $name チェックするカラム名を指定します。
	 * @param $args 追加のパラメータ配列を指定します。このメソッドでは使用しません。
	 */
	function checkNegativeNum( $name , $args )
	{
		if( isset($_POST[ $name ]) && 0 > $_POST[ $name ] )
			$this->addError( $name . '_NegativeNum',null, $name );

		return $this->check;
	}

	// 数値が指定範囲内に収まるか確認
	function checkNumRange($name,$args)
	{
		$mini	 = $args[0];
		$max	 = $args[1];

		$number = (double)$_POST[$name];


		if( strlen($_POST[$name]) && ( $number < $mini || $number > $max ) )
		{
			$isOutRange = false;

			if( '*' != $mini )
				{ $isOutRange |= $mini > $number; }
			if( '*' != $max )
				{ $isOutRange |= $max < $number; }

			if( $isOutRange )
				{ $this->addError( $name . '_NumRange',null, $name ); }
		}

		return $this->check;
	}

	// ファイルの容量を制限する
	function checkFileWeight( $name , $args )
	{
		if( isset( $this->data[ $name . '_DELETE' ] ) && $this->data[ $name . '_DELETE' ] ) //ファイルの削除が要求されている場合
			{ return $this->check; }

		if( $_FILES[ $name ] && $_FILES[ $name ][ 'tmp_name' ] && $args[ 0 ] < filesize( $_FILES[ $name ][ 'tmp_name' ] ) ) //アップロードファイルのサイズが容量制限を越えている場合
			{ $this->addError( $name . '_weight' , null , $name ); }

		if( $this->data[ $name . '_filetmp' ] && $args[ 0 ] < filesize( $this->data[ $name . '_filetmp' ] ) ) //持ち越しデータのサイズが容量制限を越えている場合
			{ $this->addError( $name . '_weight' , null , $name ); }

		return $this->check;
	}

	// 汎用チェック処理を一括で行う
	function generalCheck($edit, $data = false)
	{
		$this->data	= $data?$data:$_POST;

		$this->replaceData($edit);

		$this->checkIntRange($edit);

		$this->checkRegex($edit);

		$this->checkFileError($edit);

		$this->checkOverflow($edit);

		$row = count($this->gm->colName);
		for($i=0; $i<$row; $i++)
		{
			if( !$edit )
			{
				if($this->gm->maxStep >= 2 && $this->gm->colStep[$this->gm->colName[$i]] != $this->data['step']){
					continue;
				}
			}

			$faled		 = false;
			$name		 = $this->gm->colName[$i];

			//Null,Uri,Mail,Duplication,MailDup,Pass,Birth,

			if( !$edit )	{	$pal = $this->gm->colRegist[ $name ];	}
			else			{	$pal = $this->gm->colEdit[ $name ];	}

			if( strlen($pal) ){
				$checks = explode('/', $pal );

				foreach( $checks as $check ){
					if( strpos($check,':') === FALSE ){

						if( !method_exists( $this , 'check' . $check ) )
							{ $this->callAppendCheckFunction( 'check' . $check , $name , Array() ); }
						else
							{ call_user_func(array($this,'check'.$check), $name, Array() ); }

						if( $this->_DEBUG ){ d('check'.$check.': column('.$name.') ');}
					}
					else{
						$val = explode(':', $check );

						if( !method_exists( $this , 'check' . $val[ 0 ] ) )
							{ $this->callAppendCheckFunction( 'check' . $val[ 0 ] , $name , array_slice( $val , 1 ) ); }
						else
							{ call_user_func(array($this,'check'.$val[0]), $name, array_slice($val,1)); }

						if( $this->_DEBUG ){ d('check'.$val[0].': column('.$name.')  check('.$check.')');}
					}
				}
			}
		}
		return $this->check;
	}

	function deleteCheck($data = false)
	{
		$this->data	= $data?$data:$_POST;

		$this->replaceData($this->data);

		$row = count($this->gm->colName);

		for($i=0; $i<$row; $i++)
		{
			$faled		 = false;
			$name		 = $this->gm->colName[$i];

			$pal = $this->gm->colEdit[ $name ];

			if( strlen($pal) ){
				$checks = explode('/', $pal );

				foreach( $checks as $check ){

					if( 0 === strpos( strtolower( $check ) , 'ifdelete:' ) )
					{
						if( strpos($check,':') === FALSE ){

							if( !method_exists( $this , 'check' . $check ) )
								{ $this->callAppendCheckFunction( 'check' . $check , $name , Array() ); }
							else
								{ call_user_func(array($this,'check'.$check), $name, Array() ); }

							if( $this->_DEBUG ){ d('check'.$check.': column('.$name.') ');}
						}
						else{
							$val = explode(':', $check );

							if( !method_exists( $this , 'check' . $val[ 0 ] ) )
								{ $this->callAppendCheckFunction( 'check' . $val[ 0 ] , $name , array_slice( $val , 1 ) ); }
							else
								{ call_user_func(array($this,'check'.$val[0]), $name, array_slice($val,1)); }

							if( $this->_DEBUG ){ d('check'.$val[0].': column('.$name.')  check('.$check.')');}
						}
					}
				}
			}
		}
		return $this->check;
	}

	// エラー内容を取得
	function getError( $label = null )
	{
		$tmp = '';
		$error = '';

		if( !$this->check )
		{// エラー内容がある場合

			$is_isError = $label == 'is_error';

			if(is_null($label)){
				$is_error	 = $this->error_msg['is_error'];
				$error		.= $is_error ."\n";
				if( isset($this->error_msg['is_error'])){
					unset($this->error_msg['is_error']);
				}
				$error	.= join($this->error_msg,"\n");
				$this->error_msg['is_error'] = $is_error;
			}else if( '*' == $label ){
				foreach( array_keys( $this->error_name ) as $name )
					{ $error .= $this->error_msg[ $name ]; }
			}else if(isset($this->error_name[ $label ]) && strlen($this->error_name[ $label ]) || $is_isError ){
				$error	.= $this->error_msg[ $label ];
			}

			if( strlen($error) )
			{
				$tmp	.= $this->gm->partGetString( $this->error_design , 'head');
				$tmp	.= $error;
				$tmp	.= $this->gm->partGetString( $this->error_design , 'foot');
			}
		}

		return $tmp;
	}

	// エラーフラグを取得
	function isError( $label = null, $data )
	{
		if( !strlen($data) ) { $data = 'validate'; }
		foreach( explode( '/', $label ) as $l ){
			if( isset($this->error_name[ $l ]) && strlen($this->error_name[ $l ]) ){ return $data;  }
		}
		return "";
	}

	//指定カラムが現在のstepのものかどうかを返す
	function checkStep( $name ){
		if($this->gm->maxStep >= 2 && $this->gm->colStep[$this->gm->colName[$i]] != $this->data['step']){
			return;
		}
	}

	//エラーの有無
	function getCheck(){
		return $this->check;
	}

	//エラーチェックを自前でやる時に使うデータを返す
	function getData(){
		return $this->data;
	}

	function addError($part,$def=null,$name=null ){
		$str = $this->gm->partGetString(  $this->error_design , $part );
		if( is_null( $name ) ){ $name = $part;}
		if( !strlen( $str ) ){ $str = empty($def)?$name.$this->default_message."($part)":$def; }
		if( !isset($this->error_msg[ $name ]) ){ $this->error_msg[ $name ] = ''; }
		$this->error_msg[ $name ] .= $str;
		$this->error_name[ $name ] = true;
		if($this->check) {
			if( !isset($this->error_msg[ 'is_error' ]) ){ $this->error_msg[ 'is_error' ] = ''; }
			$this->error_msg[ 'is_error' ] .= $this->gm->partGetString(  $this->error_design , 'is_error' );
		}
		$this->check = false;
		if( $this->_DEBUG ){ d('addError:'.$part);}
	}

	function addErrorString($str){
		$this->error_msg[ 'string' ] .= $str;
		if($this->check) {
			if( !isset($this->error_msg[ 'is_error' ]) ){ $this->error_msg[ 'is_error' ] = ''; }
			$this->error_msg[ 'is_error' ] .= $this->gm->partGetString(  $this->error_design , 'is_error' );
		}
		$this->check = false;
		if( $this->_DEBUG ){ d('addError:'.$str);}
	}

	function callAppendCheckFunction( $iCheckName , $iName , $iArgs )
	{
		global $MODULES;

		foreach( $MODULES as $name => $info )
		{
			if( class_exists( $name . 'CheckData' ) && method_exists( $name . 'CheckData' , $iCheckName ) )
			{
				$className = $name . 'CheckData';
				$checkData = new $className( $this );

				$checkData->$iCheckName( $iName , $iArgs );

				return $this->check;
			}
		}

		return $this->check;
	}

	// <!--# form multi_file col_name_prefix max_num #--> 系のNullチェック
	// image1 に MultiFileNull:5:3 とする事で、image1～image5 までに 3 つ以上のファイルがアップロードされていないとエラーとする。
	function checkMultiFileNull($name,$args)
	{
		global $CONFIG_SQL_FILE_TYPES;

		if(array_search( $this->gm->colType[ $name ], $CONFIG_SQL_FILE_TYPES ) !== FALSE ) {

			$max = $args[0];
			$check = $args[1];
			$multi_colname = rtrim($name, '0123456789');
			$fileCount = 0;

			for ($i = 1; $i <= $max; $i++) {
				$colname = $multi_colname . $i;

				if (($_FILES[$colname]['name'] || !empty($this->data[$colname . '_filetmp']) || !empty($this->data[$colname])) && (!isset($this->data[$colname . '_DELETE']) || $this->data[$colname . '_DELETE'] != "true")) {
					$fileCount++;
				}
			}
			if (count($_FILES[$multi_colname]['name'])>1 || !empty($_FILES[$multi_colname]['name'][0]) ) {
    			$fileCount += count($_FILES[$multi_colname]['name']);
            }

			if( $check <= $fileCount){
				return $this->check;
			}
		}
		$this->addError( $name );
		return $this->check;
	}

	//debugフラグ操作用
	function onDebug(){ $this->_DEBUG = true; }
	function offDebug(){ $this->_DEBUG = false; }
}

?>