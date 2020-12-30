<?php

	include_once "./custom/extends/logConf.php";

	/***************************************************************************************************<pre>
	 * 
	 * データベース用ログファイル書き出しストリーム
	 * 
	 * @author 吉岡幸一郎
	 * @version 1.0.0<br/>
	 * 
	 * </pre>
	 ********************************************************************************************************/

	class SQLOutputLog extends OutputLog
	{
		var $d_path;
		
		function __construct( $d_path ){
			$this->d_path = $d_path;
		}
		
		function table_log( $tableName, $action, $message ){
			global $DB_LOG_FILE_PATHS;
			global $LOG_DIRECTORY_PATH;
			global $DB_LOG_ENABLE_FLAGS;
			
			global $DB_LOG_ENABLE_INSERT;
			global $DB_LOG_ENABLE_ADD;
			global $DB_LOG_ENABLE_DELETE;
			global $DB_LOG_ENABLE_RESTORE;
			global $DB_LOG_ENABLE_UPDATE;
			global $DB_LOG_ENABLE_TABLE_UPDATE;
			
			if( isset($DB_LOG_ENABLE_FLAGS[ $tableName ]) && $DB_LOG_ENABLE_FLAGS[ $tableName ] & ${"DB_LOG_ENABLE_".$action} ){
			
				if( isset($DB_LOG_FILE_PATHS[ $tableName ]) ){
					$this->file = $LOG_DIRECTORY_PATH.$DB_LOG_FILE_PATHS[ $tableName ];
				}else{
					$this->file = $LOG_DIRECTORY_PATH.$DB_LOG_FILE_PATHS[ 'all' ];
				}
				
				$this->write($action.','.$tableName.','.$message);
			}
		}
	}