<?php

	/**
		@brief   ログ出力設定管理クラス。
		@details SQLのログの出力設定情報を管理します。
		@author  松木 昌平
		@version 1.0
		@ingroup Information
	*/
	class DBLogInfo
	{
		//■初期化

		/**
			@brief ログ出力設定を初期化する。
		*/
		private static function Initialize()
		{
			if( !self::$Initialized )
			{
				self::ImportGlobalVarConfigs();

				self::$Initialized = true;
			}
		}

		//■取得

		/**
			@brief  レコード追加のログ出力設定を取得する。
			@retval true  出力が有効な場合。
			@retval false 出力が無効な場合。
		*/
		static function AddEnable()
		{
			self::Initialize();

			return self::$AddEnable;
		}

		/**
			@brief  レコード削除のログ出力設定を取得する。
			@retval true  出力が有効な場合。
			@retval false 出力が無効な場合。
		*/
		static function DeleteEnable()
		{
			self::Initialize();

			return self::$DeleteEnable;
		}

		/**
			@brief  レコード更新のログ出力設定を取得する。
			@retval true  出力が有効な場合。
			@retval false 出力が無効な場合。
		*/
		static function UpdateEnable()
		{
			self::Initialize();

			return self::$UpdateEnable;
		}

		/**
			@brief  レコード操作のログを出力するファイルパスを取得する。
			@return ファイルパス。
		*/
		static function GetLogFilePath()
		{
			self::Initialize();

			return self::$LogFilePath;
		}

		/**
			@brief     グローバル変数から設定値をインポートする。
			@attention 移行が完了するまでの仮機能です。
		*/
		private static function ImportGlobalVarConfigs()
		{
			global $ADD_LOG;
			global $UPDATE_LOG;
			global $DELETE_LOG;
			global $DB_LOG_FILE;

			self::$AddEnable    = $ADD_LOG;
			self::$UpdateEnable = $UPDATE_LOG;
			self::$DeleteEnable = $DELETE_LOG;
			self::$LogFilePath  = $DB_LOG_FILE;
		}

		//■変数
		private static $Initialized  = false;               ///<初期化フラグ
		private static $AddEnable    = true;                ///<レコード追加のログ出力設定
		private static $UpdateEnable = true;                ///<レコード編集のログ出力設定
		private static $DeleteEnable = true;                ///<レコード削除のログ出力設定
		private static $LogFilePath  = 'logs/dbaccess.log'; ///<ログ出力ファイルのパス
	}

?>