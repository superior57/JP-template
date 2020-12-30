<?php

	//★クラス //

	/**
		@brief DB接続クラス。
	*/
	class SQLiteDB //
	{
		//■処理 //

		/**
			@brief エラー情報を初期化する。
		*/
		function clearErrors() //
			{ $this->errors = Array(); }

		/**
			@brief     クエリを実行する。
			@retval    リソース クエリが成功した場合。
			@retval    false    クエリが失敗した場合。
		*/
		function query( $iQueryString , $iBindValues = null , $iFetchMode = null ) //
		{
			$queryString = $iQueryString;
			$bindValues  = $iBindValues;

			if( is_array( $bindValues ) && count( $bindValues ) ) //有効な引数がある場合
			{
				$index = 0;

				while( FALSE !== ( $index = strpos( $queryString , '?' , $index ) ) ) //
				{
					if( !count( $bindValues ) ) //プレースホルダより先に引数が空になった場合
						{ throw new LogicException(); }

					$replaceValue  = array_shift( $bindValues );
					$queryString   = substr_replace( $queryString , $replaceValue , $index , 1 );
					$index        += strlen( $replaceValue );
				}
			}

			$result    = sqlite_query( $this->connect , mb_convert_encoding( $queryString , 'utf8' , mb_internal_encoding() ) );
			$errorInfo = sqlite_last_error( $this->connect );

			if( $errorInfo ) //エラーがある場合
			{
				$this->errors[] = Array( 'query' => $iQueryString , 'bindValues' => $iBindValues , 'error' => sqlite_error_string( $errorInfo ) );

				return false;
			}

			return new SQLiteStatement( $result , $iFetchMode );
		}

		//■データ取得 //

		/**
			@brief  エラー情報を取得する。
			@return エラー情報配列。
		*/
		function getErrors() //
			{ return $this->errors; }

		//■コンストラクタ・デストラクタ //

		/**
			@brief コンストラクタ。
		*/
		function __construct( $iHost , $iPort = null , $iDBName = null , $iUser = null , $iPass = null ) //
		{
			global $sqlite_db_path;

			if( !function_exists( 'sqlite_open' ) )
				{ throw new Exception(); }

			$this->connect = sqlite_open( $sqlite_db_path . $iDBName. '.db' , 0666 , $error );

			if( !$this->connect )
				{ throw new Exception(); }
		}

		//■変数 //
		private $connect = null;    ///<接続オブジェクト。
		private $errors  = Array(); ///<エラー情報配列。
	}

	class SQLiteStatement implements Iterator //
	{
		function __construct( $iResource , $iFetchMode = null ) //
		{
			$this->resource  = $iResource;
			$this->fetchMode = $iFetchMode;
		}

		function current() //
		{
			if( !$this->current )
				{ $this->next(); }

			return $this->current;
		}

		function key() //
		{
			if( 0 > $this->index )
				{ $this->next(); }

			return $this->index;
		}

		function next() //
		{
			switch( $this->fetchMode )
			{
				case PDO::FETCH_NUM :
				{
					$this->current = sqlite_fetch_array( $this->resource , SQLITE_NUM );
					break;
				}

				case PDO::FETCH_ASSOC :
				{
					$this->current = sqlite_fetch_array( $this->resource , SQLITE_ASSOC );
					break;
				}

				default :
				{
					$this->current = sqlite_fetch_array( $this->resource );
					break;
				}
			}

			++$this->index;
		}

		function rewind() //
		{
			if( sqlite_num_rows( $this->resource ) ) //結果の行がある場合
				{ sqlite_seek( $this->resource , 0 ); }

			$this->index = -1;
		}

		function valid() //
			{ return sqlite_num_rows( $this->resource ) && sqlite_num_rows( $this->resource ) > $this->index; }

		function fetch() //
		{
			$this->next();

			return $this->current;
		}

		function closeCursor() //
			{}

		function setFetchMode( $iFetchMode ) //
			{ $this->fetchMode = $iFetchMode; }

		private $resource  = null;
		private $fetchMode = null;
		private $index     = -1;
		private $current   = null;
	}
