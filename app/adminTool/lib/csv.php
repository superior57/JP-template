<?php

	//★クラス //

	class CSV extends CSVBase //
	{
		//■データ取得 //

		/**
			@brief  テーブル初期値設定ファイルの一覧を取得する。
			@return ファイルパス配列。
		*/
		function getInitializeDataFilePaths( $iMode = null ) //
		{
			global $tdb_path;          ///<テーブル初期値設定ファイルの格納ディレクトリ。
			global $template_csv_dirs; ///<テンプレート定義ファイルの格納ディレクトリ。
			global $MODULES;

			$results = parent::getInitializeDataFilePaths( $iMode );

			switch( $this->tableName ) //テーブル名で分岐
			{
				case 'template' : //テンプレート
				{
					foreach( $template_csv_dirs as $type => $path ) //全てのディレクトリを処理
					{
						$dir = opendir( $path );

						while( $entry = readdir( $dir ) ) //全てのエントリを処理
						{
							if( is_dir( $path . $entry ) ) //ディレクトリの場合
								{ continue; }

							if( !preg_match( '/\.csv$/' , $entry ) ) //CSVファイルではない場合
								{ continue; }

							$results[] = $path . $entry;
						}
					}

					foreach( $MODULES as $name => $option ) //全てのモジュールパックを処理
					{
						$path = './module/' . $name . '/db/template/';
						
						if( !file_exists( $path) ) { continue; }// テンプレートの存在しないモジュールはスルー
						$dir  = opendir( $path );

						while( $entry = readdir( $dir ) ) //全てのエントリを処理
						{
							if( is_dir( $path . $entry ) ) //ディレクトリの場合
								{ continue; }

							if( !preg_match( '/\.csv$/' , $entry ) ) //CSVファイルではない場合
								{ continue; }

							$results[] = $path . $entry;
						}
					}

					break;
				}

				case 'system_tables' : //システム管理用情報
				{
					$results[] = PathUtil::ModifyTDBFilePath( 'system/tables.csv' );

					break;
				}

				default : //その他
					{ break; }
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

			$results = parent::getStructDataFilePaths();

			switch( $this->tableName ) //テーブル名で分岐
			{
				case 'system_tables' : //システム管理用情報
				{
					$results[] = $lst_path . 'system/tables.csv';

					break;
				}

				default : //その他
					{ break; }
			}

			return $results;
		}

		/**
			@brief  テーブル初期値設定ファイルから一行分の情報を取得する。
			@return 初期値情報の配列。
		*/
		function readRow( $iMode = null ) //
		{
			$result = parent::readRow( $iMode );

			if( !$result )
				{ return $result; }

			switch( $this->tableName ) //テーブル名で分岐
			{
				case 'template' : //テンプレート
				{
					$result[ 'shadow_id' ] = $this->index;
					$result[ 'id' ]        = sprintf( 'T%04d' , $this->index );

					++$this->index;

					break;
				}

				default : //その他
					{ break; }
			}

			return $result;
		}

		private $index = 0;
	}
