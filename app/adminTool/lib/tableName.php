<?php

	//★クラス //

	/**
		@brief 修飾済みテーブル名の取得クラス。
	*/
	class TableName //
	{
		//■データ取得 //

		/**
			@brief  ファイル出力処理用のファイルパスを取得する。
			@return CSVファイルのパス。
		*/
		function exportFile() //
		{
			global $TDB; ///<テーブル初期値設定ファイルの配列。

			return PathUtil::ModifyTDBFilePath( $TDB[ $this->tableName ] );
		}

		/**
			@brief  新規バックアップ用のテーブル名を取得する。
			@return DB上でのテーブル名。
		*/
		function newBackup() //
			{ return $this->real() . '_backup_' . $this->newBackupTime; }

		/**
			@brief  既存のバックアップテーブル名を取得する。
			@return DB上でのテーブル名。
		*/
		function currentBackup( $iMode = 'default' ) //
		{
			if( 'oldSys' == $iMode ) //旧システム名
				{ return $this->real() . '_backup' . $this->currentBackupTime; }
			else //その他
			{ return $this->real() . '_backup_' . $this->currentBackupTime; }
		}

		/**
			@brief  既存のバックアップテーブルの作成時刻を取得する。
			@return 作成時刻。
		*/
		function currentBackupTime() //
			{ return $this->currentBackupTime; }

		/**
			@brief  DB上でのテーブル名を取得する。
			@return DB上でのテーブル名。
		*/
		function real() //
			{ return strtolower( $this->prefix . $this->tableName . $this->suffix ); }

		/**
			@brief     一時テーブル名を生成する。
			@param[in] $iMarker 重複を避けるための追加の識別子。
			@return    DB上でのテーブル名。
		*/
		function temp( $iMarker = '' ) //
			{ return $this->real() . '_temporary' . strtolower( $iMarker ); }

		//■コンストラクタ・デストラクタ //

		/**
			@brief コンストラクタ。
			@param[in] $iTableName テーブル名。
			@param[in] $iSuffix    接尾辞。
		*/
		function __construct( $iTableName , $iSuffix = '' ) //
		{
			global $TABLE_PREFIX;

			$this->tableName     = $iTableName;
			$this->prefix        = $TABLE_PREFIX;
			$this->suffix        = $iSuffix;
			$this->newBackupTime = time();

			$backupTime = Query::GetBackupTime( $this->real() );

			if( $backupTime ) //既存のバックアップがある場合
				{ $this->currentBackupTime = $backupTime; }
		}

		//■変数 //
		private $tableName         = ''; ///<テーブル名
		private $prefix            = ''; ///<テーブル名の接頭辞
		private $suffix            = ''; ///<テーブル名の接尾辞
		private $newBackupTime     = 0;  ///<新規バックアップテーブルの時間
		private $currentBackupTime = 0;  ///<既存のバックアップテーブルの時間
	}
