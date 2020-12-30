<?php

	//★クラス //

	/**
		@brief DB接続クラス。
	*/
	class SQLite3DB //
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

			$result    = $this->connect->query( mb_convert_encoding( $queryString , 'utf8' , mb_internal_encoding() ) );
			$errorInfo = $this->connect->lastErrorMsg();

			if( !$result ) //エラーがある場合
			{
				$this->errors[] = Array( 'query' => $iQueryString , 'bindValues' => $iBindValues , 'error' => $errorInfo );

				return false;
			}

			return new SQLite3Statement( $result , $iFetchMode );
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

			if( !class_exists( 'SQLite3' ) )
				{ throw new Exception(); }

			$this->connect = new SQLite3( $sqlite_db_path . $iDBName. '.db' );
		}

		/**
			@brief デストラクタ。
		*/
		function __destruct() //
			{ $this->connect->close(); }

		//■変数 //
		private $connect = null;    ///<接続オブジェクト。
		private $errors  = Array(); ///<エラー情報配列。
	}

	class SQLite3Statement implements Iterator //
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
					$this->current = $this->resource->fetchArray( SQLITE3_NUM );
					break;
				}

				case PDO::FETCH_ASSOC :
				{
					$this->current = $this->resource->fetchArray( SQLITE3_ASSOC );
					break;
				}

				default :
				{
					$this->current = $this->resource->fetchArray();
					break;
				}
			}

			++$this->index;
			$fetched = true;

			return ( $this->current ? true : false );
		}

		function rewind() //
		{
			$this->resource->reset();

			$this->index   = -1;
			$this->fetched = false;
		}

		function valid() //
		{
			if( 0 > $this->row && 0 > $this->index )
			{
				while( $this->next() )
					{ $this->row = $this->index + 1; }

				$this->rewind();
			}

			return ( $this->row && $this->row > $this->index );
		}

		function fetch() //
		{
			if( !$this->fetched )
				{ $this->next(); }

			return $this->current;
		}

		function closeCursor() //
			{}

		function setFetchMode( $iFetchMode ) //
			{ $this->fetchMode = $iFetchMode; }

		private $resource  = null;
		private $fetchMode = null;
		private $index     = -1;
		private $row       = -1;
		private $fetched   = false;
		private $current   = null;
	}
