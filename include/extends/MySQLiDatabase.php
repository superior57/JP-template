<?php

include_once "./include/base/SQLDatabase.php";

/*******************************************************************************************************
 * <PRE>
 *
 * SQLデータベースシステム　MySQL用
 *
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
	function __construct($dbName, $tableName, $colName, $colType, $colSize ){

		global $DB_LOG_FILE;
		global $ADD_LOG;
		global $UPDATE_LOG;
		global $DELETE_LOG;
		global $SQL_SERVER;
		global $SQL_ID;
		global $SQL_PASS;
		global $TABLE_PREFIX;
		global $SQL_PORT;
			
		// フィールド変数の初期化
		$this->log		 = new OutputLog($DB_LOG_FILE);

		if($SQL_PORT != "")
			$this->connect	 = new mysqli( $SQL_SERVER.":".$SQL_PORT, $SQL_ID, $SQL_PASS );
		else
			$this->connect	 = new mysqli( $SQL_SERVER, $SQL_ID, $SQL_PASS );

		if( !$this->connect ){
			throw new InternalErrorException("SQLDatabase() : DB CONNECT ERROR. -> mysqli_connect( ".$SQL_SERVER." )\n");
		}
		if(  !$this->connect->select_db( $dbName )  ){
			throw new InternalErrorException("SQLDatabase() : DB CONNECT ERROR. -> mysqli_select_db( ". $dbName. " )\n");
		}
			
		$this->dbName		 = $dbName ;
		$this->tableName	 = strtolower( $TABLE_PREFIX.$tableName );
		$colName[]			 = strtolower( 'SHADOW_ID' );
		$this->colName		 = $colName;
		$this->colType		 = $colType;
		$this->colSize		 = $colSize;
			
		$this->addLog		 = $ADD_LOG;
		$this->updateLog	 = $UPDATE_LOG;
		$this->delLog		 = $DELETE_LOG;
			
		$this->dbInfo		 = $dbName. ",". $tableName;

		$this->prefix		 = $TABLE_PREFIX;

		//mySQLからの出力コードをSJISに
		//	mysqli_query($this->connect,"set names sjis");
		$this->connect->set_charset('sjis');
		//	mysqli_query($this->connect,"SET NAMES binary;");
		//	mysqli_set_charset('binary');
	}

	function sql_query($sql,$update = false){
		return $this->connect->query( $sql );
	}

	function sql_fetch_assoc( &$result ,$index){
		$result->data_seek( $index);
        return $result->fetch_assoc();
	}

	function sql_fetch_array( &$result ){
		return $result->fetch_array( );
	}

	function sql_fetch_all( &$result ){
		return $result->fetch_all( );
	}

	function sql_num_rows( &$result ){
		return $result->num_rows;
	}

	function sql_convert( $val ){
		return $val;
	}

	function sql_escape($val){
		return $this->connect->escape_string(($val));
	}
	
	function sql_date_group($column,$format){
		return "FROM_UNIXTIME($column,'$format')";
	}
	

	function to_boolean( $val ){
		if( is_bool( $val ) )		{ return $val; }
		else if( is_string($val )){
			if( $val == 'FALSE' ){ return false; }
			if( $val == 'TRUE' ){ return true; }
		}
		if( $val == 1 )		{ return true; }
		//else if( !strlen($val) )    { return false;}
		return false;
	}

	private function getColumnType($name){
		$t = $this->getTable();
		$t->offset	 = 0;
		$t->limit	 = 1;
		$ret = $this->sql_query( "SELECT $name FROM ". strtolower($this->tableName)." ".$t->getLimitOffset() );

		return mysqlDataType($ret->fetch_field_direct(0)->type);
	}

	/**
	 * テーブルのLike結合
	 * 
	 * @param $table テーブルデータ
	 * @param $name カラム名
	 * @param $asc 昇順・降順を 'asc' 'desc' で指定します。
	 */
	function joinTableLike( &$tbl, $b_name, $n_name, $b_col, $n_col, $n_tbl_name = null ){
		$_b_name = strtolower($this->prefix.$b_name);
		$_n_name = strtolower($this->prefix.$n_name);
		if( !is_null($n_tbl_name) )	{ $_n_name = $_n_name.' '.$n_tbl_name; $_n_tbl_name = $n_tbl_name; }
		else						{ $_n_tbl_name = $_n_name; }
		
		return $this->joinTableSQL( $tbl, $b_name, $n_name, $_n_tbl_name.".".$n_col." like concat( '%', ".$_b_name.".".$b_col.", '%') ", $n_tbl_name );
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
		$this->delete	 = '( delete_key = FALSE OR delete_key IS NULL )';

		$this->sql_char_code = "EUC-JP";//mysqli_character_set_name();
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