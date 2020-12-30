<?php

	class Query extends QueryBase //
	{
		//■処理 //

		static function DropIndex( $iTableName , $iIndexName ) //
			{}

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

			$queries      = Array();
			$columns      = QueryWriter::InsertColumns( $iColumns );
			$placeHolders = QueryWriter::InsertPlaceHolders( $iColumns );

			$queries[] = 'INSERT INTO ' . $iTableName;
			$queries[] = '( ' . $columns . ' )';
			$queries[] = 'VALUES (' . $placeHolders . ')';

			foreach( $iRows as $row ) //全ての行を処理
			{
				$params = Array();

				foreach( $iColumns as $column => $option ) //全てのカラムを処理
				{
					if( 'fake' == $option[ 'type' ] ) //擬似カラムの場合
						{ continue; }
					else
						{ $params[] = QueryWriter::EscapeInsertQueryValue( $row[ $column ] , $option[ 'type' ] ); }
				}

				$statement = DB::Query( implode( ' ' , $queries ) , $params );

				if( !$statement )
					{ break; }
			}

			return $statement;
		}

		/**
			@brief     テーブル名を変更する。
			@param[in] $iTableName    テーブル名。
			@param[in] $iNewTableName 新しいテーブル名。
			@return    クエリの実行結果。
		*/
		static function RenameTable( $iTableName , $iNewTableName ) //
		{
			$result = Query::CloneTable( $iTableName , $iNewTableName, Array() );

			if( !$result ) //複製に失敗した場合
				{ return false; }

			self::DropTable( $iTableName );

			return $result;
		}

		static function SetIndex( $iTableName , $iIndexList ) //
		{}

		//■データ取得 //

		static function GetStructData( $iTableName ) //
		{
			$statement = DB::Query( 'PRAGMA table_info(' . $iTableName . ')' );
			$result    = Array();

			foreach( $statement as $row ) //全ての行を処理
				{ $result[] = $row; }

			return $result;
		}

		static function GetIndexData( $iTableName ) //
			{ return null; }
	}
