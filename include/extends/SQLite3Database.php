<?php

include_once "./include/base/SQLDatabase.php";

/*******************************************************************************************************
 * <PRE>
 *
 * SQLデータベースシステム　SQLite用
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
    static  $commonConnect  = null;
    static  $referenceCount = 0;

	/**
	 * コンストラクタ。
	 * @param $dbName DB名
	 * @param $tableName テーブル名
	 * @param $colName カラム名を持った配列
	 */
	function __construct($dbName, $tableName, $colName, $colType, $colSize, $colExtend ){
		global $sqlite_db_path;

        if( !self::$commonConnect )
        {
            try{
                self::$commonConnect = new  SQLite3( $sqlite_db_path.$dbName.".db" );
            }catch( Exception $e ){
                exit("SQLDatabase() : DB CONNECT ERROR. new SQLite3( ".$dbName." )\n");
            }
        }

		$this->init($dbName, $tableName, $colName, $colType, $colSize, $colExtend);


		$this->char_code = 'utf-8';

		++self::$referenceCount;
	}

	function __destruct(){
		if( 0 == --self::$referenceCount )
		{
			self::$commonConnect->close();

			self::$commonConnect = null;
		}
	}

	function sql_query($sql,$update = false){
		$rtn =  self::$commonConnect->query( mb_convert_encoding( $sql, $this->char_code, mb_internal_encoding()) );
		if($rtn){ return array( 'result'=> $rtn, 'seek_point' => 0);}
		return false;
	}

	function sql_exec($sql){
        //self::$commonConnect->exec('BEGIN IMMEDIATE');
		self::$commonConnect->exec($sql);
        //self::$commonConnect->exec('COMMIT');
	}

	function sql_fetch_assoc( &$result ,$index){
		if( $index == $result["seek_point"] ){
			$result["seek_point"]++;
			return $result["result"]->fetchArray( SQLITE3_ASSOC );
		}else if( $index < $result["seek_point"] ){
			$result["seek_point"] =0;
			$result["result"]->reset();
		}
		
		while($rec = $result["result"]->fetchArray( SQLITE3_ASSOC ))
		{
			$result["seek_point"]++;
			if( $index == $result["seek_point"]-1 ){
				return $rec;
			}
		}
		$result["seek_point"] =0;
		$result["result"]->reset();
		return null;
	}

	function sql_fetch_array( &$result ){
		$result["seek_point"]++;
		return $result["result"]->fetchArray( );
	}

	function sql_fetch_all( &$result ){
		$res = Array();
		$result["result"]->reset();
		
		while($recs[] = $result["result"]->fetchArray());
		
		$result["seek_point"] =0;
		$result["result"]->reset();
		return $recs;
	}

	function sql_num_rows( &$result ){
        $result["result"]->reset();
		$result["seek_point"] = 0;
        while($result["result"]->fetchArray()){$result["seek_point"]++;};

        $row = $result["seek_point"];

        $result["seek_point"] =0;
        $result["result"]->reset();
        return $row;
	}

	function sql_convert( $val ){
		if( 'UTF-8' == SystemUtil::detect_encoding_ja($val) )
			return mb_convert_encoding( $val, mb_internal_encoding(), 'UTF-8' );

		return $val;
	}

	function sql_escape($val){
		if(!strlen($val)){return $val;}
		return self::$commonConnect->escapeString($val);
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

		$set_str = "";
		if( isset($set) && is_array($set) && count($set) ){
			foreach( $set as $key => $s ){
				$set_str = " $key = " . $this->sqlDataEscape($s, $this->colType[$key] ).', ';
			}
		}

		if(is_array($column)){
			$key_sets = Array();
			foreach( $column as $col ){
				$key_sets[] = "$col = replace( $col, '$search', '$replace' )";
			}
			$set_str .= join( ',', $key_sets );
		}else{
			$set_str .= "$column = replace( $column, '$search', '$replace' )";
		}

		//replace実行
		$sql	 = 'UPDATE '. $table_name.' SET '.$set_str. $table->getWhere();
		if( $this->_DEBUG )				{ d( "replaceTable() : ". $sql. "<br/>\n", 'sql' ); }
		$result	 = $this->sql_query( $sql );
		if( !$result ){ throw new InternalErrorException("replaceTable() : SQL MESSAGE ERROR. \n"); }

		TemplateCache::SetDBUpdateTime();
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