<?php

	//★クラス //

	/**
		@brief 設定ファイルの読み込みクラス。
	*/
	class CSVBase //
	{
		//■処理  //

		/**
			@brief     テーブルのインデックス設定ファイルを読み込む。
			@exception RuntimeException ファイルの読み込みで問題が発生した場合。
		*/
		private function readIndexDataFiles() //
		{
			foreach( $this->getIndexDataFilePaths() as $filePath ) //全てのファイルを処理
			{
				if( !is_file( $filePath ) ) //ファイルが見つからない場合
					{ continue; }

				$fp = fopen( $filePath , 'rb' );

				if( !$fp ) //ファイルの読み込みに失敗した場合
					{ throw new RuntimeException( $filePath ); }

				$results = Array();

				while( !feof( $fp ) ) //ファイルの末端まで処理
				{
					$row = fgetcsv( $fp );

					if( false === $row ) //読み込めなかった場合
						{ break; }

					if( is_array( $row ) && 1 == count( $row ) && null == $row[ 0 ] ) //空行の場合
						{ continue; }

					$indexName  = array_shift( $row );
					$presetName = array_shift( $row );
					$indexType  = array_shift( $row );

					switch( $presetName ) //プリセット設定で分岐
					{
						case 'regist' : //regist
						{
							$results[ $indexName ] = Array( 'type' => '' , 'option' => 'regist' );

							break;
						}

						default : //その他
						{
							$options = Array();

							while( is_array( $row ) && count( $row ) ) //全ての設定を処理
							{
								$name  = array_shift( $row );
								$param = array_shift( $row );

								if( $name || $param ) //名前または設定が有効な場合
									{ $options[] = $name . $param; }
							}

							if( count( $options ) ) //オプションがある場合
								{ $results[ $indexName ] = Array( 'type' => $indexType , 'option' => join( ',' , $options ) ); }

							break;
						}
					}
				}

				$this->indexes = $results;
			}
		}

		/**
			@brief     テーブル構造設定ファイルを読み込む。
			@exception RuntimeException ファイルの読み込みで問題が発生した場合。
		*/
		private function readStructDataFiles() //
		{
			$columns = Array( 'shadow_id' => Array( 'type' => 'int' , 'length' => '' ) , 'delete_key' => Array( 'type' => 'boolean' , 'length' => '' ) );

			foreach( $this->getStructDataFilePaths() as $filePath ) //全てのファイルを処理
			{
				if( !is_file( $filePath ) ) //ファイルが見つからない場合
					{ throw new RuntimeException( $filePath ); }

				$fp = fopen( $filePath , 'rb' );

				if( !$fp ) //ファイルの読み込みに失敗した場合
					{ throw new RuntimeException( $filePath ); }

				while( !feof( $fp ) ) //ファイルの末端まで処理
				{
					$row = fgetcsv( $fp );

					if( false === $row ) //読み込めなかった場合
						{ break; }

					if( is_array( $row ) && 1 == count( $row ) && null == $row[ 0 ] ) //空行の場合
						{ continue; }

					$columns[ $row[ 0 ] ] = Array( 'type' => $row[ 1 ] , 'length' => $row[ 2 ] );
				}

				fclose( $fp );
			}

			$this->columns       = $columns;
			$this->columnOptions = $columnOptions;
		}

		/**
			@brief     カラムの設定を動的に追加する。
			@param[in] $iName   カラム名。
			@param[in] $iOption カラムの設定情報。
		*/
		function addColumn( $iName , $iOption ) //
		{
			if( !count( $this->columns ) ) //カラムの情報がない場合
				{ $this->readStructDataFiles(); }

			$this->columns[ $iName ] = $iOption;
		}

		//■データ取得 //

		/**
			@brief  インデックス設定の一覧を取得する。
			@return 設定情報配列。
		*/
		function getIndexes() //
		{
			if( !count( $this->indexes ) ) //カラムの設定情報がない場合
				{ $this->readIndexDataFiles(); }

			return $this->indexes;
		}

		/**
			@brief  カラム名の一覧を取得する。
			@return カラム名の配列。
		*/
		function getColumns() //
		{
			if( !count( $this->columns ) ) //カラムの情報がない場合
				{ $this->readStructDataFiles(); }

			return $this->columns;
		}

		/**
			@brief  テーブルのインデックス設定ファイルの一覧を取得する。
			@return ファイルパス配列。
		*/
		function getIndexDataFilePaths() //
		{
			global $index_path;
			global $TDB;

			return Array( PathUtil::ModifyIndexFilePath( $TDB[ $this->tableName ] ) );
		}

		/**
			@brief  テーブル初期値設定ファイルの一覧を取得する。
			@return ファイルパス配列。
		*/
		function getInitializeDataFilePaths( $iMode = null ) //
		{
			global $tdb_path; ///<テーブル初期値設定ファイルの格納ディレクトリ。
			global $TDB;      ///<テーブル初期値設定ファイルの配列。

			$results = Array();

			if( isset( $TDB[ $this->tableName ] ) ) //定義ファイルがある場合
			{
				if( 'delete' == $iMode ) //削除データを読む場合
					{ $results[] = substr( PathUtil::ModifyTDBFilePath( $TDB[ $this->tableName ] ) , 0 , -4 ) . '_delete.csv'; }
				else //通常データを読む場合
					{ $results[] = PathUtil::ModifyTDBFilePath( $TDB[ $this->tableName ] ); }
			}

			return $results;
		}

		/**
			@brief  テーブル構造設定ファイルの一覧を取得する。
			@return ファイルパス配列。
		*/
		function getStructDataFilePaths() //
		{
			global $lst_path; ///<テーブル構造設定ファイルの格納ディレクトリ。
			global $LST;      ///<テーブル構造設定ファイルの配列。
			global $ADD_LST;  ///<追加のテーブル構造設定ファイルの配列。

			$results = Array();

			if( isset( $LST[ $this->tableName ] ) ) //ファイルがある場合
				{ $results[] = PathUtil::ModifyLSTFilePath( $LST[ $this->tableName ] ); }

			if( isset( $ADD_LST[ $this->tableName ] ) ) //追加のファイルがある場合
			{
				if( is_array( $ADD_LST[ $this->tableName ] ) ) //配列の場合
				{
					foreach( $ADD_LST[ $this->tableName ] as $fileName ) //全ての追加ファイルを処理
						{ $results[] = PathUtil::ModifyLSTFilePath( $fileName ); }
				}
				else //スカラの場合
					{ $results[] = PathUtil::ModifyLSTFilePath( $ADD_LST[ $this->tableName ] ); }
			}

			return $results;
		}

		/**
			@brief  テーブル初期値設定ファイルから一行分の情報を取得する。
			@return 初期値情報の配列。
		*/
		function readRow( $iMode = null ) //
		{
			global $SYSTEM_CHARACODE;

			$result = Array();
			$row    = null;

			while( !$row ) //ファイルの末端まで処理
			{
				if( $this->initializeDataReadHandle ) //ファイルを開いている場合
				{
					$row = fgetcsvEx( $this->initializeDataReadHandle );

					while( is_array( $row ) && 1 == count( $row ) && null == $row[ 0 ] ) //空行であった場合は繰り返し
						{ $row = fgetcsvEx( $this->initializeDataReadHandle ); }
				}

				if( !$row ) //読み込めなかった場合
				{
					if( !count( $this->initializeDataFilePaths ) ) //テーブル初期値設定ファイルが残っていない場合
						{ return null; }

					$currentFile                    = array_shift( $this->initializeDataFilePaths );

					if( 'noException' == $iMode && !is_file( $currentFile ) )
						{ return null; }

					$this->initializeDataReadHandle = fopen( $currentFile , 'rb' );

					if( !$this->initializeDataReadHandle ) //ハンドルが開けなかった場合
					{
						if( 'noException' == $iMode )
							{ return null; }

						throw new RuntimeException( $currentFile );
					}
				}
			}

			foreach( $this->getColumns() as $column => $option ) //全てのカラムを処理
				{ $result[ $column ] = mb_convert_encoding( array_shift( $row ) , $SYSTEM_CHARACODE , 'SJIS' ); }

			return $result;
		}

		//■コンストラクタ・デストラクタ //

		/**
			@brief     コンストラクタ。
			@param[in] $iTableName テーブル名。
			@param[in] $iMode      削除データのCSVを読む場合はdeleteを指定。
		*/
		function __construct( $iTableName , $iMode = null ) //
		{
			$this->tableName               = $iTableName;
			$this->initializeDataFilePaths = $this->getInitializeDataFilePaths( $iMode );
		}

		//■変数 //
		protected $tableName                   = null;    ///<テーブル名。
		protected $columns                     = Array(); ///<カラム名の一覧。
		protected $indexes                     = Array(); ///<インデックス設定の一覧。
		protected $initializeDataFileFilePaths = Array(); ///<テーブル初期値設定ファイルの読み込み用リスト。
		protected $initializeDataReadHandle    = null;    ///<テーブル初期値設定ファイルの読み込み用ハンドル。
	}
