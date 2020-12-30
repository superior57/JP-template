<?php


	$ADD_LOG										 = true;								// DBの新規追加情報を記録するか
	$UPDATE_LOG										 = true;								// DBの更新情報を記録するか
	$DELETE_LOG										 = true;								// DBの削除情報を記録するか

	$MAX_LOGFILE_SIZE = 20971520; //20MB | 1024 * 1024 * 20
//	$MAX_LOGFILE_SIZE = 5242880;  //5MB | 1024 * 1024 * 5
//	$MAX_LOGFILE_SIZE = 20480;  //20KB | 1024 * 50

	 $LOG_DIRECTORY_PATH = "logs/";
	 $LOG_DEBUG_LOG_PATH = $LOG_DIRECTORY_PATH."debug.log";

	 $DB_LOG_ENABLE_NOT = 0;
	 $DB_LOG_ENABLE_INSERT  = 1;
	 $DB_LOG_ENABLE_ADD  = 1;
	 $DB_LOG_ENABLE_DELETE  = 2;
	 $DB_LOG_ENABLE_RESTORE = 4;
	 $DB_LOG_ENABLE_UPDATE  = 8;
	 $DB_LOG_ENABLE_TABLE_UPDATE  = 8;
	 $DB_LOG_ENABLE_ALL  = 15;

	// table
	// action( INSERT DELETE RESTORE UPDATE

	//dfaultの保存先
	$DB_LOG_FILE_PATHS['all'] = "dbaccess.log";

	//保存先を指定する場合
	$DB_LOG_FILE_PATHS	[ 'message' ] = "message.log";
	$DB_LOG_FILE_PATHS	[ 'entry' ] = "entry.log";


	$DB_LOG_ENABLE_FLAGS[ 'admin' ]				 = $DB_LOG_ENABLE_ALL;
	$DB_LOG_ENABLE_FLAGS[ 'cUser' ]				 = $DB_LOG_ENABLE_ALL;
	$DB_LOG_ENABLE_FLAGS[ 'nUser' ]				 = $DB_LOG_ENABLE_ALL;
	$DB_LOG_ENABLE_FLAGS[ 'mid' ]				 = $DB_LOG_ENABLE_ALL;
	$DB_LOG_ENABLE_FLAGS[ 'message' ]			 = $DB_LOG_ENABLE_ADD;
	$DB_LOG_ENABLE_FLAGS[ 'entry' ]				 = $DB_LOG_ENABLE_ADD;
	$DB_LOG_ENABLE_FLAGS[ 'items_form' ]		 = $DB_LOG_ENABLE_NOT;
	$DB_LOG_ENABLE_FLAGS[ 'items_type' ]		 = $DB_LOG_ENABLE_NOT;
	$DB_LOG_ENABLE_FLAGS[ 'items_elements' ]	 = $DB_LOG_ENABLE_NOT;
	$DB_LOG_ENABLE_FLAGS[ 'adds' ]				 = $DB_LOG_ENABLE_NOT;
	$DB_LOG_ENABLE_FLAGS[ 'area' ]				 = $DB_LOG_ENABLE_NOT;
	$DB_LOG_ENABLE_FLAGS[ 'template' ]			 = $DB_LOG_ENABLE_NOT;
	$DB_LOG_ENABLE_FLAGS[ 'system' ]			 = $DB_LOG_ENABLE_ALL;
	$DB_LOG_ENABLE_FLAGS[ 'page' ]				 = $DB_LOG_ENABLE_ALL;
