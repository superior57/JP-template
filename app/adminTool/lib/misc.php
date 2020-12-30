<?php

	//★関数 //

	/**
		@brief     型に合わせて変換する。
		@param[in] $iColumns カラム設定配列。
		@param[in] $iRow     レコードデータ。
		@return    変換後のレコードデータ。
	*/
	function FixType( $iColumns , $iRow ) //
	{
		$results = Array();

		foreach( $iColumns as $column => $option ) //全てのカラム設定を処理
		{
			switch( $option[ 'type' ] ) //型で分岐
			{
				case 'int' : //整数
				{
					$results[ $column ] = ( int )( $iRow[ $column ] );
					break;
				}

				case 'double' : //実数
				{
					$results[ $column ] = ( double )( $iRow[ $column ] );
					break;
				}

				case 'boolean' : //真偽値
				{
					$results[ $column ] = ( bool )( $iRow[ $column ] );
					break;
				}

				default : //その他
				{
					$results[ $column ] = $iRow[ $column ];
					break;
				}
			}
		}

		return $results;
	}

	/**
		@brief     このサーバーのモジュールのバージョンを取得する。
		@param[in] $iModuleName モジュール名。
	*/
	function GetModuleVersion( $iModuleName ) //
	{
		include_once 'custom/version/' . $iModuleName . '.php';

		$functionName = 'GetVersion_' . $iModuleName;

		return $functionName();
	}

	/**
		@brief  管理ツールのパスワードを取得する。
		@return パスワードのMD5値。
	*/
	function GetToolPassword() //
	{
		global $TOOL_PASSWORD_TABLE;

		$main   = new TableName( $TOOL_PASSWORD_TABLE );
		$column = Array( 'password' => Array( 'type' => 'string' ) );

		if( !in_array( $main->real() , Query::ShowTables() ) ) //テーブルが存在しない場合
		{
			$row = Array( 'password' => md5( 'admin' ) );

			Query::CreateTable( $main->real() , $column );
			Query::InsertRecord( $main->real() , $column , Array( $row ) );
		}

		$statement = Query::GetSelectStatement( $main->real() , $column , null , 1 );
		$row       = $statement->fetch();

		return $row[ 'password' ];
	}

	/**
		@brief  パターンに一致するエントリ一覧を取得する。
		@return エントリ配列。
	*/
	function GlobEntries( $iEntries ) //
	{
		$results = Array();

		foreach( $iEntries as $entry ) //全ての要素を処理
		{
			$globResult = glob( $entry );

			if( false === $globResult ) //globに失敗した場合
				{ continue; }

			foreach( $globResult as $getEntry ) //全ての結果を処理
				{ $results[] = $getEntry; }
		}

		return $results;
	}

	/**
		@brief     文字列のhtmlエンティティをエスケープする。
		@param[in] $iString エスケープする文字列。
		@return    エスケープされた文字列。
	*/
	function Text( $iString ) //
	{
		if( is_array( $iString ) )
		{
			foreach( $iString as &$element )
				{ $element = Text( $element ); }

			return $iString;
		}
		else
			{ return htmlspecialchars( $iString , ENT_QUOTES ); }
	}

	/**
		@brief  データベースへの接続を取得する。
		@return DB接続オブジェクト。
	*/
	function CreateDBConnect( $iMaster , $iHost , $iPort , $iDBName , $iUser , $iPass ) //
	{
		global $USE_PDO;
		global $USE_SQLITE_VERSION;

		switch( $iMaster ) //DBMSの種類で分岐
		{
			case 'MySQLDatabase' : //MySQL
			{
				if( !$USE_PDO ) //PDOを使用しない場合
					{ return new MySQLDB( $iHost , $iPort , $iDBName , $iUser , $iPass ); }

				if( $iPort ) //ポートの指定がある場合
					{ $db = new AnyDB( 'mysql:host=' . $iHost . ';port=' . $iPort . ';dbname=' . $iDBName . ';charset=UTF8' , $iUser , $iPass ); }
				else //ポートの指定がない場合
					{ $db = new AnyDB( 'mysql:host=' . $iHost . ';dbname=' . $iDBName . ';charset=UTF8' , $iUser , $iPass ); }

				$db->query( 'set names utf8' );

				return $db;
			}

			case 'SQLiteDatabase' : //SQLite
			{
				if( 'auto' == $USE_SQLITE_VERSION ) //バージョンを自動判別する場合
					{ $version = ( 50400 <= PHP_VERSION_ID ? 3 : 2 ); }
				else //バージョンを直接指定している場合
					{ $version = $USE_SQLITE_VERSION; }

				if( !$USE_PDO ) //PDOを使用しない場合
				{
					if( 3 == $version ) //SQLite3が要求されている場合
						{ return new SQLite3DB( $iHost , $iPort , $iDBName , $iUser , $iPass ); }
					else //SQLite2が要求されている場合
						{ return new SQLiteDB( $iHost , $iPort , $iDBName , $iUser , $iPass ); }
				}

				global $sqlite_db_path;

				if( 3 == $version ) //SQLite3が要求されている場合
					{ $db = new AnyDB( 'sqlite:' . $sqlite_db_path . $iDBName . '.db' ); }
				else //SQLite2が要求されている場合
					{ $db = new AnyDB( 'sqlite2:' . $sqlite_db_path . $iDBName . '.db' ); }

				return $db;
			}

			default : //その他
				{ throw new LogicException(); }
		}
	}

	/**
		@brief     テーブルの構成情報を更新する。
		@param[in] $iTableName テーブル名。
		@retval    true  処理に成功した場合。
		@retval    false 処理に失敗した場合。
	*/
	function UpdateSystemTable( $iTableName ) //
	{
		$main   = new TableName( 'system_tables' );
		$target = new TableName( $iTableName );
		$delete = new TableName( $iTableName . '_delete' );
		$csv    = new CSV( 'system_tables' );

		if( !in_array( $main->real() , Query::ShowTables() ) ) //システムテーブルが存在しない場合
		{
			if( !Query::CreateTable( $main->real() , $csv->getColumns() ) ) //テーブルの作成に失敗した場合
				{ return false; }
		}

		$maxID = Query::GetMaxShadowID( $target->real() );
		$maxDeleteID = Query::GetMaxShadowID( $delete->real() );
		$row         = Array( 'table_name' => $iTableName , 'id_count' => ( $maxID > $maxDeleteID ? $maxID : $maxDeleteID ) );

		$statement = Query::GetSelectStatement( $main->real() , $csv->getColumns() , Array( 'table_name' => $iTableName ) , 1 );
		$existsRow = $statement->fetch();

		$statement->closeCursor();

		if( !$existsRow ) //レコードが存在しない場合
		{
			$row[ 'shadow_id' ] = Query::GetMaxShadowID( $main->real() ) + 1;

			return Query::InsertRecord( $main->real() , $csv->getColumns() , Array( $row ) );
		}
		else //レコードが存在する場合
		{
			$row[ 'shadow_id' ] = $existsRow[ 'shadow_id' ];

			return Query::UpdateRecord( $main->real() , $csv->getColumns() , $row , 'table_name' );
		}
	}

	/**
		@brief     管理ツールのパスワードを変更する。
		@param[in] $iNewPassword 新しいパスワード。
		@return    クエリの実行結果。
	*/
	function UpdateToolPassword( $iNewPassword ) //
	{
		global $TOOL_PASSWORD_TABLE;

		$main   = new TableName( $TOOL_PASSWORD_TABLE );
		$column = Array( 'password' => Array( 'type' => 'string' ) );
		$row    = Array( 'password' => md5( $iNewPassword ) );

		if( !in_array( $main->real() , Query::ShowTables() ) ) //テーブルが存在しない場合
		{
			Query::CreateTable( $main->real() , $column );
			$result = Query::InsertRecord( $main->real() , $column , Array( $row ) );
		}
		else
			{ $result = Query::UpdateRecord( $main->real() , $column , $row , null ); }

		if( false === $result )
			{ return false; }

		return true;
	}

	function fgetcsvEx( &$iHandle , $iLength = null , $iDelimiter = ',' , $iEnclosure = '"' ) //
	{
		$d = preg_quote( $iDelimiter );
		$e = preg_quote( $iEnclosure );

		$line = '';
		$eof  = false;

		while ( !$eof )
		{
			$line    .= ( empty( $iLength ) ? fgets( $iHandle ) : fgets( $iHandle , $iLength ) );
			$itemcnt  = preg_match_all('/' . $e . '/' , $line , $dummy );

			if( $itemcnt % 2 == 0 )
				{ $eof = true; }
		}

		if( empty( $line ) )
			{ return false; }

		$csvLine    = preg_replace( '/(?:\r\n|[\r\n])?$/' , $d , trim( $line ) );
		$csvPattern = '/(' . $e . '[^' . $e . ']*(?:' . $e . $e . '[^' . $e . ']*)*' . $e . '|[^' . $d . ']*)' . $d . '/';

		preg_match_all( $csvPattern , $csvLine , $csvMatches );

		$csvData = $csvMatches[ 1 ];

		for( $index = 0 ; count( $csvData ) > $index ; ++$index )
		{
			$csvData[ $index ] = preg_replace('/^' . $e . '(.*)' . $e . '$/s' , '$1' , $csvData[ $index ] );
			$csvData[ $index ] = str_replace( $e . $e , $e , $csvData[ $index ] );
		}

		return $csvData;
	}

	function EncodePassword( $iPassword , $iEncode )
	{
		$encode = GetPasswordEncode( $iPassword );

		if( $iEncode == $encode )
			{ return $iPassword; }
		else if( 'SHA' == $iEncode )
			{ return 'SHA:' . sha1( DecodePassword( $iPassword ) ); }
		else if( 'SHA' != $encode )
			{ return 'AES:' . DecodePassword( $iPassword ); }
		else
			{ return $iPassword; }
	}

	function DecodePassword( $iPassword )
		{ return preg_replace( '/^\w+:/' , '' , $iPassword ); }

	function GetPasswordEncode( $iPassword )
	{
		preg_match( '/^(\w+):/' , $iPassword , $matches );

		return $matches[ 1 ];
	}
