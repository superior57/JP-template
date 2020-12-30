<?php

include_once "./include/base/SQLDatabase.php";

/*******************************************************************************************************
 * <PRE>
 *
 * SQLデータベースシステム　SQLite2用
 *
 * @author 澤健太
 * @author 吉岡幸一郎
 * @original 丹羽一智
 * @version 2.0.0
 *
 * </PRE>
 *******************************************************************************************************/

class SQLDatabase extends SQLDatabaseBase
{

	/**
	 * コンストラクタ。
	 * @param $dbName DB名
	 * @param $tableName テーブル名
	 * @param $colName カラム名を持った配列
	 */
	function __construct($dbName, $tableName, $colName, $colType, $colSize, $colExtend ){
		global $sqlite_db_path;


		$this->connect = sqlite_open( $sqlite_db_path.$dbName.".db", 0666, $SQLITE_ERROR );
		if( !$this->connect ){
//			die( $SQLITE_ERROR );
			throw new InternalErrorException("SQLDatabase() : DB CONNECT ERROR. -> sqlite_open( ".$dbName." )\n");
		}

		$this->init($dbName, $tableName, $colName, $colType, $colSize, $colExtend);


		$this->char_code = 'utf-8';
	}

	function sql_query($sql,$update = false){
		return sqlite_query( $this->connect, mb_convert_encoding( $sql, $this->char_code, mb_internal_encoding()) );
	}

	function sql_fetch_assoc( &$result ,$index){
		sqlite_seek($result , $index);
		return sqlite_fetch_array($result, SQLITE_ASSOC);
	}

	function sql_fetch_array( &$result ){
		return sqlite_fetch_array( $result );
	}

	function sql_fetch_all( &$result ){
		return sqlite_fetch_all( $result );
	}

	function sql_num_rows( &$result ){
		return sqlite_num_rows( $result );
	}

	function sql_convert( $val ){
		return $val;
	}

	function sql_escape($val){
		if(!strlen($val)){return $val;}
		return sqlite_escape_string($val);
	}

	function sql_date_group($column,$format_type,$format=null){

		if(is_null($format)){
			switch($format_type){
				case 'y':
					$format = '%Y';
					break;
				case 'm':
					$format = '%Y-%m';
					break;
				case 'd':
				default:
					$format = '%Y-%m-%d';
					break;
			}
		}
		return "strftime('$format',$column,'unixepoch', 'localtime')";
	}

	//未使用
	private function getColumnType($name){
		return null;
	}

	function to_boolean( $val ){
		if( is_bool( $val ) )		{ return $val; }
		else if( is_string($val )){
			if( $val == 'FALSE' ){ return false; }
			if( $val == 'TRUE' ){ return true; }
		}
		if( $val == 1 || $val == '1')		{ return true; }
		//else if( !strlen($val) )    { return false;}
		return false;
	}

	function sqlDataEscape($val,$type,$quots = true)
	{
		if($type == "boolean"){
			if( SystemUtil::convertBool($val) )	{ return $sqlstr = 1; }
			else								{ return $sqlstr = "''"; }
		}else{
			return parent::sqlDataEscape($val,$type,$quots);
		}
	}

	function getRecord($table, $index){
		$rec = parent::getRecord($table,$index);
		if( $rec != null && strpos( $table->select , $this->tableName.'.' ) !== FALSE ){
			foreach( $rec as $key => $val ){
				if( strpos( $key, $this->tableName.'.' ) !== FALSE ){
					$newrec[ substr($key,strlen($this->tableName)+1) ] = $val;
				}else{
					$newrec[ $key ] = $val;
				}
			}
			return $newrec;
		}
		return $rec;
	}

	function addRecordList($recList, $id_update = TRUE){
		$this->sql_query( 'BEGIN' );

		foreach( $recList as &$rec )
			{ $this->addRecord( $rec ); }

		unset($rec);

		$this->sql_query( 'END' );
		return $recList;
	}

	/*
	 * 複数カラムをまとめてlike検索する
	 */
	private function scCallBack($column)
		{ return $this->tableName.".".$column; }

	function searchConcat(&$tbl,$column,$word){
		$table	 = $tbl->getClone();

		if(is_array($column)){
			if($table->join){ $column = array_map(array($this,"scCallBack"),$column); }
			$column = array_filter($column);
			$column = implode("||' '||", $column);
		}else{
			if($table->join){ $column = $this->tableName.".".$column; }
		}

		$query = "($column) like '%{$word}%'";

		$table->addWhere($query);
		$this->cashReset();

		return $table;
	}

	//暗号化非対応
	function sql_to_encrypt( $str, $key ){
		return $str;
	}
	function sql_to_decrypt( $str, $key ){
		return $str;
	}
	function addPasswordDecrypt( &$tbl ){
		return $tbl;
	}
	function replacePasswordDecrypt( &$rec ){
		return $rec;
	}


	/**
	 * テーブルの指定カラムを置換する。
	 * @param $table replaceを実行するテーブルクラスのインスタンス
	 * @param $column replaceを適用するカラム、配列を渡した場合は各行に対してreplaceが行なわれる
	 * @param $search 検索する文言
	 * @param $replace 置換する文言
	 * @param $set *任意 replaceのupdateを走らせる際に同時に変更を適用したい場合に使用する
	 */
	function replaceTable( $table, $column, $search, $replace, $set = null ){

		switch( $table->status ){
			case TABLE_STATUS_DELETED: $table_name = $this->tableName.'_delete'; break;
			case TABLE_STATUS_NOMAL: $table_name = $this->tableName; break;
			case TABLE_STATUS_ALL: return;
		}

		$datas_list = $this->getDataList($table,$column);
/*
		//callback関数の定義
		//php5.3以降であればラムダ式とクロージャで実現可能
		$func = '
			$set = '.var_export($set,true).';
			$datas["__update__"] = false;

			foreach( ((array)$datas) as $key => $data ){
				if( strlen($data) ){
					$datas[$key] = str_replace( "'.$search.'", "'.$replace.'", $data );
					$datas["__update__"] = true;
				}else{
					unset($datas[$key]);
				}
			}
			foreach( $set as $key => $s ){
				$datas[$key] = $s;
				$datas["__update__"] = true;
			}
			return $datas;
		';

		$callback = create_function('$datas',$func);

		$update_list = array_map( $callback, $datas_list );
*/
		// コールバックの場合、戻り値の配列の整合性が取れないため、修正
		$update_list = array();
		if( is_array($datas_list) ){
			foreach ($datas_list as $key => $data)
			{
				if( strlen($data) ){
					$update_list[$key][$column] = str_replace( $search, $replace, $data );
					if(is_array($set)){
						foreach( $set as $set_key => $s ){
							$update_list[$key][$set_key] = $s;
						}
					}
					$update_list[$key]["__update__"] = true;
				}else{
					unset($update_list[$key]);
				}
			}
		}

		$sql_base = 'UPDATE '.$table_name.' SET ';
		foreach( $update_list as $shadow_id => $update ){
			if( $update['__update__'] ){

				$set_list = Array();
				foreach( $update as $col => $val ){
					if( $col == '__update__' )continue;
					$set_list[] = "$col = ".$this->sqlDataEscape($val, $this->colType[$col] );
				}
				$sql = $sql_base . join(',',$set_list) ." WHERE shadow_id = $shadow_id";

				if( $this->_DEBUG )				{ d( "replaceTable() : ". $sql. "<br/>\n", 'sql' ); }
				$result	 = $this->sql_query( $sql );
				if( !$result ){ throw new InternalErrorException("replaceTable() : SQL MESSAGE ERROR. \n"); }
			}
		}
		return;
	}

	function sortRandom(&$tbl){
		$table	 = $tbl->getClone();
		$table->order[ 'RANDOM()']='';
		return $table;
	}
}

/*******************************************************************************************************/
/*******************************************************************************************************/
/*******************************************************************************************************/
/*******************************************************************************************************/

class Table extends TableBase{

	function __construct($from){

		$this->select	 = '*';
		$this->from		 = $from;
		$this->delete	 = '( delete_key = "" OR delete_key IS NULL )';

		$this->sql_char_code = "UTF-8";
		parent::__construct($from);
	}

	function getLimitOffset(){
		global $SQL_MASTER;
		if( ($this->offset == 0 || $this->offset != null) && $this->limit != null ){
			$str	 = " LIMIT ". $this->offset. ',' .$this->limit;
			return $str;
		}else{
			return "";
		}
	}

	function sql_convert( $val ){
		return $val;
	}
}
?>