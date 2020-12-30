<?php

include_once "./include/base/Database.php";
include_once "./include/extends/SQLOutputLog.php";

/*******************************************************************************************************
 * <PRE>
 *
 * SQLデータベースシステム ベースクラス
 *
 * @author 吉岡幸一郎
 * @original 丹羽一智
 * @version 2.0.0
 *
 * </PRE>
 *******************************************************************************************************/

// define

define( 'TABLE_STATUS_NOMAL', 0 );
define( 'TABLE_STATUS_DELETED', 1 );
define( 'TABLE_STATUS_ALL', 2 );

class SQLDatabaseBase implements DatabaseBase
{

	var $connect;
	var $dbName;
	var $tableName;
	var $tablePlaneName;
	var $colName;
	var $colType;
	var $colSize;
	var $colExtend;
	var $_DEBUG		 = DEBUG_FLAG_SQLDATABASE;
	var $_DEBUG_REC	 = DEBUG_FLAG_RECORD_LOAD;

	var $row = -1;
	var $row_table = null;
	var $rec_cash = null;

	var $sql_char_code;
	var $prefix;

	var $log;

	private $pass_prefix = 'pass_';

	var $char_code;

	/**
	 * コンストラクタ。
	 * @param $dbName string DB名
	 * @param $tableName string テーブル名
	 * @param $colName array カラム名を持った配列
	 */
	function __construct($dbName, $tableName, $colName, $colType, $colSize, $colExtend ){
		$this->init($dbName, $tableName, $colName, $colType, $colSize, $colExtend);
	}

	function init(&$dbName, &$tableName, &$colName, &$colType, &$colSize, &$colExtend )
	{
		global $LOG_DIRECTORY_PATH;
		global $ADD_LOG;
		global $UPDATE_LOG;
		global $DELETE_LOG;
		global $TABLE_PREFIX;
		global $SYSTEM_CHARACODE;

		// フィールド変数の初期化
		$this->log		 = new SQLOutputLog($LOG_DIRECTORY_PATH);

		$this->dbName		 = $dbName ;
		$this->tableName	 = strtolower( $TABLE_PREFIX.$tableName );
		$this->tablePlaneName = $tableName;

		array_unshift( $colName, strtolower( 'DELETE_KEY' ));
		array_unshift( $colName, 'shadow_id');
		$colType[ 'shadow_id' ] = 'string';
		$colType[ strtolower( 'DELETE_KEY' ) ] = 'boolean';

		$this->colName		 = $colName;

		$this->colType		 = $colType;
		$this->colSize		 = $colSize;
		$this->colExtend	 = $colExtend;

		$this->addLog		 = $ADD_LOG;
		$this->updateLog	 = $UPDATE_LOG;
		$this->delLog		 = $DELETE_LOG;

		$this->dbInfo		 = $dbName. ",". $tableName;

		$this->prefix		 = strtolower( $TABLE_PREFIX );

		$this->char_code	= $SYSTEM_CHARACODE;
	}

	/**
	 * その名前のカラムが存在するかを返します。
	 * @param $name string 確認するカラム名
	 * @return bool 有無のboolean値
	 */
	function isColumn( $name )
	{
		return in_array( $name , $this->colName );
	}


	/**
	 * レコードを取得します。
	 * @param $table TableBase テーブルデータ
	 * @param $index int 取得するレコード番号
	 * @return array レコードデータ
	 */
	function getRecord($table, $index){
		$rec = null;
		if( $table->cashed && !is_null($table->cash) ){
			if( $this->_DEBUG )				{ d( "getRecord() : ".$this->tableName." load table cash<br/>\n", 'sql'); }
			$result = $table->cash;
		}else if( ! is_null( $this->rec_cash )){
			if( $this->_DEBUG )				{ d( "getRecord() : ".$this->tableName." load cash<br/>\n", 'sql'); }
			$result = $this->rec_cash;
		}else{
			$table = $this->addPasswordDecrypt( $table );

			if( $this->_DEBUG ){ d( "getRecord() : ". $table->getString(). "<br/>\n", 'sql' ); }
			$result	 = $this->sql_query( $table->getString() );
			$this->rec_cash = $result;

			if( $table->cashed ){
				$table->cash = $result;
			}
		}

		if( !$result ){
			throw new InternalErrorException("getRecord() : SQL MESSAGE ERROR. \n");
		}

		if($this->sql_num_rows($result) != 0){
			$rec = $this->sql_fetch_assoc( $result, $index);
			$rec = $this->replacePasswordDecrypt( $rec );
			$rec = $this->getModelRecord( $rec );
		}

		if( $this->_DEBUG_REC ){ d( Array( $this->tableName , $rec ) , 'getRecord' ); }

		return $rec;
	}


	/**
	 * レコードの最初の行を取得します。
	 * 存在しない場合はfalseを返します。
	 *
	 * @param $table TableBase テーブルデータ
	 * @return array|bool レコードデータ|false
	 */
	function getFirstRecord($table){
		$rec = null;
		if( $table->cashed && !is_null($table->cash) ){
			if( $this->_DEBUG )				{ d( "getFirstRecord() : ".$this->tableName." load table cash<br/>\n", 'sql'); }
			$result = $table->cash;
		}else if( ! is_null( $this->rec_cash )){
			if( $this->_DEBUG )				{ d( "getFirstRecord() : ".$this->tableName." load cash<br/>\n", 'sql'); }
			$result = $this->rec_cash;
		}else{
			$table = $this->addPasswordDecrypt( $table );
			$table = $this->limitOffset( $table, 0 , 1 );


			if( $this->_DEBUG ){ d( "getFirstRecord() : ". $table->getString(). "<br/>\n", 'sql' ); }
			$result	 = $this->sql_query( $table->getString() );
		}

		if( !$result ){
			throw new InternalErrorException("getFirstRecord() : SQL MESSAGE ERROR. \n");
		}

		if($this->sql_num_rows($result) != 0){
			$rec = $this->sql_fetch_assoc( $result,0);
			$rec = $this->replacePasswordDecrypt( $rec );
			return $rec;
		}

		return false;
	}


	/**
	 * レコードを取得します。
	 *
	 * @param $id string 取得するレコードID
	 * @param $type string 操作対象となるテーブルのtype(nomal/delete/all)
	 * @return array レコードデータ。レコードデータが存在しない場合nullを返す。
	 */
	function selectRecord( $id , $type = null)
	{
		if( is_null($id) ){ return null;}

		$table	 = $this->getTable($type);
		$table	 = $this->searchTable( $table, 'id', '=', $id );
		if( $this->existsRow($table) )
		{// レコードが存在する場合
			$rec	 = $this->getRecord( $table, 0 );
			return $rec;
		}
		else	{ return null; }
	}

	/**
	 * テーブルから指定したレコードを削除します。
	 * @param $table TableBase テーブルデータ
	 * @param $rec string 削除対象となるレコード
	 * @return TableBase テーブルデータ
	 */
	function pullRecord($table, $rec){
		return $this->searchTable( $table, 'shadow_id', '!', $this->getData( $rec, 'shadow_id' ) );
	}

	/**
	 * データの内容を取得する。
	 * @param $rec array レコードデータ
	 * @param $name string カラム名
	 * @return string|bool|int 値
	 */
	function getData($rec, $name, $br = false){
		$name	 = strtolower( $name );

		if(  isset( $rec[ $name ] )  ){
			$type = isset( $this->colType[ $name ] )? $this->colType[ $name ] : 'function';
			switch( $type ){
				case 'boolean':
					return $this->to_boolean( $rec[ $name ] );
				case 'password':
				default:
					if(  is_string( $rec[ $name ] )  ){
						if( !$br ){
							return str_replace(  "\r\n", "\n", $this->sql_convert( $rec[ $name ] )  );
						}else{
							return brChange( $this->sql_convert( $rec[ $name ] ) );
						}
						break;
					}
			}
			return $rec[ $name ];
		}else{
			return null;
		}
	}

	function getDataList($table, $name, $key = 'shadow_id' ){
		if( ! is_null( $this->rec_cash )){
			if( $this->_DEBUG )				{ d( "getDataList() : ".$this->tableName." load cash<br/>\n", 'sql' ); }
			$result = $this->rec_cash;
		}else{

			if( $this->_DEBUG ){ d( "getDataList() : ". $table->getString(). "<br/>\n", 'sql' ); }

			$table = $this->addPasswordDecrypt( $table );

			$result	 = $this->sql_query( $table->getString() );
			$this->rec_cash = $result;
		}

		if( !$result ){
			throw new InternalErrorException("getDataList() : SQL MESSAGE ERROR. \n");
		}

		if( $this->colType[ $name ] == 'password' ){
			$name = $this->pass_prefix .$name;
		}

		$list = null;
		$row = $this->getRow($table);
		if($row != 0){

			for( $i=0;$row>$i;$i++){
				$rec = $this->getRecord( $table, $i );

				if( is_array( $name ) ){
					$l = Array();
					foreach( $name as $n ){
						$l[$n] = $this->getData( $rec, $n );
					}
				}else{
					$l = $this->getData( $rec, $name );
				}
				$key_data = $this->getData( $rec, $key );
				if( !is_null( $key_data ) ){
					$list[ $key_data ] = $l;
				}else{
					$list[] = $l;
				}

			}
			/*$recs = $this->sql_fetch_all( $result );

			$list = Array();
			foreach( $recs as $row ){
				if( is_array( $name ) ){
					$l = Array();
					foreach( $name as $n ){
						$l[$n] = $this->sql_convert( $row[$n] );
					}
					if(isset( $row[ $key ])){
						$list[ $this->sql_convert( $row[ $key ] ) ] = $l;
					}else{
						$list[] = $l;
					}
				}else{
					if(isset($row[ $key ])){
						$list[ $this->sql_convert( $row[ $key ] ) ] = $this->sql_convert( $row[$name] );
					}else{
						$list[] = $this->sql_convert( $row[$name] );
					}
				}
			}*/
		}

		return $list;
	}

	/**
	 * レコードの内容を更新する。
	 * DBファイルへの更新も含みます。
	 * @param $rec array レコードデータ
	 */
	function updateRecord($rec){
		//deleteテーブルのデータは更新できない
		if( !$rec || $this->getData( $rec, 'delete_time' ) ){return;}
		global $LOGIN_ID;
		global $loginUserType;

		if( $this->getData( $rec, 'delete_key' ) ){
			$this->deleteRecord( $rec );
			return;
		}

		$sql	 = "UPDATE " . $this->tableName . " SET ";
		$sql	 .= $this->toString( $rec, "UPDATE" );
		$sql	 .= " WHERE shadow_id = ". $this->getData( $rec, 'shadow_id' );
		if( $this->_DEBUG ){ d( "updateRecord() : ". $sql. "<br/>\n", 'sql' ); }

		$result = $this->sql_query( $sql );

		if($this->updateLog == true){
			$delete_data = isset($rec['DELETE_KEY'])?$rec['DELETE_KEY']:'';
			$str = $rec['shadow_id']. ",".$delete_data. ",";
			$cnt = count($this->colName);
			for($i=0; $i<$cnt; $i++){
				$str .= $rec[ $this->colName[$i] ]. ",";
			}
			$this->log->table_log($this->tableName,'UPDATE',$str);
		}
		$this->cashReset();
		TemplateCache::SetDBUpdateTime();
	}

	/**
	 * 引数として渡されたtableの全行にデータをセットしてupdateする。
	 *
	 * @param $table TableBase 更新を行なうカラムの入ったtable
	 * @param $name string カラム名
	 * @param $val string|int|bool 値
	 * @param $escape bool エスケープを実行するか指定。valにカラム等指定したい場合はfalse
	 * @return TableBase
	 */
	function setTableDataUpdate(&$table, $name, $val, $escape = true )
	{
		if( $table->status != TABLE_STATUS_NOMAL ){return;}

		if(!$this->existsRow($table)){
			return $table;
		}

		$sql	 = "UPDATE ". $this->tableName. " SET ";
		if($escape) { $val = $this->sqlDataEscape($val,$this->colType[$name]); }
		$sql	 .= $name ."=" .$val;
		$sql	 .= $table->getWhere();
		if( $this->_DEBUG ){ d( "setTableDataUpdate() : ". $sql. "<br/>\n", 'sql'); }

		$this->sql_query( $sql );

		if($this->updateLog == true){
			$str = $table->getWhere(). ",".$name."=".$val. ",".$row;
			$this->log->table_log($this->tableName,'TABLE_UPDATE',$str);
		}
		$this->cashReset();

		TemplateCache::SetDBUpdateTime();
		return $table;
	}

	/**
	 * レコードの削除。
	 * DBファイルへの反映も行います。
	 * @param $rec array レコードデータ
	 * @return array
	 */
	function deleteRecord(&$rec){
		global $CONFIG_SQL_FILE_TYPES;
		if( !$rec || $this->getData( $rec, 'delete_time' ) ){return;}
		/*
		foreach( $this->colType as $key => $type ){
			if( in_array( $type, $CONFIG_SQL_FILE_TYPES )  ){
				$filename = $this->getData( $rec, $key );
				$ret_name = $this->deleteFile( $filename );
				if(!is_null($ret_name)){	$this->setData( $rec, $key, $ret_name );	}
			}
		}
		$this->updateRecord( $rec );
		*/
		$rec	 = $this->setData( $rec, 'delete_key', true );

		$sql	 = "INSERT INTO ". $this->tableName. "_delete (\n";
		$sql .= implode(", \n",$this->colName);

		//delete table用
		$sql .= ", \n delete_type, \n \ndelete_id , \n \ndelete_time";

		$sql	 .= ")VALUES ( ";
		$sql	 .= $this->toString( $rec, "INSERT" );

		//delete table用
		$sql	 .= ',\''.$loginUserType.'\'';
		$sql	 .= ',\''.$LOGIN_ID.'\'';
		$sql	 .= ','.time();

		$sql	 .= " )";

		if( $this->_DEBUG ){ d( "deleteRecord() : ". $sql. "<br/>\n", 'sql' ); }

		$result = $this->sql_query( $sql );
		if( $result ){
			$sql	 = "DELETE FROM ". $this->tableName. " WHERE shadow_id = ".$this->getData( $rec, 'shadow_id' );
			if( $this->_DEBUG ){ d( "deleteRecord() ". $sql. "<br/>\n", 'sql' ); }
			$result = $this->sql_query( $sql );
		}

		if($this->delLog == true){
			$str = "";
			$row = count($this->colName);
			for($i=0; $i<$row; $i++){
				$str .= $rec[ $this->colName[$i] ]. ",";
			}

			$this->log->table_log($this->tableName,'DELETE',$str);
		}

		$this->cashReset();
		TemplateCache::SetDBUpdateTime();
		return $rec;
	}

	function deleteFile($filename){
		//unlink( $filename );
		global $FileBase;

		if( strlen( $filename ) && $FileBase->file_exists( $filename ) ){
			//ファイルパスの最初に現れる/file/の後ろにdelete/を付与する
			//$delname = substr_replace( $filename, 'delete/', strrpos( $filename, 'file/' )+5, 0 );
			$delname = str_replace( 'file/', 'file/delete/',$FileBase->getfilepath($filename) );

			//必要なディレクトリの生成
			SystemUtil::mkdir( $delname );

			//ファイルの移動
			$FileBase->rename( $filename, $delname );
			if( $FileBase->file_exists($filename) && $FileBase->copy($filename, $delname ) ){
				$FileBase->delete($filename);
				@chmod($delname, 0766);
			}
			return $delname;
		}
		return null;
	}

	/**
	 * whereによって選択されるテーブルの行を削除します。
	 * @param $table TableBase テーブルデータ
	 * @return int 行数
	 */
	function deleteTable($table){
		global $CONFIG_SQL_FILE_TYPES;
		global $LOGIN_ID;
		global $loginUserType;

		if( $table->status != TABLE_STATUS_NOMAL ){return;}

		/*
		//image,file型のカラムリストを作る
		$keys = Array();
		foreach( $this->colType as $key => $type ){
			if( in_array( $type, $CONFIG_SQL_FILE_TYPES )  ){
				$keys[] = $key;
			}
		}
		*/
		$this->setTableDataUpdate( $table, 'delete_key', true );

		//fileの削除(移動)
		/*
		if( count($keys) ){
			$file_datas = $this->getDataList( $table, $keys );
			foreach( $file_datas as $shadow_id => $files ){
				foreach( $files as $filename ){
					$this->deleteFile( $filename );
				}
			}
			//replaceを使う
			$this->replaceTable( $table, $keys,  'file/','file/delete/' );
		}
		*/

		$delete_key = "delete_key = ".$this->sqlDataEscape(true,'boolean').' ';

		$sql	 = 'INSERT INTO '. $this->tableName.'_delete'.' SELECT *' ;
		$sql	 .= ',\''.$loginUserType.'\',\''.$LOGIN_ID.'\','.time();
		$sql	 .= ' FROM '.$this->tableName;
		$sql	 .= ' WHERE '.$delete_key;

		if( $this->_DEBUG )				{ d( "deleteTable() : ". $sql. "<br/>\n", 'sql' ); }
		$result	 = $this->sql_query( $sql );
		if( !$result ){ throw new InternalErrorException("deleteTable() : SQL MESSAGE ERROR. \n"); }

		$sql	  = 'DELETE FROM '. $this->tableName;
		$sql	 .= ' WHERE '.$delete_key;
		if( $this->_DEBUG )				{ d( "deleteTable() : ". $sql. "<br/>\n", 'sql' ); }
		$result	 = $this->sql_query( $sql );
		if( !$result ){ throw new InternalErrorException("deleteTable() : SQL MESSAGE ERROR. \n"); }

		$this->cashReset();
		TemplateCache::SetDBUpdateTime();
		return;
	}

	/**
	 * レコードの復元。
	 * DBファイルへの反映も行います。
	 * @param $rec array レコードデータ
	 */
	function restoreRecord(&$rec){
		global $CONFIG_SQL_FILE_TYPES;
		if(!$rec){ return; }

		foreach( $this->colType as $key => $type ){
			if( in_array( $type, $CONFIG_SQL_FILE_TYPES )  ){
				$filename = $this->getData( $rec, $key );
				$ret_name = $this->restoreFile( $filename );
				if(!is_null($ret_name)){	$this->setData( $rec, $key, $ret_name );	}
			}
		}
		$this->updateRecord( $rec );

		$rec	 = $this->setData( $rec, 'delete_key', false );

		$sql	 = "INSERT INTO ". $this->tableName. " (\n";
		// カラム名リストを出力
		$sql .= implode(", \n",$this->colName);
		$sql	 .= ")VALUES ( ";
		$sql	 .= $this->toString( $rec, "INSERT" );
		$sql	 .= " )";
		if( $this->_DEBUG ){ d( "restoreRecord() : ". $sql. "<br/>\n", 'sql' ); }

		$result = $this->sql_query( $sql );

		if( $result ){
			$sql	 = "DELETE FROM ". $this->tableName. "_delete WHERE shadow_id = ".$this->getData( $rec, 'shadow_id' );
			if( $this->_DEBUG ){ d( "restoreRecord() ". $sql. "<br/>\n", 'sql' ); }
			$result = $this->sql_query( $sql );
		}

		if($this->delLog == true){
			$str = "";
			$row = count($this->colName);
			for($i=0; $i<$row; $i++){
				$str .= $rec[ $this->colName[$i] ]. ",";
			}

			$this->log->table_log($this->tableName,'RESTORE',$str);
		}
		$this->cashReset();
		TemplateCache::SetDBUpdateTime();
		return $rec;
	}

	function restoreFile( $delname ){
		if( strlen( $delname ) && file_exists( $delname ) ){
			//ファイルパスの最初に現れる/file/の後ろにdelete/を付与する
			$filename = str_replace( 'file/delete/','file/', $delname );

			//必要なディレクトリの生成
			SystemUtil::mkdir( $filename );

			//ファイルの移動
			rename( $delname,$filename );
			return $filename;
		}
		return "";
	}


	/**
	 * whereによって選択されるテーブルを復元します。
	 * @param $table TableBase テーブルデータ
	 * @return int 行数
	 */
	function restoreTable($table){
		global $CONFIG_SQL_FILE_TYPES;

		if( $table->status != TABLE_STATUS_DELETED ){return;}

		//image,file型のカラムリストを作る
		$keys = Array();
		foreach( $this->colType as $key => $type ){
			if( in_array( $type, $CONFIG_SQL_FILE_TYPES )  ){
				$keys[] = $key;
			}
		}
		//fileの復元(移動)
		if( count($keys) ){
			$file_datas = $this->getDataList( $table, $keys );
			foreach( $file_datas as $files ){
				foreach( $files as $filename ){	$this->restoreFile( $filename );	}
			}
		}

		//replaceを使う
		$this->replaceTable( $table, $keys,  'file/delete/','file/',
			Array( 'delete_key' => false ) );

		$delete_key = "delete_key = ".$this->sqlDataEscape(false,'boolean').' ';
		$delete_table = $this->tableName.'_delete';

		$sql	 = 'INSERT INTO '. $this->tableName.' SELECT ' ;
		$sql	.= implode(',',$this->colName);

		$sql	 .= ' FROM '.$delete_table;

		$sql	 .= ' WHERE '.$delete_key;
		if( $this->_DEBUG )				{ d( "restoreTable() : ". $sql. "<br/>\n", 'sql' ); }
		$result	 = $this->sql_query( $sql );
		if( !$result ){ throw new InternalErrorException("restoreTable() : SQL MESSAGE ERROR. \n"); }

		$sql	  = 'DELETE FROM '. $delete_table;
		$sql	 .= ' WHERE '.$delete_key;
		if( $this->_DEBUG )				{ d( "restoreTable() : ". $sql. "<br/>\n", 'sql' ); }
		$result	 = $this->sql_query( $sql );
		if( !$result ){ throw new InternalErrorException("restoreTable() : SQL MESSAGE ERROR. \n"); }

		$this->cashReset();
		TemplateCache::SetDBUpdateTime();
		return;
	}

	/**
	 * テーブルの指定カラムを置換する。
	 * @param $table TableBase replaceを実行するテーブルクラスのインスタンス
	 * @param $column string replaceを適用するカラム、配列を渡した場合は各行に対してreplaceが行なわれる
	 * @param $search string 検索する文言
	 * @param $replace string 置換する文言
	 * @param $set array *任意 replaceのupdateを走らせる際に同時に変更を適用したい場合に使用する
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
		return;
	}

	/**
	 * データをセットする。
	 * @param $rec array レコードデータ
	 * @param $name string カラム名
	 * @param $val string|int|bool 値
	 * @return array
	 */
	function setData(&$rec, $name, $val){
		$name			 = strtolower( $name );

		if( !empty($this->colExtend[$name]) ){
			$val = GUIManager::replaceString( $val, $this->colExtend[$name], $this->colType[$name] );
		}

		if(  is_bool( $val )  ){
			if( $val ){
				$val	 = 'TRUE';
			}else{
				$val	 = 'FALSE';
			}
		}

		$rec[ $name ]	 = $this->sql_convert($val);

		return $rec;
	}

	/**
	 * 簡易な演算を行ない、結果をセットする。
	 * カラムが数値型で無い場合は無効
	 *
	 * @param $rec array レコードデータ
	 * @param $name string カラム名
	 * @param $opp string 演算子
	 * @param $name string|int|bool 値
	 * @return Array $rec
	 */
	function setCalc(&$rec, $name , $opp , $val )
	{
		if( array_search( $opp, Array('+','-','/','*','%')) === FALSE && is_numeric($val) ){
			return null;
		}
		switch($this->colType[$name]){
			case 'string':
			case 'varchar' :
			case 'password':
			case 'char' :
			case 'image':
			case 'boolean':
				return null;
				break;
			default:
				$old = $this->getData( $rec , $name );
				eval('$new = ' .$old.$opp.$val.";");
				$this->setData( $rec , $name , $new );
				break;
		}
		return $rec;
	}

	/**
	 * レコードにデータをまとめてセットします。
	 * @param $rec array レコードデータ
	 * @param $data array データ連想配列（添え字はカラム名）
	 * @return array $rec(レコードデータ)
	 */
	function setRecord(&$rec, $data){
		$tmp	 = $this->getData( $rec, 'shadow_id' );
		$rec	 = $this->getNewRecord( $data );
		$this->setData( $rec, 'shadow_id', $tmp );
		return $rec;
	}

	/**
	 * 新しくレコードを取得します。
	 * デフォルト値を指定したい場合は
	 * $data['カラム名']の連想配列で初期値を指定してください。
	 * @param $data array 初期値定義連想配列
	 * @return array $rec(レコードデータ)
	 */
	function getNewRecord($data = null){
		global $CONFIG_SQL_FILE_TYPES;

		$rec = $this->getModelRecord( array() );

		// レコードの中身を null で初期化
		for($i=0; $i<count( $this->colName ); $i++){
			$rec[ $this->colName[$i] ] = null;
		}

		// 初期値が指定されていなければ return
		if(  !isset( $data )  ){ return $rec; }

		// 初期値を代入
		for($i=0; $i<count( $this->colName ); $i++){
			$name = $this->colName[$i];
			if( in_array( $this->colType[ $name ] , $CONFIG_SQL_FILE_TYPES ) ){
				// データファイルの場合
				$this->setFile( $rec, $name );
			}else{
				switch( $this->colType[ $name ] ){
					case  'timestamp':
						//timestampの場合{カラム名}_year等があれば生成する。
						if( isset( $data[ $name.'_year' ] ) ){
							$hasY = is_numeric( $data[ $name.'_year' ] );
							$hasM = isset( $data[ $name.'_month' ] ) && is_numeric( $data[ $name.'_month' ] );
							$hasD = isset( $data[ $name.'_day' ] ) && is_numeric( $data[ $name.'_day' ] );

							$y = $hasY ? $data[ $name.'_year' ]  : null;
							$m = $hasM ? $data[ $name.'_month' ] : 1;
							$d = $hasD ? $data[ $name.'_day' ]   : 1;

							if( !$hasY && !$hasM && !$hasD )
								{ $this->setData( $rec, $name, 0 ); }
							else
								{ $this->setData( $rec, $name, mktime($h,$min,$s,$m,$d,$y) ); }
							continue 2;
						}
						break;
					case 'date':
						if( isset( $data[ $name.'_year' ] ) ){
							$y = is_numeric( $data[ $name.'_year' ] )? $data[ $name.'_year' ]:date('Y');
							$m = ( isset( $data[ $name.'_month' ] ) && is_numeric( $data[ $name.'_month' ] ) ) ? $data[ $name.'_month' ] : 1;
							$d = ( isset( $data[ $name.'_day' ] ) && is_numeric( $data[ $name.'_day' ] ) ) ? $data[ $name.'_day' ] : 1;
							$this->setData( $rec, $name, sprintf( "%4d-%02d-%02d",$y,$m,$d ));
							continue 2;
						}
						break;
				}

				if( isset( $data[ $name ] ) && $data[ $name ] != null ){
					if( is_array( $data[ $name ] ) ){
						$str = '';
						for($j=0; $j<count(  $data[ $name ]  ); $j++){
							$str .= $data[ $name ][$j];
							if( $j != count( $data[  $name ] ) - 1 ){
								$str .= '/';
							}
						}
						$this->setData( $rec, $name, $str );
					}else{
						if( is_bool( $data[ $name ] ) ){
							$this->setData( $rec, $name, $data[ $name ] );
						}else if( strtolower( $data[ $name ] ) == 'true' ){
							$this->setData( $rec, $name, true );
						}else if( strtolower( $data[ $name ] ) == 'false' ){
							$this->setData( $rec, $name, false );
						}else{
							$data[ $name ] = GUIManager::replaceString( $data[ $name ], $this->colExtend[$name], $this->colType[$name] );
							$this->setData( $rec, $name, $data[ $name ] );
						}
					}
				}
			}
		}
		// 暗黙の主キーを定義
		$this->setData(   $rec, 'shadow_id', $this->getMaxID()+1);
		return $rec;
	}

	function setFile(&$rec, $colname){
		$sys	 = SystemUtil::getSystem( $_GET["type"] );
		$sys->doFileUpload( $this, $rec, $colname, $_FILES );
	}

	/**
	 * レコードの追加。
	 * DBへの反映も同時に行います。
	 * @param $rec array レコードデータ
	 */
	function addRecord(&$rec){
		global $ID_LENGTH;
		global $USE_REGISTER_PROCCESS_LOCK;

		// SQL文の生成を開始
		$sql	 = "INSERT INTO " . $this->tableName . " (";

		// カラム名リストを出力
		$sql	.= implode(", \n",$this->colName);

		// 重複を避ける為に暗黙の主キーを再設定

		if( $USE_REGISTER_PROCCESS_LOCK )
		{
			if( !SystemUtil::lockProccess( 'addRecord_' . $this->tableName , 10 , 100000 ) )
				{ throw new Exception(); }
		}

		$this->setData(   $rec, 'shadow_id', $this->getMaxID()+1);
		if( $ID_LENGTH[ $this->tablePlaneName ] > 0 ){
			$this->setData(  $rec, 'id', SystemUtil::getNewId( $this, $this->tablePlaneName ) );
		}

		$sql	 .= ")VALUES ( ";
		$sql	 .= $this->toString( $rec, "INSERT" );
		$sql	 .= " )";
		if( $this->_DEBUG ){ d( "addRecord() : ". $sql. "<br/>\n", 'sql' ); }

		$return = $this->sql_query( $sql );

		if($return){
			$sql = "UPDATE ".$this->prefix."system_tables SET id_count = '".$this->getData( $rec, 'shadow_id')."' WHERE table_name = '". $this->tablePlaneName ."'";
			$this->sql_query( $sql );
		}

		if( $USE_REGISTER_PROCCESS_LOCK )
			{ SystemUtil::unlockProccess( 'addRecord_' . $this->tableName ); }

		if($this->addLog == true){
			$str = "";
			$row = count($this->colName);
			for($i=0; $i<$row; $i++){
				$str .= $rec[ $this->colName[$i] ]. ",";
			}

			$this->log->table_log($this->tableName,'ADD',$str);
		}

		TemplateCache::SetDBUpdateTime();
		$this->cashReset();
	}

	/**
	 * レコードの複数追加。
	 * DBへの反映も同時に行います。
	 * @param $recList array レコードデータの配列
	 */
	function addRecordList($recList, $id_update = TRUE){
		global $ID_LENGTH;
		global $USE_REGISTER_PROCCESS_LOCK;
		global $THIS_TABLE_IS_NOACCURACY;

		// SQL文の生成を開始
		$sql	 = "INSERT INTO " . $this->tableName . " (";

		// カラム名リストを出力

		$columns = Array();

		foreach( $this->colName as $column )
		{
			if( 'fake' == $this->colType[ $column ] )
				{ continue; }

			$columns[] = $column;
		}

		$sql .= implode( ', ' . "\n" , $columns );

		// 重複を避ける為に暗黙の主キーを再設定

		if( $USE_REGISTER_PROCCESS_LOCK && ( !isset( $THIS_TABLE_IS_NOACCURACY[ $this->tablePlaneName ] ) || !$THIS_TABLE_IS_NOACCURACY[ $this->tablePlaneName ] ) )
		{
			if( !SystemUtil::lockProccess( 'addRecord_' . $this->tableName , 10 , 100000 ) )
				{ throw new Exception(); }
		}

		$sql	 .= ")VALUES ";
		$values   = Array();
		$shadowID = $this->getMaxID();

		foreach( $recList as &$rec )
		{
			++$shadowID;

			if( $id_update )
			{
				$this->setData(   $rec, 'shadow_id', $shadowID);
				if( $ID_LENGTH[ $this->tablePlaneName ] > 0 ){
					$this->setData(  $rec, 'id', SystemUtil::getNewId( $this, $this->tablePlaneName , $shadowID ) );
				}
			}

			$values[]	 = '(' . $this->toString( $rec, "INSERT" ) . ')';
		}
		unset($rec);

		$sql .= implode( ',' , $values );
		if( $this->_DEBUG ){ d( "addRecord() : ". $sql. "<br/>\n", 'sql' ); }

		$return = $this->sql_query( $sql );

		if($return){
			$sql = "UPDATE ".$this->prefix."system_tables SET id_count = '".$shadowID."' WHERE table_name = '". $this->tablePlaneName ."'";
			$this->sql_query( $sql );
		}

		if( $USE_REGISTER_PROCCESS_LOCK )
			{ SystemUtil::unlockProccess( 'addRecord_' . $this->tableName ); }

		if($this->addLog == true){
			foreach( $recList as $rec )
			{
				$str = "";
				$row = count($this->colName);
				for($i=0; $i<$row; $i++){
					$str .= $rec[ $this->colName[$i] ]. ",";
				}

				$this->log->table_log($this->tableName,'ADD',$str);
			}
		}

		TemplateCache::SetDBUpdateTime();
		$this->cashReset();
		return $recList;
	}

	function addRecordEx(&$rec,$flg="default"){
		global $ID_LENGTH;

		// SQL文の生成を開始
		$sql	 = "INSERT INTO " . $this->tableName . " (";

		// カラム名リストを出力
		$sql	.= implode(", \n",$this->colName);

		if( $ID_LENGTH[ $this->tablePlaneName ] > 0){
			switch($flg){
				case "shadow":
					$this->setData(  $rec, 'shadow_id', $this->getMaxID()+1);
					break;
				case "id":
					$this->setData(  $rec, 'id', SystemUtil::getNewId( $this, $this->tablePlaneName ) );
					break;
				case "none":
					break;
				default:
					$this->setData(   $rec, 'shadow_id', $this->getMaxID()+1);
					$this->setData(  $rec, 'id', SystemUtil::getNewId( $this, $this->tablePlaneName ) );
					break;
			}
		}

		$sql	 .= ")VALUES ( ";
		$sql	 .= $this->toString( $rec, "INSERT" );
		$sql	 .= " )";
		if( $this->_DEBUG ){
			d( "addRecord() : ". $sql. "<br/>\n", 'sql' );
		}

		$return = $this->sql_query( $sql );

		if($this->getData( $rec, 'shadow_id') > $this->getMaxID() ){
			if($return){
				$sql = "UPDATE ".$this->prefix."system_tables SET id_count = '".$this->getData( $rec, 'shadow_id')."' WHERE table_name = '". $this->tablePlaneName ."'";
				$this->sql_query( $sql );
			}
		}

		if($this->addLog == true){
			$str = "";
			$row = count($this->colName);
			for($i=0; $i<$row; $i++){
				$str .= $rec[ $this->colName[$i] ]. ",";
			}

			$this->log->table_log($this->tableName,'ADD',$str);
		}
		TemplateCache::SetDBUpdateTime();
		$this->cashReset();
	}

	/**
	 * DBが持つテーブルを取得します。
	 * @param $type string table type(nomal/delete/all)
	 * @return TableBase テーブルデータ
	 */
	function getTable($type = null){
		$table_name = strtolower($this->tableName);
		switch($type){
			default:
			case 'n':
			case 'nomal':
				$table	 = new Table($table_name );
				break;
			case 'd':
			case 'delete':
				$table	 = new Table($table_name.'_delete' );
				$table->status = TABLE_STATUS_DELETED;
				break;
			case 'a':
			case 'all':
				$delTable	 = new Table($this->tableName.'_delete' );
				$table	 = new Table($table_name );

				$table = $this->addSelectColumn( $table, 'null as delete_type, null as delete_id , null as delete_time' );
				$table->setUnionAll( $delTable );

				$table->status = TABLE_STATUS_ALL;
				break;
		}
		return $table;
	}

	/**
	 * テーブルの行数を取得します。
	 * @param $table TableBase テーブルデータ
	 * @return int 行数
	 */
	function getRow($table){

		if($this->row >= 0 && $table == $this->row_table ){
			if( $this->_DEBUG ){ d( "getRow() : load cash<br/>\n", 'sql'); }
			return $this->row;
		}
		$sql = $table->getRowString();

		if( $this->_DEBUG ){ d( "getRow() : ". $sql. "<br/>\n", 'sql'); }

		$result	 = $this->sql_query( $sql );

		if( !$result ){
			if( !$this->_DEBUG ){ d( "getRow() : ". $sql. "<br/>\n", 'sql'); }
			throw new InternalErrorException("getRow() : SQL MESSAGE ERROR. \n");
		}

		$rows = $this->sql_fetch_assoc($result,0);
		$row = $rows['cnt'];
		if( $row == -1 ){
			throw new InternalErrorException("getRow() : GET ROW ERROR ( RETURN -1 ).\n");
		}
		$this->row = $row;
		$this->row_table = $table;
		return $row;
	}

	/**
	 * テーブルに行が存在するかを返します。
	 * @param TableBase $tbl テーブルデータ
	 * @return bool 存在する場合はtrue、0件の場合はfalse
	 */
	function existsRow($tbl){
		if($this->row >= 0 && $tbl == $this->row_table ){
			if( $this->_DEBUG ){ d( "existsRow() : load row cash<br/>\n", 'sql'); }
			return $this->row > 0;
		}

		if( ! is_null( $this->rec_cash )){
			if( $this->_DEBUG )				{ d( "existsRow() : ".$this->tableName." load recorde cash<br/>\n", 'sql'); }
			$result = $this->rec_cash;
		}else{

			$table = $tbl->getClone();

			// union を利用している場合(type=ALL等)
            $union_count = count($table->union);
            if( $union_count > 0 )
            {
                //EXISTS の結果に SELECT の中身は意味がないが、* でない場合に最適化されないので定数に統一する。
                $table->select = '1';
                for( $i=0; $i<$union_count; $i++ )
                {
                    $table->union[$i]->select = '1';
                }
            }

			$sql = 'SELECT EXISTS('.$table->getString(true).') as exists_row_cnt';

			if( $this->_DEBUG ){ d( "existsRow() : $sql<br/>\n", 'sql' ); }

			$result	 = $this->sql_query( $sql );
		}

		if( !$result ){
			throw new InternalErrorException("existsRow() : SQL MESSAGE ERROR. \n");
		}
		$rec =$this->sql_fetch_assoc($result,0);
		return $rec['exists_row_cnt'];
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
			$column = implode(",' ',", $column);
		}else{
			if($table->join){ $column = $this->tableName.".".$column; }
		}

		$word = $this->sql_escape($word);

		$query = "concat($column) like '%{$word}%'";

		$table->addWhere($query);
		$this->cashReset();

		return $table;
	}


	/**
	 * 指定したテーブルのIDをランダムで取得する
	 * @param $tbl TableBase テーブルデータ
	 * @param $limit int 取得件数
	 * @return string id
	 */
	function getRandomID($tbl,$limit = 1){

		$table	 = $tbl->getClone();

		$table->select = "id";
		$table = $this->sortRandom($table);
		$table = $this->limitOffset($table,0,$limit);

		$row = $this->getRow($table);
		$id = array();
		for($i = 0 ; $i < $row ; $i++){
			$rec = $this->getRecord($table,$i);
			$id[] = $this->getData($rec,"id");
		}
		return $id;
	}

	/**
	 * テーブルの検索を行います。
	 * 利用できる演算子は以下のものです。
	 * >, <	 不等号演算子
	 * =	 等号演算子
	 * !	 非等号演算子
	 * in    in演算子
	 * &     bit演算子
	 * b	 ビトゥイーン演算子
	 * ビトゥイーン演算子の場合のみ$val2を指定します。
	 * @param $tbl TableBase テーブルデータ
	 * @param $name string カラム名
	 * @param $opp string 演算子
	 * @param $val string|int|bool 値１
	 * @param $val2 string|int|bool 値２
	 * @return TableBase テーブルデータ
	 */
	function searchTable(&$tbl, $name, $opp, $val, $val2 = null){
		if( $tbl->join ){
			//joinされている時はjoin用の検索関数を使う
			return $this->joinTableSearch( $this, $tbl , $name, $opp, $val, $val2 );
		}

		$table	 = $tbl->getClone();
		//検索パラメータがnullの場合、検索条件の追加を行わない
		if( is_null($val) )
			return $table;

		if( is_array( $val ) && !count($val) ){ return $table; }

		$table->addWhere( $this->searchTableCore($name, $opp, $val, $val2 ) );

		$this->cashReset();
		return $table;
	}
	/**
	 * 副問い合わせを利用したテーブルの検索を行います。
	 * 利用できる演算子は以下のものです。
	 * >, <	 不等号演算子
	 * =	 等号演算子
	 * !	 非等号演算子
	 * in    in演算子
	 * &     bit演算子
	 * b	 ビトゥイーン演算子
	 * ビトゥイーン演算子の場合のみ$val2を指定します。
	 * @param $tbl TableBase
	 * @param $name string
	 * @param $opp string
	 * @param $subTable TableBase
	 * @return TableBase テーブルデータ
	 */
	function searchTableSubQuery(&$tbl, $name, $opp, &$subTable){

		$table	 = $tbl->getClone();

		if($table->join){
			$table->addWhere( $this->tableName.".".$name.' '.$opp.' ('.$subTable->getString().' )' );
		}else{
			$table->addWhere( $name.' '.$opp.' ('.$subTable->getString().' )' );
		}

		$this->cashReset();
		return $table;
	}

	/**
	 * searchTableで使用する構文生成を行なう。
	 * join後のtable外部tableなどからも再利用する為の分離
	 *
	 * 内部的にしか呼ばせない為private
	 * @param $name string カラム名
	 * @param $opp string 演算子
	 * @param $val string|int|bool 値１
	 * @param $val2 string|int|bool 値２
	 * @param $tbl_name string
	 * @return string
	 */
	private function searchTableCore(&$name, &$opp, &$val, &$val2 = null, &$tbl_name = null ){
		if( $opp == 'in' && !is_array($val) ){
			$val = explode('/',$val);
		}

		if( is_array( $val ) ){
			//array_mapと匿名関数の代替処理
			$val_buf = Array();
			foreach( $val as $v ){
				$val_buf[] = $this->sqlDataEscape($v,$this->colType[$name]);
			}
			$val = '('.join(',',$val_buf).')';
		}else{
			$val = $this->sqlDataEscape($val,$this->colType[$name]);
		}
		if( !is_null( $tbl_name ) ){
			$name = $tbl_name.'.'.$name;
		}

		if( isset($val2) ){
			if( $opp == 'b' ){
				if( is_string($val2) ){ $val2	 = "'". $val2. "'"; }
				if( $val > $val2 ){ $tmp = $val; $val = $val2; $val2 = $tmp; }
				$str = " ". $name. " BETWEEN ". $val. " AND ". $val2;
			}else{//val2がフラグ判定後のチェック
				$str = " ". $name. " ".$opp." ". $val. " ". $val2." ".$val;
				//sample
				//$name:id
				//$opp:&
				//$val:0x00000001
				//$val2:=
				// ->where : id & 0x00000001 = 0x00000001
			}
		}else{
			if( $opp == '==' || strpos( $val, '%' ) === false ){
				if($opp == '=='){
					$opp = "=";
				}
				if( $opp == '!' ){
					$str  = " ". $name. " <> ". $val;
				}else if($opp == 'isnull'){
					$str  = " ( ". $name. " is null OR ". $name. " = '' ) ";
				}else{
					$str  = " ". $name. " ". $opp ." ". $val;
				}
			}else{
				if( $opp == '!' ){
					$str  = " ". $name. " not like ". $val;
				}else{
					$str  = " ". $name. " like ". $val;
				}
			}
		}
		return $str;
	}
	/**
	 * havingの設定を行なう
	 *
	 * 内部的にしか呼ばせない為private
	 *
	 * @param $name string
	 * @param $opp string
	 * @param $val string|int|bool
	 * @param null $val2 string|int|bool
	 * @return string
     */
	private function addHaving($name, $opp, $val, $val2 = null ){
		if( $opp == 'in' && !is_array($val) ){
			$val = explode('/',$val);
		}

		if( is_array( $val ) ){
			//array_mapと匿名関数の代替処理
			$val_buf = Array();
			foreach( $val as $v ){
				$val_buf[] = $this->sqlDataEscape($v,$this->colType[$name]);
			}
			$val = '('.join(',',$val_buf).')';
		}else{
			$val = $this->sqlDataEscape($val,$this->colType[$name]);
		}

		if( isset($val2) ){
			if( $opp == 'b' ){
				if( $val > $val2 ){ $tmp = $val; $val = $val2; $val2 = $tmp; }
				if( is_string($val2) ){ $val2	 = "'". $val2. "'"; }
				$str = " ". $name. " BETWEEN ". $val. " AND ". $val2;
			}else{//val2がフラグ判定後のチェック
				$str = " ". $name. " ".$opp." ". $val. " ". $val2." ".$val;
			}
		}else{
			if($opp == '=='){
				$opp = "=";
			}
			if( $opp == '!' ){
				$str  = " ". $name. " <> ". $val;
			}else if($opp == 'isnull'){
				$str  = " ( ". $name. " is null OR ". $name. " = '' ) ";
			}else{
				$str  = " ". $name. " ". $opp ." ". $val;
			}
		}
		return $str;
	}


	/**
	 * 空のテーブルを返す。
	 * searchの結果を空にしたりする時に使用。
	 * @return TableBase テーブルデータ
	 */
	function getEmptyTable(){
		$table	 = new Table(strtolower($this->tableName) );
		$table->addWhere( "shadow_id = -1" );
		return $table;
	}

	/**
	 * レコードをソートします。
	 * @param $tbl TableBase テーブルデータ
	 * @param $name string カラム名
	 * @param $asc string 昇順・降順を 'asc' 'desc' で指定します。
	 * @param $add bool sort条件を追加にするかどうかのフラグ。  デフォルト値はfalse
	 * @return TableBase テーブルデータ
	 */
	function sortTable(&$tbl, $name, $asc, $add = false){
		if( !preg_match( '/^[\w\.]+$/' , $name ) ){
			return $tbl;
		}

		$table	 = $tbl->getClone();

		if(is_null($add) || ! $add || !$table->order ){
			$table->order = Array();
		}

		if( $table->join && FALSE !== array_search( $name , $this->colName ) ){
			$name = $table->baseName . '.' . $name;
		}

		if( strtolower($asc) == 'asc' )
			$table->order[ $name ] = 'ASC';
		else
			$table->order[ $name ] = 'DESC';

		return $table;
	}

	/**
	 * レコードをランダムにソートします。
	 * 全件走査になるので数万件のテーブルにセットすると負荷が大きいです。
	 * limitOffsetを使っても全件走査になります。
	 * @param $tbl TableBase テーブルデータ
	 * @return TableBase テーブルデータ
	 */
	function sortRandom(&$tbl){
		$table	 = $tbl->getClone();
		$table->order[ 'RAND()']='';
		return $table;
	}

	function sortField( &$tbl , $column , $fields )
	{
		$values = Array( $column );
		$type   = $this->colType[ $column ];

		foreach( $fields as $field )
			{ $values[] = $this->sqlDataEscape( $field , $type ); }

		$table	                 = $tbl->getClone();
		$table->order[ 'FIELD' ] = '( ' . implode( ' , ' , $values ) . ' )';
		return $table;
	}
	/**
	 * ソート情報をリセットします。
	 * @param $tbl TableBase テーブルデータ
	 * @return TableBase テーブルデータ
	 */
	function sortReset(&$tbl){
		$table	 = $tbl->getClone();
		$table->order = Array();
		return $table;
	}
	/**
	 * ソートを行なわない。
	 * @param $tbl TableBase テーブルデータ
	 * @return TableBase テーブルデータ
	 */
	function sortDelete(&$tbl){
		$table	 = $tbl->getClone();
		$table->order = false;
		return $table;
	}

	/**
	 * テーブルの論理和。
	 * @param $tbl TableBase テーブルデータ
	 * @param $table2 TableBase テーブルデータ
	 * @return TableBase テーブルデータ
	 */
	function orTable(&$tbl, &$table2){
		$table1	 = $tbl->getClone();
		if( $table1->where == null ){
			if( $table2->where == null ){
				// どちらのテーブルも絞り込み条件が無い場合
				return $this->getTable();
			}else{
				// table1 に絞り込み条件が無い場合
				return $table2;
			}
		}else{
			if( $table2->where == null ){
				// table2 に絞り込み条件が無い場合
				return $table1;
			}else{
				$table1->addWhere( $table2->where , 'or' );
				$this->cashReset();
			}
			return $table1;
		}
	}
	/**
	 * テーブルの論理和。(配列対応版
	 * func_get_argsでは参照を受けれない為配列にて
	 * @param $a array テーブルデータの入った配列
	 * @return TableBase テーブルデータ
	 */
	function orTableM($a){
		$list = array();
		for ($i = 0; $i < count($a); $i++) {
			if( $a[$i]->where != null ){
				$list[] = $i;
			}
		}
		switch( count($list) ){
			case 0:
				return $this->getTable();
			case 1:
				return $a[$list[0]];
			default:
				$table	 = $a[$list[0]]->getClone();
				for($i=1;$i<count($list);$i++){
					$table->addWhere( $a[$list[$i]]->where , 'or' );
				}
				return $table;
		}
	}

	/**
	 * テーブルの論理積。
	 * @param $tbl TableBase テーブルデータ
	 * @param $table2 TableBase テーブルデータ
	 * @return TableBase テーブルデータ
	 */
	function andTable(&$tbl, &$table2){
		$table1	 = $tbl->getClone();
		if( $table1->where == null ){
			if( $table2->where == null ){
				// どちらのテーブルも絞り込み条件が無い場合
				return $this->getTable();
			}else{
				// table1 に絞り込み条件が無い場合
				return $table2;
			}
		}else{
			if( $table2->where == null ){
				// table2 に絞り込み条件が無い場合
				return $table1;
			}else{
				$table1->addWhere( $table2->where , 'and' );
				$this->cashReset();
			}
			return $table1;
		}
	}

	/**
	 * ユニオンテーブルを作成します。
	 * ソート条件等はrTableのものを使用。
	 * @param $lTable TableBase テーブルデータ
	 * @param $rTable TableBase テーブルデータ
	 * @param $column string カラム名
	 * @return TableBase テーブルデータ
	 */
	function unionTable(&$lTable, &$rTable, $column = null)
	{
		$table = $rTable->getClone();
		$tmpTable = $lTable->getClone();
		if( !is_null( $column ) )
		{
			$table->select = $column;
			$tmpTable->select = $column;
		}
		$table->setUnion( $tmpTable );
		if( count($tmpTable->union))
		{
			foreach( $tmpTable->union as $unionTable )
			{
				$table->setUnion( $unionTable );
			}

		}
		return $table;
	}

	/**
	 * サブクエリによる外部結合(結合条件をsql文で渡す)
	 *
	 * @param $method string
	 * @param $tbl TableBase
	 * @param $sub_tbl TableBase
	 * @param $n_name string
	 * @param $join_sql string
	 * @return TableBase テーブルデータ
	 */

	function outerJoinTableSubQuerySQL( $method, &$tbl, &$sub_tbl, $n_name, $join_sql ){
		$table	 = $tbl->getClone();

		$b_name = strtolower($this->tableName);

		switch($method){
			case "left";
			case "right";
			$join = $method." join";	break;
			default; $join = "join";
		}

		if( $table->outer ){
			$table->outer .= " {$join} (".$sub_tbl->getString().") {$n_name} on {$join_sql}";
		}else{
			$table->select	= $b_name.".*";
			$table->outer	= " {$join} (".$sub_tbl->getString().") {$n_name} on {$join_sql}";

			$table->changeJoinTable( $b_name );
		}

		$table->join = true;
		$table->outer_f = true;

		return $table;
	}

	/**
	 * サブクエリによる外部結合
	 *
	 * @param $method string
	 * @param $tbl TableBase
	 * @param $sub_tbl TableBase
	 * @param $n_name string
	 * @param $b_col string
	 * @param $n_col string
	 * @return TableBase テーブルデータ
	 */
	function outerJoinTableSubQuery( $method, &$tbl, &$sub_tbl, $n_name, $b_col, $n_col ){
		$table	 = $tbl->getClone();

		$b_name = strtolower($this->tableName);

		switch($method){
			case "left";
			case "right";
			$join = $method." join";	break;
			default; $join = "join";
		}

		if( $table->outer ){
			$table->outer .=  " {$join} (".$sub_tbl->getString().") {$n_name} on {$b_name}.{$b_col} = {$n_name}.{$n_col}";
		}else{
			$table->select	= $b_name.".*";
			$table->outer	= " {$join} (".$sub_tbl->getString().") {$n_name} on {$b_name}.{$b_col} = {$n_name}.{$n_col}";

			$table->changeJoinTable( $b_name );
		}

		$table->join = true;
		$table->outer_f = true;

		return $table;
	}

	/**
	 * テーブルの外部結合(結合条件をsql文で渡す
	 * @param $tbl TableBase テーブルデータ
	 * @param $n_name string テーブル名
	 * @param $query string 結合条件のsql文
	 * @return TableBase テーブルデータ
	 */
	function outerJoinSQL( $method, &$tbl, $n_name, $query){
		$table	 = $tbl->getClone();
		$b_name = strtolower($this->tableName);

		switch($method){
			case "left";
			case "right";
			$join = $method." join";	break;
			default; $join = "join";
		}

		if($table->outer_f){
			$table->outer = $table->outer." {$join} {$n_name} on {$query}";
		}else{
			$table->select	= $this->tableName.".*";
			$table->outer = "{$join} {$n_name} on {$query}";

			$table->changeJoinTable( $b_name );
		}

		$table->outer_f = true;
		$table->join = true;

		return $table;
	}

	/**
	 * テーブルの外部結合
	 * @param $method string 結合方法
	 * @param $tbl Table テーブルデータ
	 * @param $b_name string テーブル名
	 * @param $n_name string テーブル名
	 * @param $b_col string カラム名
	 * @param $n_col string カラム名
	 * @param $n_tbl_name string 結合に使うテーブル名
	 * @return Table テーブルデータ
	 */
	function outerJoin( $method, &$tbl, $b_name, $n_name, $b_col, $n_col, $n_tbl_name = null){
		$table	 = $tbl->getClone();

		$b_name = strtolower($this->prefix.$b_name);
		$n_name = strtolower($this->prefix.$n_name);

		if( !is_null($n_tbl_name) )	{ $_n_name = $n_name.' '.$n_tbl_name; $_n_tbl_name = $n_tbl_name; }
		else						{ $_n_tbl_name = $n_name; }

		switch($method){
			case "left";
			case "right";
			$join = $method." join";	break;
			default; $join = "join";
		}

		if($table->outer_f){
			$table->outer = $table->outer." {$join} {$n_name} on {$b_name}.{$b_col} = {$n_name}.{$n_col}";
		}else{
			$table->select	= $this->tableName.".*";
			$table->outer = "{$join} {$n_name} on {$b_name}.{$b_col} = {$n_name}.{$n_col}";

			$table->changeJoinTable( $b_name );
		}

		$table->outer_f = true;
		$table->join = true;

		return $table;
	}

	/**
	 * テーブルの結合
	 * @param $tbl TableBase テーブルデータ
	 * @param $b_name string テーブル名
	 * @param $n_name string テーブル名
	 * @param $b_col string カラム名
	 * @param $n_col string カラム名
	 * @return TableBase テーブルデータ
	 */
	function joinTable( &$tbl, $b_name, $n_name, $b_col, $n_col, $n_tbl_name = null ){
		$_b_name = strtolower($this->prefix.$b_name);
		$_n_name = strtolower($this->prefix.$n_name);
		if( !is_null($n_tbl_name) )	{ $_n_name = $_n_name.' '.$n_tbl_name; $_n_tbl_name = $n_tbl_name; }
		else						{ $_n_tbl_name = $_n_name; }

		return $this->joinTableSQL( $tbl, $b_name, $n_name, $_b_name.".".$b_col." = ".$_n_tbl_name.".".$n_col." ", $n_tbl_name );
	}
	/**
	 * テーブルのLike結合
	 * @param $tbl TableBase テーブルデータ
	 * @param $b_name string テーブル名
	 * @param $n_name string テーブル名
	 * @param $b_col string カラム名
	 * @param $n_col string カラム名
	 * @param $n_tbl_name string テーブル名
	 * @return TableBase テーブルデータ
	 */
	function joinTableLike( &$tbl, $b_name, $n_name, $b_col, $n_col, $n_tbl_name = null ){
		$_b_name = strtolower($this->prefix.$b_name);
		$_n_name = strtolower($this->prefix.$n_name);
		if( !is_null($n_tbl_name) )	{ $_n_name = $_n_name.' '.$n_tbl_name; $_n_tbl_name = $n_tbl_name; }
		else						{ $_n_tbl_name = $_n_name; }

		return $this->joinTableSQL( $tbl, $b_name, $n_name, $_b_name.".".$b_col." like '%' || ".$_n_tbl_name.".".$n_col." || '%' ", $n_tbl_name );
	}
	/**
	 * テーブルの結合(結合条件をsql文で渡す
	 * @param $tbl TableBase テーブルデータ
	 * @param $b_name string テーブル名
	 * @param $n_name string テーブル名
	 * @param $join_sql string 結合条件のsql文
	 * @param $n_tbl_name string テーブルにasで付与している名前
	 * @return TableBase テーブルデータ
	 */
	function joinTableSQL( &$tbl, $b_name, $n_name, $join_sql, $n_tbl_name = null ){
		$table	 = $tbl->getClone();

		$b_name = strtolower($this->prefix.$b_name);
		$n_name = strtolower($this->prefix.$n_name);

		if( !is_null($n_tbl_name) )	{ $n_name = $n_name.' '.$n_tbl_name; }
		else						{ $n_tbl_name = $n_name; }

		if( $table->join ){
			$table->from .= ", ".$n_name;
		}else{
			$table->select	= $b_name.".*";
			$table->from	= $b_name. ", ".$n_name;

			$table->changeJoinTable( $b_name );
		}

		$table->addWhere( '('. $join_sql .')' );

		$table->join = true;

		return $table;
	}
	/**
	 *
	 * @param $tbl TableBase
	 * @param $sub_tbl TableBase
	 * @param $n_name string
	 * @param $b_col string
	 * @param $n_col string
	 * @return TableBase テーブルデータ
	 */
	function joinTableSubQuery( &$tbl, &$sub_tbl, $n_name, $b_col, $n_col ){
		$table	 = $tbl->getClone();

		$b_name = strtolower($this->tableName);

		if( $table->join ){
			$table->from .= ", ".'('.$sub_tbl->getString().') '.$n_name;
		}else{
			$table->select	= $b_name.".*";
			$table->from	= $b_name. ", ".'('.$sub_tbl->getString().') '.$n_name;

			$table->changeJoinTable( $b_name );
		}

		$table->addWhere( $b_name.".".$b_col." = ".$n_name.".".$n_col." " );

		$table->join = true;

		return $table;
	}

	/**
	 * @param $tbl TableBase
	 * @param $sub_tbl TableBase
	 * @param $n_name string
	 * @param $join_sql string
	 * @return TableBase テーブルデータ
	 */
	function joinTableSubQuerySQL( &$tbl, &$sub_tbl, $n_name, $join_sql ){
		$table	 = $tbl->getClone();

		$b_name = strtolower($this->tableName);

		if( $table->join ){
			$table->from .= ", ".'('.$sub_tbl->getString().') '.$n_name;
		}else{
			$table->select	= $b_name.".*";
			$table->from	= $b_name. ", ".'('.$sub_tbl->getString().') '.$n_name;

			$table->changeJoinTable( $b_name );
		}

		$table->addWhere( $join_sql );

		$table->join = true;

		return $table;
	}
	/**
	 * @param $db SQLDatabaseBase
	 * @param $tbl TableBase
	 * @param $name string
	 * @param $opp string
	 * @param $val string|int|bool
	 * @param $val2 string|int|bool
	 * @param $tbl_name string
	 * @return TableBase テーブルデータ
	 */
	function joinTableSearch( &$db ,&$tbl ,$name, $opp, $val, $val2 = null, $tbl_name = null ){

		$table	 = $tbl->getClone();

		//検索パラメータがnullの場合、検索条件の追加を行わない
		if( is_null($val) ){ return $table; }
		if( is_array( $val ) && !count($val) ){ return $table; }

		if( is_null($tbl_name) )
			$tbl_name = $db->tableName;

		$sql = $db->searchTableCore($name, $opp, $val, $val2, $tbl_name );

		$table->addWhere( $sql );

		$this->cashReset();
		return $table;
	}

	/**
	 * テーブルの $start 行目から $num 個取り出す。
	 * @param $table TableBase テーブルデータ
	 * @param $start int オフセット
	 * @param $num int 数
	 * @return TableBase テーブルデータ
	 */
	function limitOffset( $table, $start, $num ){
		$ttable			 = $table->getClone();
		$ttable->offset	 = $start;
		$ttable->limit	 = $num;

		$this->cashReset();
		return $ttable;
	}

	/**
	 * 暗黙IDの最大値を返す
	 * @return Integer max
	 */
	function getMaxID(){
		global $SQL_MASTER;

		if( $this->_DEBUG ){ d( "getMaxID() : ". "select max(shadow_id) as max from ". $this->tableName. "<br/>\n", 'sql'); }


		$sql = "select id_count as max from ".$this->prefix."system_tables WHERE table_name = '". $this->tablePlaneName."'";
		$result	 = $this->sql_query( $sql );

		if( !$result ){
			if( !$this->_DEBUG ){ d( "getMaxID() : ".$sql. "<br/>\n", 'sql'); }
			throw new InternalErrorException("getMaxID() : SQL MESSAGE ERROR. \n");
		}

		$data = $this->sql_fetch_array($result);

		return $data['max'];
	}

	/**
	 * recordから登録用のSQL文を取得します。
	 * @return String SQL文
	 */
	function toString($rec, $mode){
		$row = count( $this->colName );
		$sql = '';

		for($i=0; $i<$row; $i++){
			$name = $this->colName[$i];

			if( $mode == "UPDATE" )
			$sql	 .= $name. " = ";

			// カラムの型を取得
			$type	 = $this->colType[ $name ];

			// カラムの値を取得
			$data	 = $this->getData( $rec, $name );

			//sqlとして利用可能なデータに変形
			if( is_array( $data ) ){
				// カラムの値が配列の場合、配列データを / で区切って格納
				$sql .= $this->sqlDataEscape( join( $data,'/') , $type  );
			}else{
				// カラムの値が実値の場合
				$sql .= $this->sqlDataEscape( $data , $type );
			}

			if( $i != count( $this->colName ) - 1 ){
				$sql	 .= ", \n";
			}
		}

		return $sql;
	}

	/**
	 * 集約関数のまとめ用
	 *
	 * @param $agg string
	 * @param $name string
	 * @param $tbl TableBase
	 * @return int
	 */
	private function getAggregate( $agg, $name, $tbl = null )
	{

		if( is_null($tbl) ){
			$table = $this->getTable();
		}else{
			$table = $tbl->getClone();
		}

		if( $table->join && FALSE !== array_search( $name , $this->colName ) ){
			$name = $table->baseName . '.' . $name;
		}

		$table->select = "$agg($name) as $agg";

		$sql = $table->toSelectFrom().$table->getWhere();

		if( $this->_DEBUG ){ d( "getAggregate() : ". $sql . "<br/>\n", 'sql' ); }


		$result	 = $this->sql_query( $sql );

		if( !$result ){
			if( !$this->_DEBUG ){ d( "getAggregate() : ". $sql. "<br/>\n", 'sql'); }
			throw new InternalErrorException("getAggregate() : SQL MESSAGE ERROR. \n");
		}

		$data = $this->sql_fetch_array($result);
		return (int)$data[$agg];
	}

	/**
	 * 現在のテーブルから指定したcolumnの総合計を取得します。
	 * @param $name string
	 * @param $tbl TableBase
	 * @return int
	 */
	function getSum( $name, $tbl = null){
		return $this->getAggregate('sum',$name,$tbl);
	}

	/**
	 * 現在のテーブルから指定したcolumnの最大値を取得します。
	 * @param $name string
	 * @param $tbl TableBase
	 * @return int
	 */
	function getMax( $name, $tbl = null){
		return $this->getAggregate('max',$name,$tbl);
	}

	/**
	 * 現在のテーブルから指定したcolumnの最小値を取得します。
	 * @param $name string
	 * @param $tbl TableBase
	 * @return int
	 */
	function getMin( $name, $tbl = null){
		return $this->getAggregate('min',$name,$tbl);
	}

	/**
	 * 現在のテーブルから指定したcolumnの平均値を取得します。
	 * @param $name string
	 * @param $tbl TableBase
	 * @return int
	 */
	function getAvg( $name, $tbl = null){
		return $this->getAggregate('avg',$name,$tbl);
	}

	/**
	 * 現在のテーブルのSQLを指定したcolumnでgroupbyし、指定カラムを集計した結果を付与したテーブルを返す。
	 * 実際の取得はgetRecored、getDataを使う
	 * @return TableBase テーブルデータ
	 */
	private function getAggregateTable( $agg, $agg_name, $group_name, $table = null, $opp = null, $val = null){

		if( $this->_DEBUG ){ d( "getAggregateTable();\n", 'sql' ); }

		if( is_null($table) ){
			$table = $this->getTable();
		}else{
			$table = $table->getClone();
		}

		if( $table->join ){
			$agg_name = $this->tableName.".".$agg_name;
		}

		$table->select	 = str_replace( '*' , "$group_name , $agg($agg_name) as $agg" , $table->select );


		if( is_null($table->group) ){
			$table->group = $group_name;
		}else{
			$table->group = $table->group.','.$group_name;
		}

		if($opp == "string"){
			if( $table->having != null){
				$table->having .= 'AND ';
			}else{ $table->having = ''; }
			$table->having .= $val;
		}elseif( $opp != null ){
			$str = $this->addHaving( $agg, $opp, $val );
			if( $table->having != null){
				$table->having .= 'AND ';
			}else{ $table->having = ''; }

			$table->having .= $str;
		}

		return $table;
	}

	/**
	 * 現在のテーブルのSQLを指定したcolumnでgroupbyし、指定カラムをsumした結果を付与したテーブルを返す。
	 * 実際の取得はgetRecored、getDataを使う
	 * @return TableBase テーブルデータ
	 */
	function getSumTable( $col_name, $group_name, $table = null, $opp = null, $val = null){
		return $this->getAggregateTable('sum',$col_name, $group_name, $table , $opp,  $val );
	}

	/**
	 * 現在のテーブルのSQLを指定したcolumnでgroupbyし、指定カラムをmaxした結果を付与したテーブルを返す。
	 * 実際の取得はgetRecored、getDataを使う
	 * @return TableBase テーブルデータ
	 */
	function getMaxTable( $col_name, $group_name, $table = null, $opp = null, $val = null){
		return $this->getAggregateTable('max',$col_name, $group_name, $table , $opp,  $val );
	}

	/**
	 * 現在のテーブルのSQLを指定したcolumnでgroupbyし、指定カラムをminした結果を付与したテーブルを返す。
	 * 実際の取得はgetRecored、getDataを使う
	 * @return TableBase テーブルデータ
	 */
	function getMinTable( $col_name, $group_name, $table = null, $opp = null, $val = null){
		return $this->getAggregateTable('min',$col_name, $group_name, $table , $opp,  $val );
	}

	/**
	 * 現在のテーブルのSQLを指定したcolumnでgroupbyし、指定カラムをavgした結果を付与したテーブルを返す。
	 * 実際の取得はgetRecored、getDataを使う
	 * @return TableBase テーブルデータ
	 */
	function getAvgTable( $col_name, $group_name, $table = null, $opp = null, $val = null){
		return $this->getAggregateTable('avg',$col_name, $group_name, $table , $opp,  $val );
	}

	/**
	 * 現在のテーブルのSQLを指定したcolumnでgroupbyし、指定カラムをcntした結果を付与したテーブルを返す。
	 * 実際の取得はgetRecored、getDataを使う
	 * @return TableBase テーブルデータ
	 */
	function getCountTable( $name, $tbl = null, $returnColumnAll = false, $opp = null, $val = null){
		$name	 = strtolower( $name );

		if( $this->_DEBUG ){ d( "getCountTable();\n", 'sql' ); }

		if( is_null($tbl) ){
			$table = $this->getTable();
		}else{
			$table = $tbl->getClone();
		}

		if($returnColumnAll){
			$table->select = $table->select." , count({$name}) as cnt";
		}else{
			$table->select = "$name , count(*) as cnt";
		}

		if( is_null($table->group) ){
			$table->group = $name;
		}else{
			$table->group = $table->group.','.$name;
		}

		$table = $this->sortTable( $table, 'cnt', 'asc' );

		if($opp == "string"){
			if( $table->having != null){
				$table->having .= 'AND ';
			}else{ $table->having = ''; }
			$table->having .= $val;
		}elseif( $opp != null ){
			$str = $this->addHaving( 'cnt', $opp, $val );
			if( $table->having != null){
				$table->having .= 'AND ';
			}else{ $table->having = ''; }

			$table->having .= $str;
		}

		$this->cashReset();
		return $table;
	}

	/**
	 * 選択カラムを追加。  geCountTableなどでデータの欲しいカラムが表示されない時に有効
	 * @return TableBase テーブルデータ
	 */
	function addSelectColumn( &$tbl, $name, $group = true, $table_name = null ){
		$table	 = $tbl->getClone();

		if( !is_null( $table_name ))
		{
			$name = $this->prefix. strtolower($table_name).'.'.$name;
		}

		$table->select .= ','.$name;

		if(strlen($table->group) && $group){
			$table->group .= ','.$name;
		}

		return $table;
	}

	/**
	 * group byカラムを追加。
	 * @return TableBase テーブルデータ
	 */
	function addGroupColumn( &$tbl, $name, $table_name = null ){
		$table	 = $tbl->getClone();

		if( !is_null( $table_name ))
		{
			$name = $this->prefix. strtolower($table_name).'.'.$name;
		}

		if( '*' == $table->select )
			{ $table->select .= ',max('.$name . ')'; }

		if(strlen($table->group)){
			$table->group .= ','.$name;
		}else{
			$table->group = $name;
		}

		return $table;
	}

	/**
	 * 指定カラムのみ結果を重複を削除して返す
	 * @see include/base/DatabaseBase#getDistinctColumn($name, $tbl)
	 * @return TableBase テーブルデータ
	 */
	function getDistinctColumn( $name , &$tbl){

		$table	 = $tbl->getClone();

		if( is_null($table) ){
			$table = $this->getTable();
		}

		$table->select	 = "DISTINCT " . $table->select;
		$table->select	 = str_replace( '*' , "$name " , $table->select );
		$table = $this->sortTable($table,$name,'asc');

		return $table;
	}

	/**
	 * 重複を削除して返す
	 * @param $tbl TableBase
	 * @return TableBase
	 */
	function getDistinct( &$tbl){

		$table	 = $tbl->getClone();

		if( is_null($table) )
			$table = $this->getTable();

		$table->select	 = "DISTINCT " . $table->select;

		return $table;
	}

	/**
	 * 指定カラムのみ返す
	 * @see include/base/DatabaseBase#getColumn($name, $tbl)
	 * @return TableBase テーブルデータ
	 */
	function getColumn( $name , &$tbl){

		$table	 = $tbl->getClone();

		if( is_null($table) )
		$table = $this->getTable();

		$table->select	 = str_replace( '*' , "$name " , $table->select );
		$table = $this->sortTable($table,$name,'asc');

		return $table;
	}

	/**
	 * 期間でgoroup化する
	 * @param $tbl TableBase テーブルデータ
	 * @param $column string 対象のカラム
	 * @param $format time  string 纏めるフォーマット
	 * @return TableBase テーブル
	 */
	function dateGroup(&$tbl,$column,$format){
		$table	 = $tbl->getClone();
		$table->select = $this->sql_date_group($column,$format) . ' as date_group';
		$table->group = 'date_group';
		return $this->sortTable($table,'date_group','asc');
	}

	function getClumnNameList(){
		return array_slice($this->colName,2);
	}

	/**
	 * rowとRecordのcashを削除します。
	 */
	function cashReset(){
		if( $this->_DEBUG ){ d( "cashReset() : reset <br/>\n", 'sql' ); }
		$this->row = -1;
		$this->rec_cash = null;
	}

	/**
	 * typeがpasswordなカラムを復号する関数をsqlに追加する
	 * @param $tbl TableBase テーブルデータ
	 * @return TableBase 適用済のテーブル
	 */
	function addPasswordDecrypt( &$tbl ){
		global $CONFIG_SQL_PASSWORD_KEY;

		$pass_keys = array_keys( $this->colType, 'password'  );
		if( !count($pass_keys) ){ return $tbl; }

		$table	 = $tbl->getClone();

		foreach( $pass_keys as $key ){
			$table->select .= ','.$this->sql_to_decrypt( $key, $CONFIG_SQL_PASSWORD_KEY ) . ' as ' . $this->pass_prefix . $key;
			if(strlen($table->group)){
				$table->group .= ','.$this->pass_prefix . $key;
			}
		}

		$union_count = count($table->union);
		if( $union_count > 0 )
		{
			for( $i=0; $i<$union_count; $i++ )
			{
				$table->union[$i] = $this->addPasswordDecrypt( $table->union[$i] );
			}
		}

		return $table;
	}

	/**
	 * typeがpasswordなカラムを復号する関数をsqlに追加する
	 * @param $rec array 変換を行なうレコード
	 * @return array 適用済のレコード
	 */
	function replacePasswordDecrypt( &$rec ){
		$pass_keys = array_keys( $this->colType, 'password'  );
		if( !count($pass_keys) ){ return $rec; }

		foreach( $pass_keys as $key ){
			$rec[ $key ] = $rec[ $this->pass_prefix . $key ];
			unset($rec[ $this->pass_prefix . $key ]);
		}
		return $rec;
	}


	function sql_query($sql, $update = false){
		throw new InternalErrorException('SQLDatabase::'.__FUNCTION__.'  Unmounting');
	}

	function sql_fetch_assoc(&$result,$index){
		throw new InternalErrorException('SQLDatabase::'.__FUNCTION__.'  Unmounting');
	}

	function sql_fetch_array(&$result){
		throw new InternalErrorException('SQLDatabase::'.__FUNCTION__.'  Unmounting');
	}

	function sql_fetch_all(&$result){
		throw new InternalErrorException('SQLDatabase::'.__FUNCTION__.'  Unmsounting');
	}

	function sql_num_rows(&$result){
		throw new InternalErrorException('SQLDatabase::'.__FUNCTION__.'  Unmounting');
	}

	function sql_convert( $val ){
		throw new InternalErrorException('SQLDatabase::'.__FUNCTION__.'  Unmounting');
	}

	function sql_escape( $val ){
		throw new InternalErrorException('SQLDatabase::'.__FUNCTION__.'  Unmounting');
	}

	function sql_date_group($column,$format){
		throw new InternalErrorException('SQLDatabase::'.__FUNCTION__.'  Unmounting');
	}

	function sql_to_encrypt( $str, $key ){
		throw new InternalErrorException('SQLDatabase::'.__FUNCTION__.'  Unmounting');
	}

	function sql_to_decrypt( $str, $key ){
		throw new InternalErrorException('SQLDatabase::'.__FUNCTION__.'  Unmounting');
	}

	function to_boolean( $val ){
		if( is_bool( $val ) )		{ return $val; }
		else						{ return SystemUtil::convertBool($val);}
	}


	function sqlDataEscape($val,$type,$quots = true){
	global $CONFIG_SQL_PASSWORD_KEY;
		switch ($type) {
			case 'password':
			case 'string':
			case 'varchar' :
			case 'date' :
			case 'char' :
			case 'image':
			case 'file':
			case 'text':
			case 'blob':
				// カラムの型が文字列の場合

				$sqlstr = $this->sql_escape( $val );

				/*if( preg_match( '/\\\\$/' , $sqlstr ) )
					$sqlstr .= ' ';
				*/
				if($quots){ $sqlstr = "'".$sqlstr."'"; }

				if( 'password' == $type ){
					$sqlstr = $this->sql_to_encrypt( $sqlstr, $CONFIG_SQL_PASSWORD_KEY );
				}
				break;
			case 'double':
				// カラムの型が実数の場合
				$sqlstr = doubleval($val);
				break;
			case 'boolean':
				if( SystemUtil::convertBool($val) ) { $sqlstr = 'TRUE'; }
				else								{ $sqlstr = 'FALSE'; }
				break;
			default:
			case 'int':
			case 'timestamp':
				// カラムの型が整数の場合
				$sqlstr = intval($val);
				break;
		}
		return $sqlstr;
	}

	//debugフラグ操作用
	function onDebug(){ $this->_DEBUG = true; }
	function offDebug(){ $this->_DEBUG = false; }

	private function getColumnType($name){
		throw new InternalErrorException('SQLDatabase::'.__FUNCTION__.'  Unmounting');
	}

	private function getModelRecord($rec){
		global $THIS_TABLE_MODEL_CLASS;
		global $model_path;

		if( $THIS_TABLE_MODEL_CLASS[ $this->tableName ] ){
			if( $this->_DEBUG )				{ d( "getModelRecord() : ".$this->tableName." Model Record.<br/>\n", 'sql'); }
			$class_name = $THIS_TABLE_MODEL_CLASS[ $this->tableName ].'Model';
			include_once $model_path.$class_name.'.php';
			$rec = new $class_name($rec);
			/*
			if( !class_exists( $class_name ) ){
				if( file_exists( $model_path.$class_name.'.php') )
				{
					include_once $model_path.$class_name.'.php';
					if ( class_exists( $class_name ) )
					{
						$rec = new $class_name($rec);
					}
				}
			}*/
		}
		return $rec;
	}
}

/*******************************************************************************************************/
/*******************************************************************************************************/
/*******************************************************************************************************/
/*******************************************************************************************************/

class TableBase{
	var $sql;
	var $select	 = null;
	var $from	 = null;
	var $outer   = null;
	var $where	 = null;
	var $having	 = null;
	var $order	 = Array();
	var $group	 = null;
	var $offset	 = null;
	var $limit	 = null;
	var $deleted = false;

	var $baseName = ''; //ベーステーブル名
	var $join    = false;//joinフラグ
	var $outer_f = false;//joinフラグ
	var $union	 = Array();
	var $union_a = false;
	var $sql_char_code = null;

	var $status = 0;

	var $cashed = false;
	var $cash = null;

	function __construct($from){$this->baseName = $from;}

	function getClone(){
		$table			 = new Table( $this->from );
		$table->select	 = $this->select;
		$table->where	 = $this->where;
		$table->having	 = $this->having;
		$table->group	 = $this->group;
		$table->order	 = $this->order;
		$table->offset	 = $this->offset;
		$table->limit	 = $this->limit;
		$table->baseName = $this->baseName;
		$table->join	 = $this->join;
		$table->outer	 = $this->outer;
		$table->outer_f  = $this->outer_f;
		$table->union_a	 = $this->union_a;
		$table->deleted	 = $this->deleted;

		$union_count = count($this->union);
		if( $union_count > 0 )
		{
			for( $i=0; $i<$union_count; $i++ )
			{
				$table->union[$i] = $this->union[$i]->getClone();
			}
		}

		$table->status	 = $this->status;
		return $table;
	}

	function getString($row=false){
		global $SQL_MASTER;

		$sql	 = $this->toSelectFrom();
		$sql	.= $this->getWhere();

		if( count($this->union) ){
			foreach( $this->union as $unionTable )
			{
				$sql .= ' UNION';
				if($this->union_a){
					$sql .= ' ALL';
				}
				$sql .= ' '.$unionTable->getUnionString();
			}
		}

		if( $this->group != null ){
			$sql	 .= " GROUP BY ". $this->group;
			if( $this->having != null ){
				$sql	 .= " HAVING ". $this->having;
			}
		}
		if( !$row ){
			if( is_array( $this->order ) ){
				if( count($this->order) ){
					$sql	 .= " ORDER BY";
					$ord = Array();
					foreach( $this->order as $col => $val ){
						$ord[] = " $col $val";
					}
					$sql .= join(',',$ord);
				}else{
					$sql	 .= " ORDER BY shadow_id";
				}
			}
		}

		$sql .= $this->getLimitOffset();

		return $this->sql_convert( $sql );
	}

	function getUnionString( $data = null ){

		if( isset($data) ) { $this->select	 = str_replace( '*' , "shadow_id,".$data , $this->select ); }
		$sql	 = $this->toSelectFrom();
		$sql	.= $this->getWhere();

		if( $this->group != null ){
			$sql	 .= " GROUP BY ". $this->group;
		}

		return $this->sql_convert( $sql );

	}

	//Row取得用
	function getRowString(){
		$cTable = $this->getClone();

		if( $cTable->select == '*' && is_null( $cTable->limit ) && !count($this->union) ){
			$cTable->select	 = 'COUNT(*) as cnt';
			$sql	 = $cTable->toSelectFrom();

			$sql	.= $this->getWhere();

			$sql .= $this->getLimitOffset();
		}else{
			$sql = 'SELECT count(*) as cnt FROM ('.
				$cTable->getString(true)
				.') as result_table';
		}

		return $this->sql_convert( $sql );
	}

	function getLimitOffset(){
		throw new RuntimeException('Table::'.__FUNCTION__.'  Unmounting');
	}

	function sql_convert( $val ){
		throw new RuntimeException('Table::'.__FUNCTION__.'  Unmounting');
	}

	/*
	 * and/orの条件を追加する
	 * @param sql			追加する条件
	 * @param conjunction	接続詞(and/or)
	 */
	function addWhere( $sql, $conjunction = 'and' ){
		$conjunction = strtolower($conjunction);
		if( is_null($this->where) ){
			$this->where= $sql ;
		}else{
			if(is_string($this->where)){
				$this->where = Array( $conjunction => Array( $this->where ) );
			}else if(!isset($this->where[$conjunction])){
				$old = $conjunction == 'and' ? 'or' : 'and';
				$this->where[ $conjunction ][$old] = $this->where[ $old ];

				unset($this->where[ $old ]);
			}
			array_push($this->where[$conjunction],$sql);
		}

		$union_count = count($this->union);
		if( $union_count > 0 )
		{
			for( $i=0; $i<$union_count; $i++ ) { $this->union[$i]->addWhere( $sql, $conjunction ); }
		}
	}

	/*
	 * 保存しているwhereの内容を解析して文字列に整形する
	 * @param $array		andもしくはorの配列
	 */
	function getWhere( ){
		$ret = "";

		$where = $this->getWhereReflexive();

		if( $ret && $where ){
			$ret .= " AND ";
		}
		$ret .= $where;

		if(strlen($ret)){ $ret = " WHERE " . $ret; }

		return $ret;
	}


	function getWhereReflexive( $conjunction = null , $array = null ){
		if(is_null($array)){
			if(is_null($this->where)){return "";}
			else if(!is_array($this->where)){return '('.$this->where.')';}

			foreach( $this->where as $key => $val ){
				$array = $val;
				$conjunction = $key;
				break;
			}
		}
		foreach( $array as $key => $val ){
			if(is_array($val)){$array[$key]=$this->getWhereReflexive($key,$val);}
		}
		return "(".implode($array," $conjunction ").")";
	}

	/*
	 * 保存しているwhereの内容を引数に指定されたtableをベースとするjoinテーブルに変更する
	 */
	function changeJoinTable( $base_tbl_name, $array = null ){

		$flg = false;
		if(is_null($array)){

			//まずorder
			if(!count($this->order)){ $this->order['shadow_id'] = 'DESC'; }
			$new_order = Array();
			foreach( $this->order as $key => $val ){
				$new_order[ $base_tbl_name.'.'.$key ] = $val;
			}
			$this->order = $new_order;


			$base_tbl_name = ' '.$base_tbl_name.'.';
			if(is_null($this->where)){return;}
			else if(!is_array($this->where)){$this->where = $base_tbl_name.$this->where; return; }
			$array = $this->where;
			$flg = true;
		}
		foreach( $array as $key => $val ){
			if(is_array($val)){$array[$key]=$this->changeJoinTable($base_tbl_name,$val);}
			else{ $array[$key]=$base_tbl_name.$val; }
		}
		if($flg){ $this->where = $array; }
		return $array;
	}

	function toSelectFrom(){
		if($this->outer_f){
			$sql = 'SELECT '.$this->select.' FROM '.$this->from.' '.$this->outer;
		}else{
			$sql = 'SELECT '.$this->select.' FROM '.$this->from;
		}
		return $sql;
	}

	function setUnion($table){
		$this->union[] = $table;
	}

	function setUnionAll($table){
		$this->union[] = $table;
		$this->union_a = true;
	}

	function onCash(){ $this->cashed = true; }
	function offCash(){ $this->cashed = false; $this->cash = null; }
}

?>