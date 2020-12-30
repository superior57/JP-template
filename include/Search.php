<?php

/*******************************************************************************************************
 * <PRE>
 *
 * Searchクラス
 *  検索フォームの生成、
 *  フォームからPostされたデータからGUIManagerの保持するDBから検索結果を返す機能も保有します。
 * @author 丹羽一智
 * @version 3.0.0
 *
 * </PRE>
 *******************************************************************************************************/

class Search
{
	var $addHiddenForm;
	var $gm;

	var $type;

	var $param = null;
	var $value = null;
	var $alias = null;
	var $sort = null;

	var $table_type = "";


	/**
	 * コンストラクタ。
	 * @param $gm GUIManagerオブジェクト
	 */
	function __construct( &$gm = null, $type = null )	{ $this->gm = &$gm; $this->type = $type;}

	/**
	 * 検索パラメータ構文を解析して各パラメータ変数にセットします。
	 * @param $args パラメータ構文のセットされた配列。実際には$_GETが渡される事が多い。
	 */
	function setParamertorSet(&$args){
		global $TABLE_NAME;
		if(is_null($this->param)){ $this->param = Array(); }
		if(is_null($this->value)){ $this->value = Array(); }
		if(is_null($this->alias)){ $this->alias['alias'] = Array(); $this->alias['param'] = Array(); }

		// 検索キーが指定されていない場合
		$db		 = $this->gm->getDB();

		for($i=0; $i<count($db->colName); $i++)
		{
			// カラムの数だけ繰り返す
			$column_name = $db->colName[$i];

			//PALの指定がない検索値に適切なPALを補完する
			if( ( !isset( $args[ $column_name . '_PAL' ] ) || !is_array( $args[ $column_name . '_PAL' ] ) || !count( $args[ $column_name . '_PAL' ] ) ) ) //PAL未指定の場合
			{
				if( ( isset( $args[ $column_name . '_A' ] ) && $args[ $column_name . '_A' ] ) || ( isset( $args[ $column_name . '_B' ] ) && $args[ $column_name . '_B' ] ) ) //範囲検索値がある場合
					{ $args[ $column_name . '_PAL' ] = Array( 'match between' ); }
				else if( isset( $args[ $column_name ] ) && $args[ $column_name ] ) //単一の検索値がある場合
				{
					if( is_array( $args[ $column_name ] ) ) //検索値が配列の場合
						{ $args[ $column_name . '_PAL' ] = Array( 'match and' ); }
					else //検索値がスカラの場合
					{
						if( 'string' == $this->gm->colType[ $column_name ] || 'varchar' == $this->gm->colType[ $column_name ] ) //可変長文字列の場合
							{ $args[ $column_name . '_PAL' ] = Array( 'match like' ); }
						else //それ以外の値の場合
							{ $args[ $column_name . '_PAL' ] = Array( 'match comp' ); }
					}
				}
				else if( 'timestamp' == $this->gm->colType[ $column_name ] ) //検索カラムがタイムスタンプの場合
				{
					$hasYearA  = isset( $args[ $column_name . '_yearA' ] )  && $args[ $column_name . '_yearA' ];
					$hasMonthA = isset( $args[ $column_name . '_monthA' ] ) && $args[ $column_name . '_monthA' ];
					$hasDayA   = isset( $args[ $column_name . '_dayA' ] )   && $args[ $column_name . '_dayA' ];
					$hasYearB  = isset( $args[ $column_name . '_yearB' ] )  && $args[ $column_name . '_yearB' ];
					$hasMonthB = isset( $args[ $column_name . '_monthB' ] ) && $args[ $column_name . '_monthB' ];
					$hasDayB   = isset( $args[ $column_name . '_dayB' ] )   && $args[ $column_name . '_dayB' ];

					if( $hasYearA || $hasMonthA || $hasDayA || $hasYearB || $hasMonthB || $hasDayB ) //いずれかの検索値がある場合
						{ $args[ $column_name . '_PAL' ] = Array( 'match between' ); }
				}
			}

			if(  isset( $args[ $column_name. '_PAL'] )  )
			{
				// 検索パラメータをチェック
				if(   !is_array( $args[ $column_name. '_PAL']  )   )
				{
					// 値が配列の場合はエラー
					throw new InternalErrorException('Search param error -> '. $args[ $column_name ]. '_PAL[] is not array.');
				}
				for($j=0; $j<count( $args[$column_name. '_PAL'] ); $j++)
				{

					$this->param[$column_name] = explode( ' ', $args[$column_name. '_PAL'][$j] );

					//timestampの場合、年月日に分離されている場合があるので結合する。
					if( $db->colType[ $column_name ] == 'timestamp' && $this->param[$column_name][1] == 'between' ){
						$this->joinTimestamp( $column_name, $args );
					}
					if( $db->colType[ $column_name ] == 'date' ){
						$this->joinDate( $column_name, $args, $this->param[$column_name][1] == 'between' );
					}

					if( !isset($args[ $column_name ]) || $args[ $column_name ] == null || $args[ $column_name ] == "" ) { continue; }

					$this->value[$column_name] = $args[ $column_name ];
					if( $this->param[$column_name][1] == 'between' ){
						$this->value[$column_name] = Array( 'A' => $args[ $column_name.'A' ], 'B' => $args[ $column_name.'B' ]);
					}
				}
			}else if( isset( $args[ $column_name ] ) && !is_null(  $args[ $column_name ] ) ){
				// 条件式に値があるかどうか
				throw new InternalErrorException('Search param error -> '. $column_name. '_PAL[] is null.');
			}
		}

		//sortをセット
		if( isset( $args['sort'] ) && strlen( $args['sort'] ) )
		{
			if( !isset( $args['sort_PAL'] ) || $args['sort_PAL'] == 'asc' ){ $this->sort['param'] = 'asc'; }
			else{ $this->sort['param'] = 'desc'; }
			$this->sort['key'] =  $args['sort'];
		}else{
			$this->sort['key'] =  'shadow_id';
			$this->sort['param'] = 'desc';
		}

		//aliasをセット
		foreach( $TABLE_NAME as $tName )
		{// 定義されているテーブル分確認
			if( isset( $args[ $tName.'_alias' ] ) && isset( $args[ $tName. '_alias_PAL'] ) )
			{
				$this->setAlias( $tName, $args[ $tName.'_alias' ] );

				if(is_array($args[ $tName.'_alias_PAL' ])){
					foreach( $args[ $tName.'_alias_PAL' ] as $alias_pal ){
						$param			 = explode( ' ', $alias_pal );
						$this->setAliasParam($tName,$param);

						if( !isset($args[ $param[0] ]) || $args[ $param[0] ] == null || $args[ $param[0] ] == "" ) { continue; }

						$this->value[ $param[0] ] = $args[ $param[0] ];
						if( $param[3] == 'between'){
							$this->value[$param[0]] = Array( 'A' => $args[ $param[0].'A' ], 'B' => $args[ $param[0].'B' ] );
						}
					}
				}
			}
		}
	}

	/**
	 * 検索パラメータをセットします。 必ずsetValueで対応するvalueもセットしてください
	 * @param $var セットするパラメータ。
	 */
	function setParamertor($column_name,$var){
		if(is_null($this->param)){
			$this->param = Array();
		}
		$this->param[$column_name] = $var;
	}

	/**
	 * 検索データをセットします。
	 * @param $var セットするデータ
	 */
	function setValue($column_name,$var,$key=null){
		if(is_null($this->value)){
			$this->value = Array();
		}
		if(is_null($key))
			$this->value[$column_name] = $var;
		else
			$this->value[$column_name][$key] = $var;
	}

	/**
	 * alias検索ベースパラメータをセットします。
	 * @param $var セットするデータ
	 */
	function setAlias($table_name,$var){
		if(!isset($this->alias['alias'][$table_name]) || is_null($this->alias['alias'][$table_name])){
			$this->alias['alias'][$table_name] = Array();
		}
		$this->alias['alias'][$table_name] = $var;
	}
	/**
	 * alias検索パラメータをセットします。
	 * @param $var セットするデータ
	 */
	function setAliasParam($table_name,$var){
		if(!isset($this->alias['param'][$table_name]) || is_null($this->alias['param'][$table_name])){
			$this->alias['param'][$table_name] = Array();
		}
		$this->alias['param'][$table_name][]= $var;
	}

	/**
	 * 検索データをゲットします。
	 * @param $var セットするデータ
	 */
	function getValue( $column_name,$key=null ){
		if(is_null($this->value) || !isset( $this->value[$column_name] ) ){
			return null;
		}
		if( is_null($key) || !is_array($this->value[$column_name]))
			return $this->value[$column_name];
		else
			return $this->value[$column_name][$key];

	}

	/**
	 * フォームを描画します。
	 * @param $html デザインHTMLファイル
	 * @param $jump=null submitで飛ぶ先
	 * @param $partkey=null 分割キー
	 */
	function drawForm( $html, $jump = null, $partkey = null )
	{ print $this->getFormString( $html, $jump, $partkey ); }

	/**
	 * フォームを描画します。
	 * @param $html デザインHTMLファイル
	 * @param $jump=null submitで飛ぶ先
	 * @param $partkey=null 分割キー
	 */
	function getFormString( $html, $jump = null, $partkey = null, $form_flg = null )
	{
		if( !isset($form_flg) ) { $form_flg = $this->gm->form_flg; }
		switch($form_flg)
		{
			case 'variable':
			case 'v':
				return $this->getFormStringSetVariable( $html, $jump, $partkey );
				break;
			case 'buffer':
			case 'b':
			default:
				return $this->getFormStringSetBuffer( $html, null, $jump, $partkey );
				break;
		}
	}

	/**
	 * フォームつきHTMLデータを取得します。
	 * @param $html デザインHTMLファイル
	 * @param $rec=null レコードデータ
	 * @param $jump=null submitで飛ぶ先
	 * @param $partkey=null 分割キー
	 */
	function getFormStringSetBuffer( $html, $rec = null, $jump = null, $partkey = null )
	{
		$buffer	 = "";
		$buffer	 .= '<form name="search_form" method="get" action="'. $jump .'" style="margin: 0px 0px;">';
		$this->gm->addHiddenForm('run', 'true');
		$this->gm->addForm	 .= $this->addHiddenForm;
		$this->addHiddenForm = "";
		$this->gm->authenticity_token = false;
		$buffer	 .= $this->gm->getFormString($html, null, null, $partkey, 'buffer');
		$buffer	 .= '</form>';

		return $buffer;
	}

	/**
	 * フォームを描画します。
	 *
	 *  formタグ等をvariableにセットしてテンプレートに渡します。
	 *  header部等でformが使われていて、getFormStringだとformがネストしてしまう時にお使いください。
	 *
	 * @param $html デザインHTMLファイル
	 * @param $jump=null submitで飛ぶ先
	 * @param $partkey=null 分割キー
	 */
	function getFormStringSetVariable( $html, $jump = null, $partkey = null )
	{
		$this->gm->addHiddenForm('run', 'true');
		$this->gm->addForm	 .= $this->addHiddenForm;
		$this->addHiddenForm = "";
		$this->gm->setVariable('form_begin','<form name="search_form" method="get" action="'. $jump .'" style="margin: 0px 0px;">'.$this->gm->addForm);
		$this->gm->setVariable('form_end','</form>');
		$this->gm->addForm = "";
		$this->gm->authenticity_token = false;
		$buffer	 = $this->gm->getFormString($html, null, null, $partkey, 'buffer');

		return $buffer;
	}

	/**
	 * 不可視フォームを追加します。
	 * @param $name フォーム名
	 * @param $val フォームの値
	 */
	function addHiddenForm($name, $val)
	{ $this->addHiddenForm .= '<input type="hidden" name="'. $name .'" value="'. $val .'" />'. "\n"; }

	var $aliasDB;

	/**
	 * エイリアスで用いるGUIManagerを追加。
	 * $name という名前のtableをalias表示用テーブルとして生成、記憶する。
	 * コマンドコメントのエイリアスコマンドにより描画が要求された際には
	 * このGUIManagerを用いて描画処理を行う。
	 * @param $name 名前
	 * @param $gm GUIManager オブジェクト
	 */
		function addAlias($name)	{	global $gm;		$this->aliasDB[$name]	 = $gm[ $name ]->db; }

	/**
	 * 検索結果を取得します。
	 * 検索結果の取得にはclassのプロパティにセットされた値($param,$value)を使います。
	 * 値がセットされていない場合、アクセス時に渡されたGET内容を用います。
	 * @param $gmkey=null _SKEY構文を持つDBを所持するGUIManagerオブジェクト
	 * @param $reckey=null $gmkeyに渡ってきたGUIManagerから取得したレコード
	 * @return 検索結果のテーブル
	 */
	function getResult( $args = null )
	{
		if( !is_null($args) ){ $this->setParamettorSet($args); }
		else if( is_null($this->param) ){ $this->setParamertorSet($_GET); }

		// 検索キーが指定されていない場合
		$db		 = $this->gm->getDB();
		$table	 = $db->getTable( $this->table_type );

		foreach( $this->param as $column_name => $param ){
			if( !isset($this->value[ $column_name ]) || $this->value[ $column_name ] == null || $this->value[ $column_name ] == "" ) { continue; }
			if(is_array($this->value[ $column_name ])){
				$this->value[ $column_name ] = array_filter($this->value[ $column_name ]);
				if(count($this->value[ $column_name ])==0){continue;}
			}
			$table = $this->searchTable( $db , $table, $column_name, $param , $this->value[ $column_name ] );
		}
		// alias検索を実行
		$table = $this->searchAlias( $db , $table );

		$table = $db->sortTable( $table, $this->sort['key'], $this->sort['param'] );

		return $table;
	}


	/**
	 * alias検索結果を取得します。
	 *
	 *パラメータ記述例
	 *<input name="cUser_alias" type="hidden" value="owner id match or">
	 *<input name="cUser_alias_PAL[]" type="hidden" value="companyName name matchlike">
	 *<input name="cUser_alias_PAL[]" type="hidden" value="area area match like">
	 *<input name="cUser_alias_PAL[]" type="hidden" value="line line match like">
	 *
	 * @param $db 検索対象のDB
	 * @param $table 検索対象のテーブル
	 * @return 検索結果のテーブル
	 */
	function searchAlias( $db, $table )
	{
		global $TABLE_NAME;
		global $gm;

		if( is_array($this->alias['alias']) && count($this->alias['alias']) ){
			foreach( $this->alias['alias'] as $tName => $alias )
			{// 定義されているテーブル分確認
				if( isset( $alias ) && isset( $this->alias['param'][$tName] ) )
				{
					$param			 = explode( ' ', $alias );
					$base_colum		 = array_shift($param);
					$alias_colum	 = array_shift($param);
					$tDb			 = $gm[$tName]->getDB();

					$fast_alias = true;

					foreach( $this->alias['param'][$tName] as $key => $param )
					{
						// aliasテーブルに検索条件をセット
						$data_colum		 = array_shift($param);
						$search_colum	 = array_shift($param);

						if( !isset($this->value[ $data_colum ]) || $this->value[ $data_colum ] == null || $this->value[ $data_colum ] == "" ) { continue; }

						if( $fast_alias ){
							$table = $db->joinTable( $table, $this->type, $tName, $base_colum, $alias_colum );

							$fast_alias = false;
						}

						$data = $this->value[ $data_colum ];

						$table = $this->searchTable( $db, $table, $search_colum,  $param , $data , $tDb );
					}
				}
			}
		}
		return $table;

	}


	/**
	 * 実際に検索処理を行ないます。
	 *
	 * @param $db    検索を行なうテーブルのデータベース
	 * @param $table 検索を行なうテーブル
	 * @param $name  検索対象のカラム
	 * @param $param 検索に使用するパラメータ配列
	 * @param $data  検索に使用するデータ
	 * @return 検索結果のテーブル
	 */
	function searchTable( $db , $table , $name , $param , $data , $join_db = null ){

		switch( $param[0] )
		{
			case 'alias'://古い記法  現在は非推奨。  新しいもに関してはsearchAliasを参照
				//実際に検索で来たデータで別テーブルを検索し、その結果のカラムを使って元々検索してるテーブルの任意のカラムを検索
				//data:置き換え元データ
				//column_name:検索カラム
				//param:
				//    0:
				//    1:alias検索時を一致検索にするかlike検索にするか
				//    2:置き換え元テーブル
				//    3:置き換え元の検索カラム
				//    4:置き換え元のキーカラム
				//    5:matchパラメータ
				//関連データの補正後、matchと同処理
				//
				//拡張
				//    同検索テーブルの別項目を検索対象にする場合は続けて記載する。
				//    6:置き換え元の検索カラム
				//    7:検索に使うキー情報
				//    8:一致検索かlike検索か
				//(以上3項目をループ

				if( is_array( $data ) )
				{// 値が配列の場合はエラー
					throw new InternalErrorException('Search param error -> '. $name. '_PAL[] is alias. but '. $name .' is array.');
				}

				if( !isset( $this->aliasDB[ $param[2] ] ) )	{ $this->addAlias( $param[2] ); }

				if( $param[5] == 'between' ){
					// エイリアスにbetweenは無理ぽ(今のところ
					throw new InternalErrorException('Search param error -> '. $name. '_PAL[] is alias+between. but between is alias.');
				}

				$tdb	 = $this->aliasDB[ $param[2] ];
				if( $param[1] == 'comp' ){
					$ttable = $tdb->searchTable( $tdb->getTable() , $param[3] , "==" , $data );
				}else{
					$ttable = $tdb->searchTable( $tdb->getTable() , $param[3] , "=" , '%'.$data.'%' );
				}

				if( count($param) > 6 ){
					$cnt = count($param);

					for( $k=6 ; $k < $cnt ; $k+=3 ){
						if(   is_array(  $this->value[$param[$k+1]]  )   ){
							// 値が配列の場合はエラー
							throw new InternalErrorException('Search param error -> '. $param[$k] . '_PAL[] is alias. but '. $param[$k] .' is array.');
						}
						if( $param[$k+2] == 'comp' )
						$ttable = $tdb->searchTable( $ttable , $param[$k] , "==" , $this->value[$param[$k+1]] );
						else
						$ttable = $tdb->searchTable( $ttable , $param[$k] , "=" , "%".$this->value[$param[$k+1]]."%" );

					}
				}

				$trow = $tdb->getRow( $ttable );

				if( $trow != 0 ){
					if( $param[5] == 'and' || $param[5] == 'or' ){
						//配列
						$data = Array();
						$ttable->onCashe();
						for( $k=0;$k<$trow;$k++ ){
							$data[] = $tdb->getData( $tdb->getRecord( $ttable  , $k ) , $param[4] );
						}
						$ttable->offCashe();
					}else{
						//単一データ
						$data = $tdb->getData( $tdb->getRecord( $ttable  , 0 ) , $param[4] );
					}
				}else{
					//結果無し
					$table = $db->getEmptyTable();
					break;
				}
				$param[1] = $param[5];

			case 'match':
				// 一致系検索の場合
				switch( $param[1] )
				{
					case 'comp':
						// 完全一致の場合
						if(   is_array(  $data  )   )
						{// 値が配列の場合はエラー
							throw new InternalErrorException('Search param error -> '. $name. '_PAL[] is match+comp. but '. $name .' is array.');
						}
						$table = $this->searchExecute( $db, $join_db, $table, $name, '=', ($data));
						break;
					case 'like':
						// 部分一致の場合
						if(   is_array(  $data  )   )
						{// 値が配列の場合はエラー
							throw new InternalErrorException('Search param error -> '. $name. '_PAL[] is match+like. but '. $name .' is array.');
						}
						$table = $this->searchExecute( $db, $join_db, $table, $name, '=', '%'. ($data). '%' );
						break;

					case 'not':
						// 否定一致の場合
						if(   is_array(  $data  )   )
						{// 値が配列の場合はエラー
							throw new InternalErrorException('Search param error -> '. $name. '_PAL[] is match+not. but '. $name .' is array.');
						}
						$table = $this->searchExecute( $db, $join_db, $table, $name, '!=', ($data) );
						break;

					case 'keyword':
						// キーワード検索の場合
						$table = $this->searchKeyword( $db, $table, $name, $data, array($name), $join_db );
						break;

					case 'between':
						$table = $this->searchBetween( $db, $table, $name, $data[ 'A' ], $data[ 'B' ], $join_db );
						break;

					case 'lt':
						$table = $this->searchExecute( $db, $join_db, $table, $name, '<', $data );
						break;

					case 'lteq':
						$table = $this->searchExecute( $db, $join_db, $table, $name, '<=', $data );
						break;

					case 'gt':
						$table = $this->searchExecute( $db, $join_db, $table, $name, '>', $data );
						break;

					case 'gteq':
						$table = $this->searchExecute( $db, $join_db, $table, $name, '>=', $data );
						break;

					case 'or':
						if(isset($param[2])){ $table = $this->searchOR( $db , $table , $name, $data, $join_db, $param[2] ); }
						else				{ $table = $this->searchOR( $db , $table , $name, $data, $join_db); }
						break;
					case 'and':
						$table = $this->searchAND( $db , $table , $name, $data, $join_db);
						break;
					case 'in':
						$table = $this->searchIN( $db , $table , $name, $data, $join_db);
						break;
					case 'empty':
						$table = $this->searchExecute( $db, $join_db, $table, $name, '=', '' );
						break;
				}
			break;
			case 'unmatch':
				// 非一致系検索の場合
				switch( $param[1] )
				{
					case 'comp':
						// 完全一致の場合
						if(   is_array(  $data  )   )
						{// 値が配列の場合はエラー
							throw new InternalErrorException('Search param error -> '. $name. '_PAL[] is unmatch+comp. but '. $name .' is array.');
						}
						$table = $this->searchExecute( $db, $join_db, $table, $name, '!', ($data));
						break;
					case 'like':
						// 部分一致の場合
						if(   is_array(  $data  )   )
						{// 値が配列の場合はエラー
							throw new InternalErrorException('Search param error -> '. $name. '_PAL[] is unmatch+like. but '. $name .' is array.');
						}
						$table = $this->searchExecute( $db, $join_db, $table, $name, '!', '%'. ($data). '%' );
						break;
					case 'keyword':
						// キーワード検索の場合
						// unmatchとkeywordは組み合わせ不可
						throw new InternalErrorException('Search param error -> '. $name. '_PAL[] is unmatch+keyword.');

					case 'between':
						$table = $this->searchBetween( $db, $table, $name, $data[ 'A' ], $data[ 'B' ], $join_db , 'unmatch' );
						break;

					case 'lt':
						$table = $this->searchExecute( $db, $join_db, $table, $name, '>=', $data );
						break;

					case 'lteq':
						$table = $this->searchExecute( $db, $join_db, $table, $name, '>', $data );
						break;

					case 'gt':
						$table = $this->searchExecute( $db, $join_db, $table, $name, '<=', $data );
						break;

					case 'gteq':
						$table = $this->searchExecute( $db, $join_db, $table, $name, '<', $data );
						break;

					case 'or':
						if(isset($param[2])){ $table = $this->searchOR( $db , $table , $name, $data, $join_db, $param[2] , 'unmatch' ); }
						else				{ $table = $this->searchOR( $db , $table , $name, $data, $join_db , null , 'unmatch' ); }
						break;
					case 'and':
						$table = $this->searchAND( $db , $table , $name, $data, $join_db , 'unmatch');
						break;
					case 'in':
						$table = $this->searchIN( $db , $table , $name, $data, $join_db , 'unmatch');
						break;
					case 'empty':
						$table = $this->searchExecute( $db, $join_db, $table, $name, '!', '' );
						break;
				}
				break;
					case 'group':
						// グルーピング検索の場合
						switch( $param[1] )
						{
							default:
								$param[2] = $param[1];
							case 'file':
								$table = $this->searchFile( $db, $table, $name, $data, $param[2] );
								break;
							case 'table':
								//$param
								//    2:一番上の親Tableの名前
								//    3:親テーブルの名前
								//    4:子テーブルの名前
								//    5-:以下 3~4の繰り返し(親から子の順で
								$table = $this->searchMultipleTable( $db, $table, $name, $data, array_slice( $param , 2 ), $this->type, $join_db );
								break;
							case 'keyword':
								// キーワード検索の場合
								//（$paramに検索に使うカラムを羅列して渡す
								$table = $this->searchKeyword( $db, $table, $name, $data, array_slice( $param , 2 ), $join_db );
								break;
						}
						break;
					case 'array':
						$table = $this->searchTable($db,$table,$name,array_slice($param,1),explode('/',$data),$join_db);
						break;
		}
		return $table;
	}


	/*******************************************************************************************************
	 *
	 * search extension
	 *
	 *  SQL::searchTableで出来ないフィルタを実現する為のメソッド郡
	 *  systemのサーチで使用さているものと同じ機能をどこでも利用出来る。
	 *
	 *******************************************************************************************************/

	/**
	 * 配列によるOR検索を行ないます。
	 *
	 * @param $db    検索するテーブルの続するデータベース
	 * @param $table 検索するテーブル
	 * @param $name  検索するカラム名
	 * @param $data  検索用の配列
	 * @return 検索結果のテーブル
	 */
	function searchOR( &$db , $table , $name, $data, $join_db = null , $param = null , $unmatch = false ){
		if( !is_array(  $data  ) ) { $data = explode( '/', $data); }

		$blankTable = $db->getTable();
		$ttable	 = array();
		for($k=0; $k<count($data); $k++)	{
			if( !is_null($param) && $param == "comp" )	{	$str = ($data[$k]);	}
			else
			{
				switch( $db->colType[ $name ] )
				{
					case 'int' :
					case 'double' :
                    case 'timestamp' :
					case 'boolean' :
						$str = $data[$k];
						break;

					default :
						$str = "%".($data[$k])."%";
						break;
				}
			}

			if( 'unmatch' == $unmatch )
				{ $ttable[]	 = $this->searchExecute( $db, $join_db, $blankTable, $name, '!', $str ); }
			else
				{ $ttable[]	 = $this->searchExecute( $db, $join_db, $blankTable, $name, '=', $str ); }
		}
		$blankTable	 = $ttable[0];
		for($k=1; $k<count($data); $k++)	{ $blankTable		 = $db->orTable( $blankTable, $ttable[$k] ); }
		$table = $db->andTable( $table , $blankTable );

		return $table;
	}
	function searchIN( &$db , $table , $name, $data , $join_db = null , $unmatch = false ){
		if(   !is_array(  $data  )   )
		{
			$data = explode( '/', $data);
			
			// 値が配列じゃない場合はエラー
			//throw new InternalErrorException('Search param error -> '. $name. '_PAL[] is match+or. but '. $name .' is not array.');
		}

		if( 'unmatch' == $unmatch )
			{ return $this->searchExecute( $db, $join_db, $table, $name, 'not in', $data ); }
		else
			{ return $this->searchExecute( $db, $join_db, $table, $name, 'in', $data ); }
	}

	/**
	 * 配列によるAND検索を行ないます。
	 *
	 * @param $db    検索するテーブルの続するデータベース
	 * @param $table 検索するテーブル
	 * @param $name  検索するカラム名
	 * @param $data  検索用の配列
	 * @return 検索結果のテーブル
	 */
	function searchAND( &$db , $table , $name, $data , $join_db = null , $unmatch = false ){
		if( !is_array(  $data  ) ) { $data = array($data); }

		$blankTable = $db->getTable();

		$ttable	 = array();
		for($k=0; $k<count($data); $k++){

			if( 'unmatch' == $unmatch )
				{ $ttable[]	 = $this->searchExecute( $db, $join_db, $blankTable, $name, '!', '%'.($data[$k]).'%' ); }
			else
				{ $ttable[]	 = $this->searchExecute( $db, $join_db, $blankTable, $name, '=', '%'.($data[$k]).'%' ); }
		}

		$blankTable	 = $ttable[0];
		for($k=1; $k<count($data); $k++)	{ $blankTable		 = $db->andTable( $blankTable, $ttable[$k] ); }

		$table = $db->andTable( $table , $blankTable );

		return $table;
	}


	/**
	 * 範囲検索を行ないます。
	 *
	 * @param $db    検索するテーブルの続するデータベース
	 * @param $table 検索するテーブル
	 * @param $name  検索するカラム名
	 * @param $dataA  検索用の配列
	 * @param $dataB  検索用の配列
	 * @return 検索結果のテーブル
	 */
	function searchBetween( &$db , $table , $name, $dataA, $dataB , $join_db = null , $unmatch = false ){
		if(   is_null(  $dataA  ) && is_null(  $dataB  )   )
		{// 値が存在しない場合はスルー
			return $table;
		}else if( is_null( $dataA ) ){
			$dataA = "";
		}else if( is_null( $dataB ) ){
			$dataB = "";
		}

		if(strpos( $dataA ,'*') !== FALSE){ $tmpA = explode( '*', $dataA ); $dataA = $tmpA[1]; }
		if(strpos( $dataB ,'*') !== FALSE){ $tmpB = explode( '*', $dataB ); $dataB = $tmpB[2]; }

		if($dataA == "bottom" || $dataA == "" ){
			if($dataB == "top" || $dataB == "" ){
				//検索しない。
				return $table;
			}
			//下限のみ無し

			if( 'unmatch' == $unmatch )
				{ $table	 = $this->searchExecute( $db, $join_db, $table, $name, '>', $dataB  ); }
			else
				{ $table	 = $this->searchExecute( $db, $join_db, $table, $name, '<=', $dataB  ); }

			return $table;
		}else if($dataB == "top" ||  $dataB == "" ){
			//上限のみ無し

			if( 'unmatch' == $unmatch )
				{ $table	 = $this->searchExecute( $db, $join_db, $table, $name, '<', $dataA  ); }
			else
				{ $table	 = $this->searchExecute( $db, $join_db, $table, $name, '>=', $dataA  ); }

			return $table;
		}

		switch( $this->gm->colType[ $name ] ){
			case 'int':
			case 'timestamp':
				$dataA	 = (int)$dataA;
				$dataB	 = (int)$dataB;
				break;
			case 'double':
				$dataA	 = doubleval($dataA);
				$dataB	 = doubleval($dataB);
				break;
			case 'date':
				$dataA	 = (string)$dataA;
				$dataB	 = (string)$dataB;
				break;
			default:
				throw new InternalErrorException('Search param error -> '. $name. '_PAL[] is between. but '. $name .' is not number.');
		}

		if( 'unmatch' == $unmatch )
			{
				if( $dataA < $dataB )
				{
					$table	 = $this->searchExecute( $db, $join_db, $table, $name, '<', $dataA );
					$table	 = $this->searchExecute( $db, $join_db, $table, $name, '>', $dataB );
				}
				else
				{
					$table	 = $this->searchExecute( $db, $join_db, $table, $name, '<', $dataB );
					$table	 = $this->searchExecute( $db, $join_db, $table, $name, '>', $dataA );
				}
			}
		else
			{ $table	 = $this->searchExecute( $db, $join_db, $table, $name, 'b', $dataA,$dataB  ); }

		return $table;
	}

	/**
	 * ファイルによるグルーピング検索を行ないます。
	 * 互換性の為に用意していますが、あまり推奨されません、tableを利用する形が推奨されます。
	 *
	 * @param $db    検索するテーブルの続するデータベース
	 * @param $table 検索するテーブル
	 * @param $name  検索するカラム名
	 * @param $data  検索用の配列
	 * @param $file  グループ検索用のファイル名
	 * @return 検索結果のテーブル
	 */
	function searchFile( &$db , $table , $name , $data , $file ){
		if(  !file_exists( './group/'. $file )  )
		{
			// グルーピングファイルが見つからない場合
			throw new InternalErrorException( 'Search param error -> '. $name. '_PAL[] is group. but group file not found : ./group/'. $file );
		}
		if(   is_array(  $data  )   )
		{
			// 値が配列の場合はエラー
			throw new InternalErrorException('Search param error -> '. $name. '_PAL[] is group. but '. $name .' is array.');
		}

		// グルーピングファイルを開く
		$fp		 = fopen ( './group/'. $file, 'r' );
		$flg	 = false;
		while(  !feof( $fp ) )
		{
			$buffer	 = fgets( $fp, 20480 );
			$group	 = explode( ',', $buffer );
			if(  count( $group ) < 2  )	{ continue; }
			if(  $data == $group[0]  )
			{
				// グルーピング検索対象の場合。
				if( $group[1] == 'all' || $group[1] == 'ALL' )
				{
					// グルーピングパラメータが ALL の場合はそのまま返す
					$flg	 = true;
					break;
				}
				else
				{
					// グルーピング処理
					$ttable	 = array();
					for($k=1; $k<count( $group ); $k++)
					{
						if( $group[$k] == null || $group[$k] == '' ) {	continue; }
						$ttable[]	 = $db->searchTable( $table, $name, '=', '%'. $group[$k]. '%' );
					}
					$table	 = $ttable[0];
					for($k=1; $k<count($ttable); $k++)	{ $table		 = $db->orTable( $table, $ttable[$k] ); }
					$flg	 = true;
				}
			}
		}
		fclose( $fp );

		if( !$flg )
		{
			// どの親にも一致しなかった場合は部分一致検索
			$table	 = $db->searchTable( $table, $name, '=', '%'.( $data). '%' );
		}
		return $table;
	}

	/**
	 * グループ検索を行ないます。。
	 *  $param
	 *    0:一番上の親Tableの名前
	 *    1:親テーブルの名前
	 *    2:子テーブルの名前
	 *    3-:以下 1~2の繰り返し(親から子の順で
	 *
	 * @param $db    検索するテーブルの続するデータベース
	 * @param $table 検索するテーブル
	 * @param $name  検索するカラム名
	 * @param $param 検索用のパラメータ配列
	 * @param $type  検索対象のテーブル名
	 * @return 検索結果のテーブル
	 */
	function searchMultipleTable( &$db , $table , $name , $data , $param, $type , $join_db = null ){
		if(   is_array(  $data  )   )
		{
			// 値が配列の場合はエラー
			throw new InternalErrorException('Search param error -> '. $name. '_PAL[] is match+comp. but '. $name .' is array.');
		}

		//親か子かを判断
		if( strpos( $data , $ID_HEADER[ $type ] ) === 0 ){
			//子である場合
			$table	 = $db->searchTable( $table, $name, '=', ($data) );
			return $table;;
		}else if(strpos( $data , $ID_HEADER[ $param[0] ] ) === 0){
			//祖先(トップ親)である
			$start = true;
		}else{
			$start = false;
		}

		$atable = Array();
		$atable[] = $data;
		$trow = 1;
		$cnt = ( count($param) - 1 ) /2;
		//祖先ではないが子でもない場合
		for($k=0;$k<$cnt;$k++){
			$table_num = 1+$k*2;
			$key_num = 2+$k*2;

			if( !isset( $param[$table_num] ) || !strlen( $param[$table_num] ) )
			{
				// テーブル名が存在しない場合エラー
				throw new InternalErrorException('Search param error -> '. $name. '_PAL[] is group. but table name not found.');
			}

			if( !isset( $this->aliasDB[ $param[$table_num] ] ) )	{ $this->addAlias( $param[$table_num] ); }

			//開始するまでcontinue
			if( !$start ){
				if(strpos( $data , $ID_HEADER[ $param[$table_num] ] ) === 0 ){
					$start = true;

				}
				continue;
			}

			if( !isset( $param[$key_num] ) || !strlen( $param[$key_num] ) ){ $param[$key_num] = 'parent'; }

			$tdb	 = $this->aliasDB[ $param[$table_num] ];

			$atable2 = Array();
			for($l=0; $l<$trow; $l++){
				$ttable	 = $tdb->searchTable( $tdb->getTable(), $param[$key_num], '=', $atable[$l] );
				$trow2 = $tdb->getRow( $ttable );
				$ttable->onCashe();
				for($m=0; $m<$trow2; $m++){
					$atable2[] = $tdb->getData( $tdb->getRecord( $ttable , $m ) , 'id' );
				}
				$ttable->offCashe();
			}
			$atable = $atable2;
			$trow = $trow2;
		}

		$ttable = Array();
		for($k=0; $k<$trow; $k++){
			$ttable[]	 = $db->searchTable( $table, $name, '=', $atable[$k] );
		}

		$table	 = $ttable[0];
		for($k=1; $k<$trow; $k++)	{ $table		 = $db->orTable( $table, $ttable[$k] ); }
		return $table;
	}

	/**
	 * キーワードによる検索を行ないます。
	 *
	 * @param $db    検索するテーブルの続するデータベース
	 * @param $table 検索するテーブル
	 * @param $data  検索文字列
	 * @param $param 検索用パラメータ配列、検索に使うカラムを羅列する
	 * @return 検索結果のテーブル
	 */
	function searchKeyword( &$db , $table , $name , $data , $param , $join_db = null , $unmatch = false ){
		if(   is_array(  $data  )   )
		{
			// 値が配列の場合はエラー
			throw new InternalErrorException('Search param error -> '. $name. '_PAL[] is match+keyword. but '. $name .' is array.');
		}
		$data	 = str_replace( "　", " ", ($data) );
		$key	 = explode(  ' ', $data  );
		$ttable	 = null;
		for( $k=0; $k<count($key); $k++ )
		{
			if(  substr( $key[$k], 0, 1 ) == '-'  ) {
				$keyword = '%'.  substr( $key[$k], 1 ). '%';
				$ttable[$k]	 =  $this->searchExecute( $db, $join_db, $table, $param[0],'!', $keyword );
				for( $l=1; $l < count($param) ; $l++ ){
					$ttable[$k]	 = $db->andTable( $db->searchTable( $table, $param[$l] , '!', $keyword ) , $ttable[$k] );
				}
			}
			else{
				$keyword = '%'. $key[$k]. '%';
				$ttable[$k]	 =  $this->searchExecute( $db, $join_db, $table, $param[0], '=', $keyword );
				for( $l=1; $l < count($param) ; $l++ ){
					$ttable[$k]	 = $db->orTable( $db->searchTable( $table, $param[$l] , '=', $keyword ) , $ttable[$k] );
				}
			}
		}
		$table	 = $ttable[0];
		for( $k=1; $k<count($ttable); $k++ )		{ $table	 = $db->andTable( $table, $ttable[$k] ); }
		return $table;
	}

	function paramReset(){ $this->param = Array(); $this->value = Array(); $this->alias['alias'] = Array(); $this->alias['param'] = Array(); $this->sort['key'] =  'SHADOW_ID'; $this->sort['param'] = 'desc'; }

	function joinTimestamp( $column_name, &$args ){

		$a = $column_name.'A';
		$b = $column_name.'B';
		$y_a = $column_name.'_yearA';
		$m_a = $column_name.'_monthA';
		$d_a = $column_name.'_dayA';
		$y_b = $column_name.'_yearB';
		$m_b = $column_name.'_monthB';
		$d_b = $column_name.'_dayB';

		$ret = false;

		if( strlen($args[$y_a]) || strlen($args[$m_a]) || strlen($args[$d_a]) ){
			$time = mktime(0,0,0,
			strlen($args[$m_a]) ? $args[$m_a]-0 : 1,
			strlen($args[$d_a]) ? $args[$d_a]-0 : 1,
			strlen($args[$y_a]) ? $args[$y_a]-0 : 1970
			);
			$this->setValue( $column_name, $time , 'A' );
			$this->setValue( $column_name, '' , 'B' );
			$args[$a] = $time;
		}else if( isset($args[$a]) && strlen($args[$a]) && $args[$a] != 'bottom' ){
			$args[$m_a] = date('n',$args[$a]);
			$args[$d_a]  = date('j',$args[$a]);
			$args[$y_a] = date('Y',$args[$a]);
		}

		if( strlen($args[$y_b]) || strlen($args[$m_b]) || strlen($args[$d_b]) ){
			$time = mktime(0,0,0,
			strlen($args[$m_b]) ? $args[$m_b]-0 : 12,
			strlen($args[$d_b]) ? $args[$d_b]+1 : 31,
			strlen($args[$y_b]) ? $args[$y_b]-0 : 2069
			);
			$this->setValue( $column_name, $time , 'B' );
			$args[$b] = $time;
			if( $this->getValue( $column_name,'A') == null ){
				$this->setValue( $column_name, '' , 'A' );
			}
		}else if( isset($args[$b]) && strlen($args[$b]) && $args[$b] != 'top' ){
			$args[$m_b] = date('n',$args[$b]);
			$args[$d_b]  = date('j',$args[$b]);
			$args[$y_b] = date('Y',$args[$b]);
		}

		return $ret;
	}

	private function joinDate( $column_name, &$args, $between = false ){

		if( $between ){
			$a = $column_name.'A';
			$b = $column_name.'B';
			$y_a = $column_name.'A_year';
			$m_a = $column_name.'A_month';
			$d_a = $column_name.'A_day';
			$y_b = $column_name.'B_year';
			$m_b = $column_name.'B_month';
			$d_b = $column_name.'B_day';
			if( strlen($args[$y_a]) ){
	        	$args[$a] = sprintf("%4d-%02d-%02d",$args[$y_a],strlen($args[$m_a])?$args[$m_a]:1,strlen($args[$d_a])?$args[$d_a]:1);
			}

			if( strlen($args[$y_b]) ){
	        	$args[$b] = sprintf("%4d-%02d-%02d",$args[$y_b],strlen($args[$m_b])?$args[$m_b]:1,strlen($args[$d_b])?$args[$d_b]:1);
			}
		}else{
			$y = $column_name.'_year';
			$m = $column_name.'_month';
			$d = $column_name.'_day';

			if( strlen($args[$y]) || strlen($args[$m]) || strlen($args[$d]) ){

				$args[$column_name] = "";

				if( strlen($args[$y]) ){
		        	$args[$column_name] .= sprintf("%4d",$args[$y]);
				}else{
					$args[$column_name] .= '%';
				}

				$args[$column_name] .= '-';

				if( strlen($args[$m]) ){
		        	$args[$column_name] .= sprintf("%02d",$args[$m]);
				}else{
					$args[$column_name] .= '%';
				}
				$args[$column_name] .= '-';

				if( strlen($args[$d]) ){
		        	$args[$column_name] .= sprintf("%02d",$args[$d]);
				}else{
					$args[$column_name] .= '%';
				}
//TODO:%を文字数の?に置き換え可能か(もしくは［0－9］
			}
		}
	}

	private function searchExecute(  SQLDatabase &$db,$join_db,Table &$tbl, $name, $opp, $val, $val2 = null ){

		if($join_db==null){
			return $db->searchTable( $tbl, $name, $opp, $val, $val2 );
		}else{
			return $db->joinTableSearch( $join_db, $tbl, $name, $opp, $val, $val2 );
		}
		return $tbl;
	}
}

/*******************************************************************************************************/

?>