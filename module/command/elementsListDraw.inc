<?PHP

class mod_elementListDraw extends command_base
{

	/**
	 * コンストラクタ。
	 */
	//    function mod_elementListDraw(){ $this->flushBuffer(); }

	/*
	 注目の○○リストを表示
	 つまりは、任意のテーブルの任意のフラグがtrueの項目を一覧として表示する。

	 args
	 0:テーブル名
	 1:フラグカラム名
	 2:表示数
	 3:template part sufix
	 4-:表示条件をループで
	 */
	function pickupListDraw( &$gm, $rec , $args ){
		global $loginUserType;
		global $loginUserRank;
		$HTML = Template::getTemplate( $loginUserType , $loginUserRank , $args[0] , 'MOD_ELEMENT_LIST_TEMPLATE' );

		if( !strlen( $HTML ) ){
			throw new InternalErrorException('dos not template');
		}

		$tgm = SystemUtil::getGMforType( $args[0] );
		$db = $tgm->getDB();
		$list = $db->searchTable( $db->getTable() , $args[1] , '=' , true );

		if( isset($args[3]) ){$sufix=$args[3];}else{$sufix="";}

		for($i=0;isset($args[4+3*$i]);$i++){
			$list = $db->searchTable( $list , $args[4+3*$i] , $args[5+3*$i] , $args[6+3*$i] );
		}
		$list = $db->sortTable( $list, 'regist', 'desc' );

		$maxRow = $args[2];

		$row = $db->getRow( $list );

		if( $maxRow > $row ){ $maxRow = $row; }
		
		$list = $db->limitOffset( $list,0,$maxRow ); 

		$this->addBuffer( $tgm->getString( $HTML , null , 'head'.$sufix ) );
		$list->onCash();
		for( $i = 0 ; $i < $args[2] ; $i++ ){
			if( $i < $row ){
				$lrec = $db->getRecord( $list , $i );
				$this->addBuffer( $tgm->getString( $HTML , $lrec , 'element'.$sufix ) );
			}else{
				$this->addBuffer( $tgm->getString( $HTML , null , 'dummy'.$sufix ) );
			}
		}
		$list->offCash();
		$this->addBuffer( $tgm->getString( $HTML , null , 'foot'.$sufix ) );

		return $this->getBuffer();
	}

	/*
	 新着の○○リストを表示
	 つまりは、registでソートしたテーブルの項目を一覧として表示する。

	 args
	 0:テーブル名
	 1:新着とする期間(時間で)  (0の時は無期限
	 2:表示数
	 3:該当項目が無い場合にnotfoundを表示するか否か(省略時はhead,dummy,footが表示)
	 4:template part sufix
	 5-:表示条件の3引数セットをループで
	 */
	function newListDraw( &$gm, $rec , $args ){
		$tgm = SystemUtil::getGMforType( $args[0] );
		$db = $tgm->getDB();

		$list = $db->getTable();
		$list = $db->sortTable( $list, 'regist', 'desc' );
		$link_param = "";
		if( $args[1] != 0 ){
			$check_time = time() - ($args[1]*60*60);
			$list = $db->searchTable( $list , 'regist' , '>' , $check_time );
			$link_param = '&regist=bt&regist_PAL[]=match+between&registA='.$check_time;
			$link_param .= '&sort=regist&sort_PAL=desc';
		}
		
		array_splice( $args,1,1 );
		$this->listDraw(  &$gm, $rec , $args, $list, $link_param );
	}

	/**
	 任意のカラムでソートしたリストを表示

	 args
	 0:テーブル名
	 1:sortに使用するカラム名
	 2:asc or desc
	 3:表示数
	 4:該当項目が無い場合にnotfoundを表示するか否か(省略時はhead,dummy,footが表示)
	 5:template part sufix
	 6-:表示条件をループで
	 */
	function sortListDraw( &$gm, $rec , $args ){
		$tgm = SystemUtil::getGMforType( $args[0] );
		$db = $tgm->getDB();

		$list = $db->getTable();
		$list = $db->sortTable($list,$args[1],$args[2]);
		$link_param .= '&sort='.$args[1].'&sort_PAL='.$args[2];
		
		array_splice( $args,1,2 );
		$this->listDraw(  &$gm, $rec , $args, $list, $link_param );
	}


	/**
	 複数の任意のカラムでソートしたリストを表示

	 args
	 0:テーブル名
	 1:sortデータ 例(key:asc/key:asc/key:desc)
	 2:表示数
	 3:該当項目が無い場合にnotfoundを表示するか否か(省略時はhead,dummy,footが表示)
	 4:template part sufix
	 5-:表示条件をループで
	 */
	function multiSortListDraw( &$gm, $rec , $args ){
		$tgm = SystemUtil::getGMforType( $args[0] );
		$db = $tgm->getDB();
		
		$sorts = explode( '/', $args[1] );
		foreach( $sorts as $sort ){
			$set = explode( ':', $sort );
			$list = $db->getTable();
			$list = $db->sortTable($list,$set[0],$set[1]);
			$link_param .= '&sort='.$set[0].'&sort_PAL='.$set[1];
		}
		
		array_splice( $args,1,1 );
		$this->listDraw(  &$gm, $rec , $args, $list, $link_param );
	}

	/*
	 任意条件のリスト

	 args
	 0:テーブル名
	 1:表示数
	 2:該当項目が無い場合にnotfoundを表示するか否か(省略時はhead,dummy,footが表示)
	 3:template part sufix
	 (4-6)*n:表示条件をループで
	 */
	function listDraw( &$gm, $rec , $args, $list = null, $link_param = '' ){
		global $loginUserType;
		global $loginUserRank;
		$HTML = Template::getTemplate( $loginUserType , $loginUserRank , $args[0] , 'MOD_ELEMENT_LIST_TEMPLATE' );

		if( !strlen( $HTML ) ){
			throw new InternalErrorException('dos not template');
		}

		$tgm = GMList::getGM( $args[0] );
		$db = $tgm->getDB();
		if( is_null($list) ){ $list = $db->getTable(); }

		if( isset($args[2]) ){$sufix=$args[2];}else{$sufix="";}

		for($i=0;isset($args[4+3*$i]);$i++){
			$list = $db->searchTable( $list , $args[4+3*$i] , $args[5+3*$i] , $args[6+3*$i] );
			
			switch($args[5+3*$i]){
				case '=':
					$link_param .= '&'.$args[4+3*$i].'='.$args[6+3*$i].'&'.$args[4+3*$i].'_PAL%5B%5D=match+comp';
					break;
				case 'in':
					$array = explode('/',$args[6+3*$i]);
					foreach( $array as $val ){
						$link_param .= '&'.$args[4+3*$i].'%5B%5D='.$val;
					}
					$link_param .= '&'.$args[4+3*$i].'_PAL%5B%5D=match+in';
					break;
				case '>=':
				case '>':
					$link_param .= '&'.$args[4+3*$i].'=bt&'.$args[4+3*$i].'_PAL[]=match+between&'.$args[4+3*$i].'A='.$check_time;
					break;
				case '<=':
				case '<':
					$link_param .= '&'.$args[4+3*$i].'=bt&'.$args[4+3*$i].'_PAL[]=match+between&'.$args[4+3*$i].'B='.$check_time;
					break;
			}
		}

		$row = $db->getRow( $list );
		
		if( $row <= 0 && isset($args[2]) && $args[2] == 'true' ){
			$this->addBuffer( $tgm->getString( $HTML , null , 'notfound' ) );
		}else{
			if( $row > $args[1] ){
				$tgm->setVariable( 'search_url', 'search.php?run=true&type='.$args[0].$link_param );
				$tgm->setVariable( 'more', $tgm->getString( $HTML , null , 'more'.$sufix ) );
			}
			
			$this->addBuffer( $tgm->getString( $HTML , null , 'head'.$sufix ) );
			for( $i = 0 ; $i < $args[1] ; $i++ ){
				if( $i < $row ){
					$lrec = $db->getRecord( $list , $i );
					$this->addBuffer( $tgm->getString( $HTML , $lrec , 'element'.$sufix ) );
				}else{
					$this->addBuffer( $tgm->getString( $HTML , null , 'dummy'.$sufix ) );
				}
			}
			$this->addBuffer( $tgm->getString( $HTML , null , 'foot'.$sufix ) );
		}

		return $this->getBuffer();
	}

	/*
	 * 最新データをランダムに並び替えて表示します。
	 * @param &$_gm 現在のページのGUIManagerインスタンスを指定します。
	 * @param $_rec 現在のページのレコードデータを指定します。
	 * @param $_args コマンドコメント引数を指定します。以下の順で指定します。
	 *  @li 0     テーブル名を指定します。
	 *  @li 1     絞込み期間を時間単位で指定します。0で無制限。
	 *  @li 2     最大表示件数を指定します。
	 *  @li 3     テンプレートのパーツ名に付ける接尾子を指定します。
	 *  @li 4     該当項目が無い場合、空のリストの代わりにnotfoundパーツを描画する場合はTRUEを指定します。
	 *  @li 5 ~ n カラム名、演算子、値の順で検索条件を繰り返して指定します。
	 */
	function NewListRandomDraw( &$_gm , $_rec , $_args )
	{
		global $loginUserType;
		global $loginUserRank;

		/*		引数を取得		*/
		$tableName        = array_shift( $_args );
		$limitTime        = array_shift( $_args );
		$maxRow           = array_shift( $_args );
		$partsSuffix      = array_shift( $_args );
		$useNotFoundParts = array_shift( $_args );

		/*		テンプレートを取得		*/
		$HTML = Template::getTemplate( $loginUserType , $loginUserRank , $tableName , 'MOD_ELEMENT_LIST_TEMPLATE' );

		if( !strlen( $HTML ) )
			throw new InternalErrorException( 'does not template' );

		/*		該当テーブルのGUIManagerを取得		*/
		$gm = SystemUtil::getGMforType( $tableName );

		if( !$gm )
			throw new InternalErrorException( 'args error' );

		/*		テーブルを取得		*/
		$db    = $gm->getDB();
		$table = $db->getTable();

		if( 0 < $limitTime )
			$table = $db->searchTable( $table , 'regist' , '>=' , time() - ( $limitTime * 60 * 60 * 24 ) );

		while( count( $_args ) )
		{
			$column = array_shift( $_args );
			$op     = array_shift( $_args );
			$value  = array_shift( $_args );

			$table = $db->searchTable( $table , $column , $op , $value );
		}

		$row = $db->getRow( $table );

		if( !$row )
		{
			/*		notfound出力		*/
			if( $useNotFoundParts )
			{
				$this->addBuffer( $gm->getString( $HTML , null , 'notfound' ) );
				return;
			}
		}

		if( $maxRow > $row ){
			$maxRow = $row;
		}

		/*		リスト出力		*/
		$this->addBuffer( $gm->getString( $HTML , null , 'head' , $partsSuffix ) );

		$SWITCH_RAND_ALGORITHM = 500;
		if( $row < $SWITCH_RAND_ALGORITHM ){
			/*		リミットの付与、ランダムソート		*/
			$table = $db->sortRandom( $db->sortReset( $db->limitOffset( $table,0,$maxRow ) ) );

			$table->onCash();
		for( $i = 0 ; $i < $maxRow ; $i++ )
			{
				$rec = $db->getRecord( $table , $i );
				$this->addBuffer( $gm->getString( $HTML , $rec , 'new' , $partsSuffix ) );
			}
			$table->offCash();
		}else{
			$shuffle = range(0,$SWITCH_RAND_ALGORITHM-1);
			shuffle($shuffle);
			$table = $db->limitOffset( $db->sortDelete($table), $row-$SWITCH_RAND_ALGORITHM,$SWITCH_RAND_ALGORITHM );

			$table->onCash();
		for( $i = 0 ; $i < $maxRow ; $i++ )
		{
				$rec = $db->getRecord( $table , $shuffle[$i] );
			$this->addBuffer( $gm->getString( $HTML , $rec , 'new' , $partsSuffix ) );
			}
			$table->offCash();
		}

		$this->addBuffer( $gm->getString( $HTML , null , 'foot' , $partsSuffix ) );

		return $this->getBuffer();
	}
}


?>