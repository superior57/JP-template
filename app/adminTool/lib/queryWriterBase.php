<?php

	//★クラス //

	/**
		@brief クエリ構築クラス。
	*/
	class QueryWriterBase //
	{
		//■データ取得 //

		/**
			@brief     CREATE文のカラム指定クエリを構築する。
			@param[in] $iColumns カラム設定配列。
			@return    クエリ文字列。
		*/
		static function CreateColumns( $iColumns ) //
		{
			$columns = Array();

			foreach( $iColumns as $column => $option ) //全てのカラムを処理
			{
				if( 'fake' == $option[ 'type' ] ) //擬似カラムの場合
					{ continue; }
				else if( $option[ 'length' ] ) //カラムサイズの指定がある場合
					{ $columns[] = $column . ' ' . QueryWriter::RealTypeName( $option[ 'type' ] ) . '(' . $option[ 'length' ] . ')'; }
				else //カラムサイズの指定がない場合
					{ $columns[] = $column . ' ' . QueryWriter::RealTypeName( $option[ 'type' ] ); }
			}

			return implode( ' , ' , $columns );
		}

		/**
			@brief     INSERT文のカラム指定クエリを構築する。
			@param[in] $iColumns カラム設定配列。
			@return    クエリ文字列。
		*/
		static function InsertColumns( $iColumns ) //
		{
			$columns = Array();

			foreach( $iColumns as $column => $option ) //全てのカラムを処理
			{
				if( 'fake' == $option[ 'type' ] ) //擬似カラムの場合
					{ continue; }
				else //その他のカラムの場合
					{ $columns[] = $column; }
			}

			return implode( ' , ' , $columns );
		}

		/**
			@brief     INSERT文のプレースホルダ指定クエリを構築する。
			@param[in] $iColumns カラム設定配列。
			@return    クエリ文字列。
		*/
		function InsertPlaceHolders( $iColumns ) //
		{
			$placeHolders = Array();

			foreach( $iColumns as $column => $option ) //全てのカラムを処理
			{
				if( 'fake' == $option[ 'type' ] ) //擬似カラムの場合
					{ continue; }
				else if( 'password' == $option[ 'type' ] ) //パスワードカラムの場合
					{ $placeHolders[] = QueryWriter::EncryptPlaceHolder(); }
				else //通常カラムの場合
					{ $placeHolders[] = '?'; }
			}

			return implode( ' , ' , $placeHolders );
		}

		/**
			@brief     カラムの型名を変換する。
			@param[in] $iTypename 設定上の型名。
			@return    実際の型名。
		*/
		static function RealTypeName( $iTypeName ) //
			{ return QueryWriter::RealTypeName( $iTypeName ); }

		/**
			@brief  トランザクション巻き戻しクエリを構築する。
			@return クエリ文字列。
		*/
		static function RollbackTransaction() //
			{ return QueryWriter::RollbackTransaction(); }

		/**
			@brief     SELECT文のカラム指定クエリを構築する。
			@param[in] $iColumns カラム設定配列。
			@return    クエリ文字列。
		*/
		static function SelectColumns( $iColumns ) //
		{
			$columns = Array( '*' );

			foreach( $iColumns as $column => $option ) //全てのカラムを処理
			{
				if( 'password' == $option[ 'type' ] ) //パスワードカラムの場合
				{
					$decrypt = QueryWriter::DecryptColumn( $column );

					if( $column != $decrypt ) //独自の復号書式がある場合
						{ $columns[] = QueryWriter::DecryptColumn( $column ) . ' as ' . $column; }
				}
			}

			return implode( ' , ' , $columns );
		}

		/**
			@brief  テーブル名一覧取得クエリを構築する。
			@return クエリ文字列。
		*/
		static function ShowTables() //
			{ return QueryWriter::ShowTables(); }

		/**
			@brief     UPDATE文のプレースホルダ指定クエリを構築する。
			@param[in] $iColumns カラム設定配列。
			@return    クエリ文字列。
		*/
		static function UpdatePlaceHolders( $iColumns ) //
		{
			$pairs = Array();

			foreach( $iColumns as $column => $option ) //全てのカラムを処理
			{
				if( 'fake' == $option[ 'type' ] ) //擬似カラムの場合
					{ continue; }
				else if( 'password' == $option[ 'type' ] ) //パスワードカラムの場合
					{ $pairs[] = $column . ' = ' . QueryWriter::EncryptPlaceHolder(); }
				else //通常カラムの場合
					{ $pairs[] = $column . ' = ?'; }
			}

			return implode( ' , ' , $pairs );
		}

		/**
			@brief     WHERE句のプレースホルダ指定クエリを構築する。
			@param[in] $iColumns       カラム設定配列。
			@param[in] $iTargetColumns 検索するカラム一覧。
			@return    クエリ文字列。
		*/
		static function WherePlaceHolders( $iColumns , $iTargetColumns ) //
		{
			foreach( $iTargetColumns as $column ) //全てのカラムを処理
			{
				if( 'password' == $iColumns[ $column ][ 'type' ] ) //パスワードカラムの場合
					{ $pairs[] = QueryWriter::DecryptColumns( $column ) . ' = ?'; }
				else //通常カラムの場合
					{ $pairs[] = $column . ' = ?'; }
			}

			return implode( ' , ' , $pairs );
		}
	}
