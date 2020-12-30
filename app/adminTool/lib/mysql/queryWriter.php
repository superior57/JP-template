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
			{ return 'START TRANSACTION'; }

		/**
			@brief     CREATE文のインデックス指定クエリを構築する。
			@param[in] $iColumns カラム設定配列。
			@param[in] $iIndexes インデックス設定配列。
			@return    クエリ文字列。
		*/
		static function CreateIndexes( $iColumns , $iIndexes ) //
		{
			$noIndexType        = Array( 'string', 'image' , 'password' );
			$requireIndexColumn = Array( 'shadow_id','id' );
			$indexes            = Array();

			foreach( $iColumns as $column => $option ) //全てのカラムを処理
			{
				if( !in_array( $option[ 'type' ] , $noIndexType ) ) //インデックス対象の型の場合
				{
					if( in_array( $column , $requireIndexColumn ) ) //自動インデックスの対象の場合
						{ $indexes[] = 'UNIQUE INDEX system_' . $column . ' ( ' . $column . ' )'; }
				}
			}

			foreach( $iIndexes as $name => $option ) //全てのインデックスを処理
			{
				if( 'fulltext' == strtolower( $option[ 'type' ] ) ) //FULLTEXTの場合
					{ $indexes[] = 'FULLTEXT INDEX ' . $name . ' ( ' . $option[ 'option' ] . ' )'; }
				else if( 'unique' == strtolower( $option[ 'type' ] ) ) //ユニークキーの場合
					{ $indexes[] = 'UNIQUE INDEX ' . $name . ' ( ' . $option[ 'option' ] . ' )'; }
				else //それ以外の場合
					{ $indexes[] = 'INDEX ' . $name . ' ( ' . $option[ 'option' ] . ' )'; }
			}

			return implode( ' , ' , $indexes );
		}

		/**
			@brief     CREATE文の文字セット指定クエリを構築する。
			@param[in] $iCharaSet 文字セット。
			@return    クエリ文字列。
		*/
		static function CreateCollate( $iCharaSet ) //
		{
			$charaset = '';
			$collate  = '';

			switch( strtolower( $iCharaSet ) ) //文字セットの指定で分岐
			{
				case 'sjis'      :
				case 'shift_jis' :
				{
					$charaset = 'SJIS';
					$collate  = 'sjis_japanese_ci';

					break;
				}

				case 'utf8'  :
				case 'utf-8' :
				{
					$charaset = 'UTF8';
					$collate  = 'utf8_general_ci';

					break;
				}
			}

			if( $charaset && $collate ) //設定候補がある場合
				{ return 'CHARACTER SET ' . $charaset . ' COLLATE ' . $collate; }
			else //設定候補がない場合
				{ return ''; }
		}

		/**
			@brief     CREATE文のストレージエンジン指定クエリを構築する。
			@param[in] $iIndexes インデックス設定配列。
			@return    クエリ文字列。
		*/
		static function CreateStorageEngine( $iIndexes ) //
		{
            global $MYSQL_DEFAULT_TABLE_ENGINE;

			foreach( $iIndexes as $name => $option ) //全てのインデックスを処理
			{
				if( 'fulltext' == strtolower( $option[ 'type' ] ) ) //FULLTEXTの場合
					{ return 'ENGINE = MyISAM'; }
			}

            if( empty($MYSQL_DEFAULT_TABLE_ENGINE)){
			return '';
            }else{ return "ENGINE = $MYSQL_DEFAULT_TABLE_ENGINE"; }
		}

		/**
			@brief     カラムを復号するクエリを構築する。
			@param[in] $iColumn カラム名。
			@return    クエリ文字列。
		*/
		static function DecryptColumn( $iColumn ) //
		{
			global $CONFIG_SQL_PASSWORD_KEY;

			return 'AES_DECRYPT( ' . $iColumn . ' , \'' . $CONFIG_SQL_PASSWORD_KEY . '\' )';
		}

		/**
			@brief  プレースホルダを暗号化するクエリを構築する。
			@return クエリ文字列。
		*/
		static function EncryptPlaceHolder() //
		{
			global $CONFIG_SQL_PASSWORD_KEY;

			return 'AES_ENCRYPT( ? , \'' . $CONFIG_SQL_PASSWORD_KEY . '\' )';
		}

		/**
			@brief  トランザクション終了クエリを構築する。
			@return クエリ文字列。
		*/
		static function EndTransaction() //
			{ return 'COMMIT'; }

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

					case 'int'       :
					case 'double'    :
					case 'timestamp' :
						{ return ( !$iValue ? 0 : $iValue ); }

					case 'boolean' :
						return ( !$iValue || 'false' == strtolower( $iValue ) ? 0 : 1 );

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
					{ return '\'' . EncodePassword( $iValue , $PASSWORD_MODE ) . '\''; }

				case 'boolean' :
					return ( !$iValue || 'false' == strtolower( $iValue ) ? 'FALSE' : 'TRUE' );

				default :
					{ return '\'' . str_replace( Array( '\'' , '\\' ) , Array( '\'\'' , '\\\\' ) , $iValue ) . '\''; }
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
					{ return '\'' . str_replace( '\'' , '\\\'' , $iValue ) . '\''; }

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

				case 'string' : //文字列
				case 'image'  : //画像
					{ return 'text'; }

				case 'password' : //パスワード
					{ return 'blob'; }

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
			{ return 'SHOW TABLES'; }

		/**
			@brief  UPDATE時の行数制限クエリを構築する。
			@return クエリ文字列。
		*/
		static function UpdateLimit( $iLimitNum ) //
			{ return 'LIMIT ' . $iLimitNum; }
	}
