<?php

	//★クラス //

	/**
		@brief DB接続クラス。
	*/
	class MySQLDB //
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

			$result    = mysqli_query($this->connect, $queryString );
			$errorInfo = mysqli_error($this->connect);

			if( $errorInfo ) //エラーがある場合
			{
				$this->errors[] = Array( 'query' => $iQueryString , 'bindValues' => $iBindValues , 'error' => $errorInfo );

				return false;
			}

			return new MySQLStatement( $result , $iFetchMode );
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
			if( !function_exists( 'mysqli_connect' ) )
				{ throw new Exception(); }

			if( $iPort )
				{ $this->connect = mysqli_connect( $iHost . ':' . $iPort , $iUser , $iPass ); }
			else
				{ $this->connect = mysqli_connect( $iHost , $iUser , $iPass ); }

			if( !$this->connect )
				{ throw new Exception(); }

			if( !mysqli_select_db( $this->connect , $iDBName ) )
				{ throw new Exception(); }

			mysqli_query( $this->connect ,'set names utf8' );
		}

		//■変数 //
		private $connect = null;    ///<接続オブジェクト。
		private $errors  = Array(); ///<エラー情報配列。
	}

	class MySQLStatement implements Iterator //
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
					$this->current = mysqli_fetch_row( $this->resource );
					break;
				}

				case PDO::FETCH_ASSOC :
				{
					$this->current = mysqli_fetch_assoc( $this->resource );
					break;
				}

				default :
				{
					$this->current = mysqli_fetch_array( $this->resource );
					break;
				}
			}

			++$this->index;
		}

		function rewind() //
		{
			if( mysqli_num_rows( $this->resource ) ) //結果の行がある場合
				{ mysqli_data_seek( $this->resource , 0 ); }

			$this->index = -1;
		}

		function valid() //
			{ return mysqli_num_rows( $this->resource ) && mysqli_num_rows( $this->resource ) > $this->index; }

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
