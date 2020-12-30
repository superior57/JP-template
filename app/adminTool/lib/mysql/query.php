<?php

	class Query extends QueryBase //
	{
		//■処理 //

		static function DropIndex( $iTableName , $iIndexName ) //
			{ return DB::Query( 'ALTER TABLE ? DROP INDEX ?' , Array( $iTableName , $iIndexName ) ); }

		/**
			@brief     テーブルに行を挿入する。
			@param[in] $iTableName テーブル名。
			@param[in] $iColumns   カラム設定配列。
			@param[in] $iRows      挿入するレコード配列。
			@return    クエリの実行結果。
		*/
		static function InsertRecord( $iTableName , $iColumns , $iRows ) //
		{
			global $PASSWORD_MODE;

			$queries         = Array();
			$columns         = QueryWriter::InsertColumns( $iColumns );
			$placeHolders    = QueryWriter::InsertPlaceHolders( $iColumns );
			$placeHolderSets = Array();
			$params          = Array();

			$queries[] = 'INSERT INTO ' . $iTableName;
			$queries[] = '( ' . $columns . ' )';

			foreach( $iRows as $row ) //全ての行を処理
			{
				$placeHolderSets[] = '( ' . $placeHolders . ' )';

				foreach( $iColumns as $column => $option ) //全てのカラムを処理
				{
					if( 'fake' == $option[ 'type' ] ) //擬似カラムの場合
						{ continue; }
					else
						{ $params[] = QueryWriter::EscapeInsertQueryValue( $row[ $column ] , $option[ 'type' ] ); }
				}
			}

			$queries[] = 'VALUES ' . implode( ' , ' , $placeHolderSets );

			return DB::Query( implode( ' ' , $queries ) , $params );
		}

		/**
			@brief     テーブル名を変更する。
			@param[in] $iTableName    テーブル名。
			@param[in] $iNewTableName 新しいテーブル名。
			@return    クエリの実行結果。
		*/
		static function RenameTable( $iTableName , $iNewTableName ) //
			{ return DB::Query( 'ALTER TABLE ' . $iTableName . ' RENAME TO ' . $iNewTableName ); }

		static function SetIndex( $iTableName , $iIndexList ) //
		{
			foreach( $iIndexList as $name => $option )
			{
				if( 'UNIQUE' == $option[ 'type' ] )
					{ DB::Query( 'CREATE UNIQUE INDEX ? ON ?(?)' , Array( $name , $iTableName , $option[ 'option' ] ) ); }
				else
					{ DB::Query( 'ALTER TABLE ? ADD INDEX ?(?)' , Array( $iTableName , $name , $option[ 'option' ] ) ); }
			}
		}

		//■データ取得 //

		static function GetStructData( $iTableName ) //
		{
			$statement = DB::Query( 'SHOW COLUMNS FROM ' . $iTableName );
			$result    = Array();

			$statement->setFetchMode( PDO::FETCH_ASSOC );

			foreach( $statement as $row ) //全ての行を処理
				{ $result[] = $row; }

			return $result;
		}

		static function GetIndexData( $iTableName ) //
		{
			$statement = DB::Query( 'SHOW INDEX FROM ' . $iTableName );
			$result    = Array();

			$statement->setFetchMode( PDO::FETCH_ASSOC );

			foreach( $statement as $row ) //全ての行を処理
			{
				$row[ 'Table' ]       = null;
				$row[ 'Cardinality' ] = null;
				$result[]             = $row;
			}

			return $result;
		}

	}
