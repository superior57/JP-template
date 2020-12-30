<?php

	//★クラス //

	/**
		@brief DB接続クラス。
	*/
	class DB //
	{
		//■処理 //

		/**
			@brief エラー情報を初期化する。
		*/
		static function ClearErrors() //
		{
			self::Open();

			self::$DB->ClearErrors();
		}

		/**
			DBに接続する。
		*/
		private static function Open() //
		{
			global $SQL_MASTER;
			global $SQL_SERVER;
			global $SQL_PORT;
			global $DB_NAME;
			global $SQL_ID;
			global $SQL_PASS;

			if( !self::$DB ) //DBオブジェクトが作成されていない場合
				{ self::$DB = CreateDBConnect( $SQL_MASTER , $SQL_SERVER , $SQL_PORT , $DB_NAME , $SQL_ID , $SQL_PASS ); }
		}

		/**
			@brief     クエリを実行する。
			@param[in] $iQueryString クエリ文字列。
			@param[in] $iBindValues  バインドする値の配列。
			@param[in] $iFetchMode   フェッチモードの指定。
			@retval    ステートメントオブジェクト クエリが成功した場合。
			@retval    false                      クエリが失敗した場合。
		*/
		static function Query( $iQueryString , $iBindValues = null , $iFetchMode = null ) //
		{
			self::Open();

			return self::$DB->query( $iQueryString , $iBindValues , $iFetchMode );
		}

		//■データ取得 //

		/**
			@brief  エラー情報を取得する。
			@return エラー情報配列。
		*/
		static function GetErrors() //
		{
			self::Open();

			return self::$DB->getErrors();
		}

		//■変数 //
		private static $DB = null; ///<DBオブジェクト。
	}
