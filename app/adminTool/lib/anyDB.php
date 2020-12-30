<?php

	//★クラス //

	/**
		@brief DB接続クラス。
	*/
	class AnyDB //
	{
		//■処理 //

		/**
			@brief エラー情報を初期化する。
		*/
		function clearErrors() //
			{ $this->errors = Array(); }

		/**
			@brief     クエリを実行する。
			@param[in] $iQueryString クエリ文字列。
			@param[in] $iBindValues  バインドする値の配列。
			@param[in] $iFetchMode   フェッチモードの指定。
			@retval    ステートメントオブジェクト クエリが成功した場合。
			@retval    false                      クエリが失敗した場合。
		*/
		function query( $iQueryString , $iBindValues = null , $iFetchMode = null ) //
		{
			if( is_array( $iBindValues ) && 0 < count( $iBindValues ) ) //バインド値がある場合
			{
				$statement = $this->pdo->prepare( $iQueryString );
				$result    = $statement->execute( $iBindValues );
				$errorInfo = $statement->errorInfo();

				if( '00000' != $errorInfo[ 0 ] ) //エラーがある場合
				{
					$this->errors[] = Array( 'query' => $iQueryString , 'bindValues' => $iBindValues , 'error' => $errorInfo[ 2 ] );
					return false;
				}
			}
			else //バインド値がない場合
			{
				$statement = $this->pdo->query( $iQueryString );
				$errorInfo = $this->pdo->errorInfo();

				if( '00000' != $errorInfo[ 0 ] ) //エラーがある場合
				{
					$this->errors[] = Array( 'query' => $iQueryString , 'bindValues' => $iBindValues , 'error' => $errorInfo[ 2 ] );

					return false;
				}
			}

			if( $iFetchMode ) //フェッチモードの指定がある場合
				{ $statement->setFetchMode( $iFetchMode ); }

			return $statement;
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
			@brief     コンストラクタ。
			@param[in] $iDSN PDOのコンストラクタの引数。
		*/
		function __construct( $iDSN , $iUser = null , $iPass = null ) //
		{
			$this->pdo = new PDO( $iDSN , $iUser , $iPass );

			$this->pdo->setAttribute( PDO::ATTR_DEFAULT_FETCH_MODE , PDO::FETCH_ASSOC );
			$this->pdo->setAttribute( PDO::MYSQL_ATTR_USE_BUFFERED_QUERY , true);
		}

		//■変数 //
		private $pdo    = null;    ///<PDOオブジェクト。
		private $errors = Array(); ///<エラー情報配列。
	}
