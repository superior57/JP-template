<?php

	//★クラス //

	/**
		@brief クエリ構築クラス。
	*/
	class QueryWriter extends QueryWriterBase //
	{
		//■データ取得 //

		/**
			@brief  トランザクション開始クエリを構築する。
			@return クエリ文字列。
		*/
		static function BeginTransaction() //
			{ return 'BEGIN'; }

		/**
			@brief     CREATE文のインデックス指定クエリを構築する。
			@param[in] $iColumns カラム設定配列。
			@param[in] $iIndexes インデックス設定配列。
			@return    クエリ文字列。
		*/
		static function CreateIndexes( $iColumns , $iIndexes ) //
			{ return ''; }

		/**
			@brief     CREATE文の文字コード指定クエリを構築する。
			@param[in] $iColumns 文字セット。
			@return    クエリ文字列。
		*/
		static function CreateCollate( $iCharSet ) //
			{ return ''; }

		/**
			@brief     CREATE文のストレージエンジン指定クエリを構築する。
			@param[in] $iIndexes インデックス設定配列。
			@return    クエリ文字列。
		*/
		static function CreateStorageEngine( $iIndexes ) //
			{ return ''; }

		/**
			@brief     カラムを復号するクエリを構築する。
			@param[in] $iColumn カラム名。
			@return    クエリ文字列。
		*/
		static function DecryptColumn( $iColumn ) //
			{ return $iColumn; }

		/**
			@brief  プレースホルダを暗号化するクエリを構築する。
			@return クエリ文字列。
		*/
		static function EncryptPlaceHolder() //
			{ return '?'; }

		/**
			@brief  トランザクション終了クエリを構築する。
			@return クエリ文字列。
		*/
		static function EndTransaction() //
			{ return 'END'; }

		/**
			@brief クエリで使用する値を適切にエスケープまたは変換する。
		*/
		static function EscapeInsertQueryValue( $iValue , $iColumnType ) //
			{ return QueryWriter::EscapeUpdateQueryValue( $iValue , $iColumnType ); }

		/**
			@brief クエリで使用する値を適切にエスケープまたは変換する。
		*/
		static function EscapeUpdateQueryValue( $iValue , $iColumnType ) //
		{
			global $CONFIG_SQL_PASSWORD_KEY;
			global $PASSWORD_MODE;
			global $USE_PDO;

			if( $USE_PDO )
			{
				switch( $iColumnType )
				{
					case 'password' :
						{ return EncodePassword( $iValue , $PASSWORD_MODE ); }

					default :
						{ return $iValue; }
				}
			}

			switch( $iColumnType )
			{
				case 'int'       :
				case 'double'    :
				case 'timestamp' :
					{ return ( !$iValue ? 0 : $iValue ); }

				case 'password' :
					{ return '\'' . str_replace( '\'' , '\'\'' , EncodePassword( $iValue , $PASSWORD_MODE ) ) . '\''; }

				case 'boolean' :
					return ( !$iValue || 'false' == strtolower( $iValue ) ? '\'\'' : '1' );

				default :
					{ return '\'' . str_replace( '\'' , '\'\'' , $iValue ) . '\''; }
			}
		}

		/**
			@brief クエリで使用する値を適切にエスケープまたは変換する。
		*/
		static function EscapeWhereQueryValue( $iValue , $iColumnType ) //
		{
			global $USE_PDO;

			if( $USE_PDO )
				{ return $iValue; }

			switch( $iColumnType )
			{
				case 'password' :
					{ return '\'' . str_replace( '\'' , '\'\'' , $iValue ) . '\''; }

				default :
					{ return QueryWriter::EscapeUpdateQueryValue( $iValue , $iColumnType ); }
			}
		}

		/**
			@brief     カラムの型名を変換する。
			@param[in] $iTypename 設定上の型名。
			@return    実際の型名。
		*/
		static function RealTypeName( $iTypeName ) //
		{
			switch( $iTypeName ) //型名で分岐
			{
				case 'int'       : //整数
				case 'timestamp' : //タイムスタンプ
					{ return 'integer'; }

				case 'double' : //実数
					{ return 'real'; }

				case 'string'   : //文字列
				case 'image'    : //画像
				case 'password' : //パスワード
					{ return 'text'; }

				default : //その他
					{ return $iTypeName; }
			}
		}

		/**
			@brief  トランザクション巻き戻しクエリを構築する。
			@return クエリ文字列。
		*/
		static function RollbackTransaction() //
			{ return 'ROLLBACK'; }

		/**
			@brief  テーブル名一覧取得クエリを構築する。
			@return クエリ文字列。
		*/
		static function ShowTables() //
			{ return 'SELECT name FROM sqlite_master WHERE type="table" UNION ALL SELECT name FROM sqlite_temp_master WHERE type = "table" ORDER BY name'; }

		/**
			@brief  UPDATE時の行数制限クエリを構築する。
			@return クエリ文字列。
		*/
		static function UpdateLimit( $iLimitNum ) //
			{ return ''; }
	}
