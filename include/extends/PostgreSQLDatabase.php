<?php

include_once "./include/base/SQLDatabase.php";

/*******************************************************************************************************
 * <PRE>
 *
 * SQLデータベースシステム　PostgreSQL用
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
	function __construct($dbName, $tableName, $colName, $colType, $colSize, $colExtend ){

		global $SQL_SERVER;
		global $SQL_ID;
		global $SQL_PASS;
		global $SQL_PORT;
		
		if($SQL_PORT != "")
			$this->connect  = pg_connect( 'host=' . $SQL_SERVER . ' port=' . $SQL_PORT . ' dbname=' . $dbName . ' user=' . $SQL_ID . ' password=' . $SQL_PASS );
		else
			$this->connect  = pg_connect( 'host=' . $SQL_SERVER . ' dbname=' . $dbName . ' user=' . $SQL_ID . ' password=' . $SQL_PASS );

		if( !$this->connect ) { throw new InternalErrorException("DB CONNECT ERROR. -> dbname=". $dbName. " host=".$SQL_SERVER."\n"); }

		$this->init($dbName, $tableName, $colName, $colType, $colSize, $colExtend);
			
		//                  $this->sql_char_code = "UTF8";
		//                $this->sql_char_code = "SJIS";
		pg_set_client_encoding('SJIS');
		$this->sql_char_code = pg_client_encoding();  //pg_client_encoding($connect)
		//                pg_set_client_encoding($connect,'SJIS');

	}

	function sql_query($sql,$update = false){
		return pg_query( $this->connect, $sql );
	}

	function sql_fetch_assoc( &$result,$index){
		return pg_fetch_array( $result, $index, PGSQL_ASSOC );;
	}

	function sql_fetch_array(&$result){
		return pg_fetch_array( $result, 0, PGSQL_ASSOC );
	}
	
	function sql_fetch_all( &$result ){
		return pg_fetch_all( $result );
	}

	function sql_num_rows(&$result){
		return pg_num_rows( $result );
	}

	function sql_convert( $val ){
		return mb_convert_encoding( $val, mb_internal_encoding(), $this->sql_char_code );
	}

	function sql_escape($val){
		return mb_convert_encoding( pg_escape_string( $val ), $this->sql_char_code, mb_internal_encoding() );
	}
	
	function sql_date_group($column,$format_type,$format=null){
		
		if(is_null($format)){
			switch($format_type){
				case 'y':
					$format = 'YYYY';
					break;
				case 'm':
					$format = 'YYYY-MM';
					break;
				case 'd':
				default:
					$format = 'YYYY-MM-dd';
					break;
			}
		}
		return "to_char('1970-01-01 09:00'::timestamp + ( $column || 's')::interval, '$format')";
		//return "to_char('1970-01-01'::date + ( $column || 's')::interval, '$format')";
	}
	
	

	function to_boolean( $val ){
		if( is_bool( $val ) )		{ return $val; }
		else if( is_string($val )){
			if( $val == 'f' ){ return false; }
			if( $val == 't' ){ return true; }
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
			if( SystemUtil::convertBool($val) )	{ return $sqlstr = "TRUE"; }
			else								{ return $sqlstr = "FALSE"; }
		}else{
			return parent::sqlDataEscape($val,$type,$quots);
		}
	}

	private function getColumnType($name){
		$t = $this->getTable();
		$t->offset	 = 0;
		$t->limit	 = 1;
		$ret = $this->sql_query( "SELECT $name FROM ". strtolower($this->tableName)." ".$t->getLimitOffset() );

		return pg_field_type($ret,0);;
	}
	
	function getRecord($table, $index){
		$rec = parent::getRecord($table,$index);
		if( $rec != null && strpos( $table->select , $this->type.'.' ) !== FALSE ){
			foreach( $rec as $key => $val ){
				if( strpos( $key, $this->type.'.' ) !== FALSE ){
					$newrec[ substr($key,strlen($this->tableName)+1) ] = $val;
				}else{
					$newrec[ $key ] = $val;
				}
			}
			return $newrec;
		}
		return $rec;
	}
	
	//暗号化未対応
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
}

/*******************************************************************************************************/
/*******************************************************************************************************/
/*******************************************************************************************************/
/*******************************************************************************************************/

class Table extends TableBase{

	function __construct($from){
		global $SQL_MASTER;

		$this->select	 = '*';
		$this->from		 = $from;
		$this->delete	 = '( delete_key = FALSE OR delete_key IS NULL )';

		$this->sql_char_code = pg_client_encoding();
		parent::__construct($from);
	}


	function getLimitOffset(){
		if( ($this->offset == 0 || $this->offset != null) && $this->limit != null ){
			$str	 = " OFFSET ". $this->offset. " LIMIT ". $this->limit;
			return $str;
		}else{
			return "";
		}
	}
	function sql_convert( $val ){
		return mb_convert_encoding( $val, $this->sql_char_code, mb_internal_encoding() );
	}
}
?>