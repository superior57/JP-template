<?php

	//★クラス //

	/**
		@brief クエリ実行クラス。
	*/
	class QueryBase //
	{
		//■処理 //

		/**
			@brief  トランザクションを開始する。
			@return クエリの実行結果。
		*/
		static function Begin() //
			{ return DB::Query( QueryWriter::BeginTransaction() ); }

		/**
			@brief     テーブルを複製する。
			@param[in] $iTableName    テーブル名。
			@param[in] $iNewTableName 複製先テーブル名。
			@return    クエリの実行結果。
		*/
		static function CloneTable( $iTableName , $iNewTableName , $iColumns , $iIndexes = Array() ) //
			{
				$queries = Array();
				$indexes = QueryWriter::CreateIndexes( $iColumns , $iIndexes );

				if( $indexes ) //インデックスがある場合
					{ $queries[] = '( ' . $indexes . ')'; }

				$queries[] = QueryWriter::CreateStorageEngine( $iIndexes );

				return DB::Query( 'CREATE TABLE ' . $iNewTableName . ' ' . implode( ' ' , $queries ) . ' AS SELECT * FROM ' . $iTableName );
			}

		/**
			@brief     テーブルを作成する。
			@param[in] $iTableName テーブル名。
			@param[in] $iColumns   カラム設定配列。
			@param[in] $iIndexes   インデックス設定配列。
			@return    クエリの実行結果。
		*/
		static function CreateTable( $iTableName , $iColumns , $iIndexes = Array() ) //
		{
			global $SYSTEM_CHARACODE;

			$queries = Array();
			$columns = QueryWriter::CreateColumns( $iColumns );
			$indexes = QueryWriter::CreateIndexes( $iColumns , $iIndexes );

			$queries[] = 'CREATE TABLE ' . $iTableName;

			if( $indexes ) //インデックスがある場合
				{ $queries[] = '( ' . $columns . ' , ' . $indexes . ')'; }
			else //インデックスがない場合
				{ $queries[] = '( ' . $columns . ')'; }

			$queries[] = QueryWriter::CreateStorageEngine( $iIndexes );
			$queries[] = QueryWriter::CreateCollate( $SYSTEM_CHARACODE );

			return DB::Query( implode( ' ' , $queries ) );
		}

		/**
			@brief     テーブルを削除する。
			@param[in] $iTableName テーブル名。
			@return    クエリの実行結果。
		*/
		static function DropTable( $iTableName ) //
			{ return DB::Query( 'DROP TABLE ' . $iTableName ); }

		/**
			@brief  トランザクションを終了する。
			@return クエリの実行結果。
		*/
		static function End() //
			{ return DB::Query( QueryWriter::EndTransaction() ); }

		/**
			@brief  トランザクションを巻き戻す。
			@return クエリの実行結果。
		*/
		static function Rollback() //
			{ return DB::Query( QueryWriter::RollbackTransaction() ); }

		/**
			@brief     テーブルの1行を更新する。
			@param[in] $iTableName テーブル名。
			@param[in] $iColumns   カラム設定配列。
			@param[in] $iRow       更新するレコードの内容。
			@param[in] $iKeyColumn 更新するレコードを特定するためのカラム。
			@return    クエリの実行結果。
		*/
		static function UpdateRecord( $iTableName , $iColumns , $iRow , $iKeyColumn = 'id' ) //
		{
			global $PASSWORD_MODE;

			$queries      = Array();
			$placeHolders = QueryWriter::UpdatePlaceHolders( $iColumns );
			$params       = Array();

			if( $iKeyColumn )
				{ $wheres = QueryWriter::WherePlaceHolders( $iColumns , Array( $iKeyColumn ) ); }

			$queries[] = 'UPDATE ' . $iTableName;
			$queries[] = 'SET ' . $placeHolders;

			if( $iKeyColumn )
				{ $queries[] = 'WHERE ' . $wheres; }

			$limitQuery = QueryWriter::UpdateLimit( 1 );

			if( $limitQuery )
				{ $queries[] = $limitQuery; }

			foreach( $iColumns as $column => $option ) //全てのカラムを処理
			{
				if( 'fake' == $option[ 'type' ] ) //擬似カラムの場合
					{ continue; }
				else
					{ $params[] = QueryWriter::EscapeUpdateQueryValue( $iRow[ $column ] , $option[ 'type' ] ); }
			}

			if( $iKeyColumn )
				{ $params[] = QueryWriter::EscapeUpdateQueryValue( $iRow[ $iKeyColumn ] , $iColumns[ $iKeyColumn ][ 'type' ] ); }

			return DB::Query( implode( ' ' , $queries ) , $params );
		}

		//■データ取得 //

		/**
			@brief                 DB上にバックアップテーブルがあればその作成時刻を取得する。
			@param[in] $iTableName テーブル名。
			@return                バックアップテーブルの作成時刻。
		*/
		static function GetBackupTime( $iTableName ) //
		{
			$tableNames = Query::ShowTables();

			foreach( $tableNames as $name ) //全てのテーブル名を処理
			{
				if( !preg_match( '/^' . $iTableName . '_backup_(\d+)$/' , $name , $matches ) ) //バックアップテーブルではない場合
					{ continue; }

				return $matches[ 1 ];
			}

			foreach( $tableNames as $name ) //全てのテーブル名を処理
			{
				if( !preg_match( '/^' . $iTableName . '_backup(\d+)$/' , $name , $matches ) ) //バックアップテーブル(旧システム名)ではない場合
					{ continue; }

				return $matches[ 1 ];
			}

			return false;
		}

		/**
			@brief     shadow_idの最大値を取得する。
			@param[in] $iTableName テーブル名。
			@return    shadow_idの最大値。
		*/
		static function GetMaxShadowID( $iTableName ) //
		{
			$statement = DB::Query( 'SELECT max( abs( shadow_id ) ) FROM ' .  $iTableName );

			if( !$statement ) //行がない場合
				{ return 0; }

			$row = $statement->fetch();

			return array_shift( $row );
		}

		/**
			@brief     テーブルの行数を取得する。
			@param[in] $iTableName テーブル名。
			@return    テーブルの行数。
		*/
		static function GetRowCount( $iTableName ) //
		{
			$statement = DB::Query( 'SELECT COUNT (*) AS cnt FROM ' . $iTableName );
			$row       = $statement->fetch();

			return array_shift( $row );
		}

		/**
			@brief     SELECT文のス実行結果を取得する。
			@param[in] $iTableName テーブル名。
			@param[in] $iColumns   カラム設定配列。
			@param[in] $iWheres    検索条件。
			@param[in] $iLimit     最大取得件数。
			@return    クエリの実行結果。
		*/
		static function GetSelectStatement( $iTableName , $iColumns , $iWheres = null , $iLimit = null ) //
		{
			$queries = Array();
			$columns = QueryWriter::SelectColumns( $iColumns );
			$params  = Array();

			$queries[] = 'SELECT ' . $columns;
			$queries[] = 'FROM ' . $iTableName;

			if( $iWheres ) //検索条件の指定がある場合
			{
				$queries[] = 'WHERE ' . QueryWriter::WherePlaceHolders( $iColumns , array_keys( $iWheres ) );

				foreach( $iWheres as $column => $value )
					{ $params[] = QueryWriter::EscapeWhereQueryValue( $value , $iColumns[ $column ][ 'type' ] ); }
			}

			if( $iLimit ) //抽出数の指定がある場合
				{ $queries[] = 'LIMIT ' . $iLimit; }

			if( $params ) //パラメータがある場合
				{ return DB::Query( implode( ' ' , $queries ) , $params ); }
			else //パラメータがない場合
				{ return DB::Query( implode( ' ' , $queries ) ); }
		}

		/**
			@brief  テーブルの一覧を取得する。
			@return テーブル名配列。
		*/
		static function ShowTables() //
		{
			$statement = DB::Query( QueryWriter::ShowTables() );
			$results   = Array();

			foreach( $statement as $row ) //全ての行を処理
				{ $results[] = array_shift( $row ); }

			return $results;
		}
	}
