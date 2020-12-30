<?php

	/**
		@brief   テーブル設定情報管理クラス。
		@details テーブルとカラムの設定情報を管理します。
		@author  松木 昌平
		@version 1.0
		@ingroup Information
	*/
	class TableInfo
	{
		//■初期化

		/**
			@brief システム設定を初期化する。
		*/
		private static function Initialize()
		{
			if( !self::$Initialized ) //初期化されていない場合
			{
				self::$Initialized = true;

				self::ImportGlobalVarConfigs();
			}
		}

		//■追加

		/**
			@brief     csvファイルからテーブルのカラム設定を追加する。
			@exception InvalidArgumentException 引数に不正な値を指定した場合。
			@exception FileIOException          csvファイルの読み込みに失敗した場合。
			@exception RuntimeException         テーブルに対する同名のカラム情報が既に存在する場合。
			@param[in] $iTableName_   テーブル名。
			@param[in] $iLstFilePath_ テーブルのカラム構造を記述したcsvファイルのパス。
			@remarks   1つのテーブルに複数のcsvファイルがある場合は、各ファイル毎にこのメソッドを呼び出してください。
		*/
		static function LoadColumn( $iTableName_ , $iLstFilePath_ )
		{
			Concept::IsString( $iTableName_ , $iLstFilePath_ )->OrThrow( 'InvalidArgument' , 'テーブル名またはファイルパスが無効です' );

			$fp = fopen( $iLstFilePath_ , 'rb' );

			Concept::IsResource( $fp )->OrThrow( 'FileIOException' , 'ファイルが開けません[' . $iLstFilePath_ . ']' );

			while( !feof( $fp ) )
			{
				$datas = WS_fgetcsv( $fp );

				if( !is_array( $datas ) ) //読み込みに失敗した場合
					{ continue; }

				if( 1 == count( $datas ) && is_null( $datas[ 0 ] ) ) //空行だった場合
					{ continue; }

				foreach( $datas as &$data ) //全てのフィールドを処理する
					{ $data = trim( $data ); }

				List( $name , $type , $maxSize , $registerCheck , $editcheck , $regex , $step , $replace ) = $datas;

				Concept::IsFalse( self::ExistsColumn( $iTableName_ , $name ) )->OrThrow( 'Logic' , '[' . $iTableName_ . ']テーブルのカラム設定[' . $name . ']が重複しています' );

				$configs = Array();

				$configs[ 'type' ]          = $type;
				$configs[ 'size' ]          = $maxSize;
				$configs[ 'registercheck' ] = $registCheck;
				$configs[ 'editCheck' ]     = $editCheck;
				$configs[ 'regex' ]         = $regex;
				$configs[ 'step' ]          = $step;
				$configs[ 'replace' ]       = $replace;

				if( 0 >= $configs[ 'step' ] ) //ステップ設定が0以下の場合
					{ $configs[ 'step' ] = 0; }

				self::$Columns[ $iTableName_ ][ $name ] = $configs;
			}
		}

		/**
			@brief     テーブル設定を追加する。
			@exception InvalidArgumentException 引数に不正な値を指定した、または同名のテーブル設定が既に存在する場合。
			@param[in] $iTableName_ テーブル名。
			@param[in] $iConfigs_   テーブルの設定値を格納した連想配列。
				@li isUser               このテーブルをユーザーと認識し、ログインを許可するならtrue。
				@li loginIDColumn        ログインに使用するユーザー識別カラム。ユーザーテーブルでない場合は無視されます。
				@li loginPassColumn      ログインに使用するパスワードカラム。ユーザーテーブルでない場合は無視されます。
				@li loginPassCheckColumn [任意]パスワードの入力確認に使用するカラム。ユーザーテーブルでない場合は無視されます。
				@li lstFilePath          テーブルのカラム構造を記述したcsvファイルのパス。存在しないファイルを指定することはできません。
				@li tdbFilePath          テーブルのレコードデータを記述したcsvファイルのパス。存在しないファイルを指定することはできません。
				@li idHeader             このテーブルのレコードIDの頭文字。
				@li idLength             このテーブルのレコードIDの頭文字を含めた長さ。
				@li enableQuickLogin     [任意]このテーブルのクイックログインを許可するならtrue。ユーザーテーブルでない場合は無視されます。
				@li registrableUsers     [任意]このテーブルを登録可能なユーザーリスト。配列で指定します
				@li editableUsers        [任意]このテーブルを編集可能なユーザーリスト。配列で指定します。
				@li ownerMarks           [任意]このテーブルの所有者IDを格納するカラムリスト。所有者テーブル名/カラム名の連想配列で指定します。\n
				                         所有者が指定されているテーブルはIDが一致するユーザーにしか編集できなくなります。
			@attention 設定項目 loginPassCheckColumn は将来的に廃止される予定です。
		*/
		static function RegisterTable( $iTableName_ , $iConfigHash_ )
		{
			Concept::IsString( $iTableName_ )->OrThrow( 'InvalidArgument' );
			Concept::IsArray( $iConfigHash_ )->OrThrow( 'InvalidArgument' );

			Concept::IsFalse( self::ExistsTable( $iTableName_ ) )->OrThrow( 'InvalidArgument' );

			foreach( self::$RequestConfigTypes as $name => $type ) //共通設定項目の処理
				{ Concept::IsInType( $type , $iConfigHash_[ $name ] )->OrThrow( 'InvalidArgument' , '[' . $iTableName_ . '][' . $type . '][' . $name . '][' . $iConfigHash_[ $name ] . ']' ); }

			if( $iConfigHash_[ 'isUser' ] ) //ユーザーテーブルの場合
			{
				foreach( self::$RequestUserConfigTypes as $name => $type ) //ユーザー専用設定項目の処理
					{ Concept::IsInType( $type , $iConfigHash_[ $name ] )->OrThrow( 'InvalidArgument' ); }
			}

			self::$Tables[ $iTableName_ ] = $iConfigHash_;
		}

		//■リスト

		/**
			@brief  テーブル名一覧を取得する。
			@return テーブル名を格納した配列。
		*/
		static function GetTableNames()
		{
			self::Initialize();

			return array_keys( self::$Tables );
		}

		//■テーブル設定取得

		/**
			@brief     テーブル設定が存在するか確認する。
			@exception InvalidArgumentException 引数に不正な値を指定した場合。
			@param[in] $iTableName_ テーブル名。
			@retval    true  テーブル設定が存在する場合。
			@retval    false テーブル設定が存在しない場合。
		*/
		static function ExistsTable( $iTableName_ )
		{
			self::Initialize();

			Concept::IsString( $iTableName_ )->OrThrow( 'InvalidArgument' );

			return array_key_exists( $iTableName_ , self::$Tables );
		}

		/**
			@brief     テーブルを編集可能なユーザー一覧を取得する。
			@exception InvalidArgumentException 引数に不正な値を指定した、またはテーブル設定が存在しない場合。
			@param[in] $iTableName_ テーブル名。
			@return    編集可能ユーザー名を格納した配列。
		*/
		static function GetEditableUsers( $iTableName_ )
		{
			self::Initialize();

			Concept::IsString( $iTableName_ )->OrThrow( 'InvalidArgument' );
			Concept::IsTrue( self::ExistsTable( $iTableName_ ) )->OrThrow( 'InvalidArgument' );

			if( is_array( self::$Tables[ $iTableName_ ][ 'editableUsers' ] ) ) //配列が設定されている場合
				{ self::$Tables[ $iTableName_ ][ 'editableUsers' ]; }
			else //nullが設定されている場合
				{ return Array(); }
		}

		/**
			@brief     テーブルのレコードIDの接頭子を取得する。
			@exception InvalidArgumentException 引数に不正な値を指定した、またはテーブル設定が存在しない場合。
			@param[in] $iTableName_ テーブル名。
			@return    レコードIDの接頭子。
		*/
		static function GetIDHeader( $iTableName_ )
		{
			self::Initialize();

			Concept::IsString( $iTableName_ )->OrThrow( 'InvalidArgument' );
			Concept::IsTrue( self::ExistsTable( $iTableName_ ) )->OrThrow( 'InvalidArgument' );

			return self::$Tables[ $iTableName_ ][ 'idHeader' ];
		}

		/**
			@brief     テーブルのレコードIDのサイズを取得する。
			@exception InvalidArgumentException 引数に不正な値を指定した、またはテーブル設定が存在しない場合。
			@param[in] $iTableName_ テーブル名。
			@return    レコードIDのサイズ。
		*/
		static function GetIDLength( $iTableName_ )
		{
			self::Initialize();

			Concept::IsString( $iTableName_ )->OrThrow( 'InvalidArgument' );
			Concept::IsTrue( self::ExistsTable( $iTableName_ ) )->OrThrow( 'InvalidArgument' );

			return self::$Tables[ $iTableName_ ][ 'idLength' ];
		}

		/**
			@brief     ログインIDに使用するカラム名を取得する。
			@exception InvalidArgumentException 引数に不正な値を指定した、またはテーブル設定が存在しない場合。
			@param[in] $iTableName_ テーブル名。
			@return    カラム名。
		*/
		static function GetLoginIDColumn( $iTableName_ )
		{
			self::Initialize();

			Concept::IsString( $iTableName_ )->OrThrow( 'InvalidArgument' );
			Concept::IsTrue( self::ExistsTable( $iTableName_ ) )->OrThrow( 'InvalidArgument' );

			return self::$Tables[ $iTableName_ ][ 'loginIDColumn' ];
		}

		/**
			@brief     ログインパスワード入力チェックに使用するカラム名を取得する。
			@exception InvalidArgumentException 引数に不正な値を指定した、またはテーブル設定が存在しない場合。
			@param[in] $iTableName_ テーブル名。
			@return    カラム名。
		*/
		static function GetLoginPassCheckColumn( $iTableName_ )
		{
			self::Initialize();

			Concept::IsString( $iTableName_ )->OrThrow( 'InvalidArgument' );
			Concept::IsTrue( self::ExistsTable( $iTableName_ ) )->OrThrow( 'InvalidArgument' );

			return self::$Tables[ $iTableName_ ][ 'loginPassCheckColumn' ];
		}

		/**
			@brief     ログインパスワードに使用するカラム名を取得する。
			@exception InvalidArgumentException 引数に不正な値を指定した、またはテーブル設定が存在しない場合。
			@param[in] $iTableName_ テーブル名。
			@return    カラム名。
		*/
		static function GetLoginPassColumn( $iTableName_ )
		{
			self::Initialize();

			Concept::IsString( $iTableName_ )->OrThrow( 'InvalidArgument' );
			Concept::IsTrue( self::ExistsTable( $iTableName_ ) )->OrThrow( 'InvalidArgument' );

			return self::$Tables[ $iTableName_ ][ 'loginPassColumn' ];
		}

		/**
			@brief     テーブル構造定義ファイルのパスを取得する。
			@exception InvalidArgumentException 引数に不正な値を指定した、またはテーブル設定が存在しない場合。
			@param[in] $iTableName_ テーブル名。
			@return    ファイルのパス。
		*/
		static function GetLstFilePath( $iTableName_ )
		{
			self::Initialize();

			Concept::IsString( $iTableName_ )->OrThrow( 'InvalidArgument' );
			Concept::IsTrue( self::ExistsTable( $iTableName_ ) )->OrThrow( 'InvalidArgument' );

			return self::$Tables[ $iTableName_ ][ 'lstFilePath' ];
		}

		/**
			@brief     テーブルの所有者チェック設定一覧を取得する。
			@exception InvalidArgumentException 引数に不正な値を指定した、またはテーブル設定が存在しない場合。
			@param[in] $iTableName_ テーブル名。
			@return    所有者ユーザー名とID格納カラム名を格納した連想配列。
		*/
		static function GetOwnerMarks( $iTableName_ )
		{
			self::Initialize();

			Concept::IsString( $iTableName_ )->OrThrow( 'InvalidArgument' );
			Concept::IsTrue( self::ExistsTable( $iTableName_ ) )->OrThrow( 'InvalidArgument' );

			if( is_array( self::$Tables[ $iTableName_ ][ 'ownerMarks' ] ) ) //配列が設定されている場合
				{ self::$Tables[ $iTableName_ ][ 'ownerMarks' ]; }
			else //nullが設定されている場合
				{ return Array(); }
		}

		/**
			@brief     テーブルを登録可能なユーザー一覧を取得する。
			@exception InvalidArgumentException 引数に不正な値を指定した、またはテーブル設定が存在しない場合。
			@param[in] $iTableName_ テーブル名。
			@return    登録可能ユーザー名を格納した配列。
		*/
		static function GetRegistrableUsers( $iTableName_ )
		{
			self::Initialize();

			Concept::IsString( $iTableName_ )->OrThrow( 'InvalidArgument' );
			Concept::IsTrue( self::ExistsTable( $iTableName_ ) )->OrThrow( 'InvalidArgument' );

			if( is_array( self::$Tables[ $iTableName_ ][ 'registrableUsers' ] ) ) //配列が設定されている場合
				{ self::$Tables[ $iTableName_ ][ 'registrableUsers' ]; }
			else //nullが設定されている場合
				{ return Array(); }
		}

		/**
			@brief     レコードデータファイルのパスを取得する。
			@exception InvalidArgumentException 引数に不正な値を指定した、またはテーブル設定が存在しない場合。
			@param[in] $iTableName_ テーブル名。
			@return    ファイルのパス。
		*/
		static function GetTdbFilePath( $iTableName_ )
		{
			self::Initialize();

			Concept::IsString( $iTableName_ )->OrThrow( 'InvalidArgument' );
			Concept::IsTrue( self::ExistsTable( $iTableName_ ) )->OrThrow( 'InvalidArgument' );

			return self::$Tables[ $iTableName_ ][ 'tdbFilePath' ];
		}

		/**
			@brief     このテーブルがクイックログイン許可属性を持つか確認する。
			@exception InvalidArgumentException 引数に不正な値を指定した、またはテーブル設定が存在しない場合。
			@param[in] $iTableName_ テーブル名。
			@retval    true  テーブルのクイックログインが許可されている場合。
			@retval    false テーブルのクイックログインが禁止されている場合。
		*/
		static function IsEnableQuickLogin( $iTableName_ )
		{
			self::Initialize();

			Concept::IsString( $iTableName_ )->OrThrow( 'InvalidArgument' );
			Concept::IsTrue( self::ExistsTable( $iTableName_ ) )->OrThrow( 'InvalidArgument' );

			return self::$Tables[ $iTableName_ ][ 'isEnableQuickLogin' ];
		}

		/**
			@brief     このテーブルがユーザー属性を持つか確認する。
			@exception InvalidArgumentException 引数に不正な値を指定した、またはテーブル設定が存在しない場合。
			@param[in] $iTableName_ テーブル名。
			@retval    true  テーブルがユーザーテーブルの場合。
			@retval    false テーブルがデータテーブルの場合。
		*/
		static function IsUser( $iTableName_ )
		{
			self::Initialize();

			Concept::IsString( $iTableName_ )->OrThrow( 'InvalidArgument' );
			Concept::IsTrue( self::ExistsTable( $iTableName_ ) )->OrThrow( 'InvalidArgument' );

			return self::$Tables[ $iTableName_ ][ 'isUser' ];
		}

		//■カラム設定取得

		/**
			@brief     テーブルにカラムが存在するか確認する。
			@exception InvalidArgumentException 引数に不正な値を指定した場合。
			@param[in] $iTableName_  テーブル名。
			@param[in] $iColumnName_ カラム名。
			@retval    true  カラムが存在する場合。
			@retval    false カラムが存在しない場合。
		*/
		static function ExistsColumn( $iTableName_ , $iColumnName_ )
		{
			self::Initialize();

			Concept::IsString( $iTableName_ , $iColumnName_ )->OrThrow( 'InvalidArgument' );

			if( array_key_exists( $iTableName_ , self::$Columns ) ) //カラム設定が存在する場合
				{ return array_key_exists( $iColumnName_ , self::$Columns[ $iTableName_ ] ); }
			else //カラム設定が存在しない場合
				{ return false; }
		}

		/**
			@brief     カラムの編集時チェック設定を取得する。
			@exception InvalidArgumentException 引数に不正な値を指定した場合。
			@param[in] $iTableName_  テーブル名。
			@param[in] $iColumnName_ カラム名。
			@return    編集時チェック設定。
		*/
		static function GetColumnEditCheck( $iTableName_ , $iColumnName_ )
		{
			self::Initialize();

			Concept::IsString( $iTableName_ , $iColumnName_ )->OrThrow( 'InvalidArgument' );
			Concept::IsTrue( self::ExistsColumn( $iTableName_ , $iColumnName_ ) )->OrThrow( 'InvalidArgument' );

			return self::$Columns[ $iTableName_ ][ $iColumnName_ ][ 'editCheck' ];
		}

		/**
			@brief     カラムの正規表現設定を取得する。
			@exception InvalidArgumentException 引数に不正な値を指定した場合。
			@param[in] $iTableName_  テーブル名。
			@param[in] $iColumnName_ カラム名。
			@return    正規表現設定。
		*/
		static function GetColumnRegex( $iTableName_ , $iColumnName_ )
		{
			self::Initialize();

			Concept::IsString( $iTableName_ , $iColumnName_ )->OrThrow( 'InvalidArgument' );
			Concept::IsTrue( self::ExistsColumn( $iTableName_ , $iColumnName_ ) )->OrThrow( 'InvalidArgument' );

			return self::$Columns[ $iTableName_ ][ $iColumnName_ ][ 'regex' ];
		}

		/**
			@brief     カラムの登録時チェック設定を取得する。
			@exception InvalidArgumentException 引数に不正な値を指定した場合。
			@param[in] $iTableName_  テーブル名。
			@param[in] $iColumnName_ カラム名。
			@return    登録時チェック設定。
		*/
		static function GetColumnRegiserCheck( $iTableName_ , $iColumnName_ )
		{
			self::Initialize();

			Concept::IsString( $iTableName_ , $iColumnName_ )->OrThrow( 'InvalidArgument' );
			Concept::IsTrue( self::ExistsColumn( $iTableName_ , $iColumnName_ ) )->OrThrow( 'InvalidArgument' );

			return self::$Columns[ $iTableName_ ][ $iColumnName_ ][ 'registerCheck' ];
		}

		/**
			@brief     カラムの置換設定を取得する。
			@exception InvalidArgumentException 引数に不正な値を指定した場合。
			@param[in] $iTableName_  テーブル名。
			@param[in] $iColumnName_ カラム名。
			@return    置換設定。
		*/
		static function GetColumnReplace( $iTableName_ , $iColumnName_ )
		{
			self::Initialize();

			Concept::IsString( $iTableName_ , $iColumnName_ )->OrThrow( 'InvalidArgument' );
			Concept::IsTrue( self::ExistsColumn( $iTableName_ , $iColumnName_ ) )->OrThrow( 'InvalidArgument' );

			return self::$Columns[ $iTableName_ ][ $iColumnName_ ][ 'replace' ];
		}

		/**
			@brief     テーブルのカラム名一覧を取得する。
			@exception InvalidArgumentException 引数に不正な値を指定した場合。
			@param[in] $iTableName_  テーブル名。
			@return    カラム名を格納した配列。
		*/
		static function getColumns( $iTableName_ )
		{
			self::Initialize();

			Concept::IsString( $iTableName_ )->OrThrow( 'InvalidArgument' );

			if( array_key_exists( $iTableName_ , self::$Columns ) ) //カラム設定が存在する場合
				{ return array_keys( self::$Columns[ $iTableName_ ] ); }
			else //カラム設定が存在しない場合
				{ return Array(); }
		}

		/**
			@brief     カラムのサイズ設定を取得する。
			@exception InvalidArgumentException 引数に不正な値を指定した場合。
			@param[in] $iTableName_  テーブル名。
			@param[in] $iColumnName_ カラム名。
			@return    サイズ設定。
		*/
		static function GetColumnSize( $iTableName_ , $iColumnName_ )
		{
			self::Initialize();

			Concept::IsString( $iTableName_ , $iColumnName_ )->OrThrow( 'InvalidArgument' );
			Concept::IsTrue( self::ExistsColumn( $iTableName_ , $iColumnName_ ) )->OrThrow( 'InvalidArgument' );

			return self::$Columns[ $iTableName_ ][ $iColumnName_ ][ 'size' ];
		}

		/**
			@brief     カラムの登録ステップ設定を取得する。
			@exception InvalidArgumentException 引数に不正な値を指定した場合。
			@param[in] $iTableName_  テーブル名。
			@param[in] $iColumnName_ カラム名。
			@return    登録ステップ設定。
		*/
		static function GetColumnStep( $iTableName_ , $iColumnName_ )
		{
			self::Initialize();

			Concept::IsString( $iTableName_ , $iColumnName_ )->OrThrow( 'InvalidArgument' );
			Concept::IsTrue( self::ExistsColumn( $iTableName_ , $iColumnName_ ) )->OrThrow( 'InvalidArgument' );

			return self::$Columns[ $iTableName_ ][ $iColumnName_ ][ 'step' ];
		}

		/**
			@brief     カラムの型設定を取得する。
			@exception InvalidArgumentException 引数に不正な値を指定した場合。
			@param[in] $iTableName_  テーブル名。
			@param[in] $iColumnName_ カラム名。
			@return    型設定。
		*/
		static function GetColumnType( $iTableName_ , $iColumnName_ )
		{
			self::Initialize();

			Concept::IsString( $iTableName_ , $iColumnName_ )->OrThrow( 'InvalidArgument' );
			Concept::IsTrue( self::ExistsColumn( $iTableName_ , $iColumnName_ ) )->OrThrow( 'InvalidArgument' );

			return self::$Columns[ $iTableName_ ][ $iColumnName_ ][ 'type' ];
		}

		//■特殊

		/**
			@brief     グローバル変数から設定値をインポートする。
			@attention 移行が完了するまでの仮機能です。
		*/
		static function ImportGlobalVarConfigs()
		{
			global $TABLE_NAME;
			global $THIS_TABLE_IS_USERDATA;
			global $LOGIN_KEY_COLUM;
			global $LOGIN_PASSWD_COLUM;
			global $LOGIN_PASSWD_COLUM2;
			global $LST;
			global $TDB;
			global $ID_HEADER;
			global $ID_LENGTH;
			global $THIS_TABLE_IS_QUICK;
			global $THIS_TABLE_REGIST_USER;
			global $THIS_TABLE_EDIT_USER;
			global $THIS_TABLE_OWNER_COLUM;
			global $ADD_LST;
			
			global $lst_path;
			global $tdb_path;

			foreach( $TABLE_NAME as $table ) //全てのテーブルを処理
			{
				$configs = Array();

				$configs[ 'isUser' ] = $THIS_TABLE_IS_USERDATA[ $table ];

				if( $configs[ 'isUser' ] ) //ユーザーテーブルの場合
				{
					$configs[ 'loginIDColumn' ]        = $LOGIN_KEY_COLUM[ $table ];
					$configs[ 'loginPassColumn' ]      = $LOGIN_PASSWD_COLUM[ $table ];
					$configs[ 'loginPassCheckColumn' ] = $LOGIN_PASSWD_COLUM2[ $table ];
				}

				$configs[ 'lstFilePath' ]      = $lst_path . $LST[ $table ];
				$configs[ 'tdbFilePath' ]      = $tdb_path . $TDB[ $table ];
				$configs[ 'idHeader' ]         = $ID_HEADER[ $table ];
				$configs[ 'idLength' ]         = $ID_LENGTH[ $table ];
				$configs[ 'enableQuickLogin' ] = $THIS_TABLE_IS_QUICK[ $table ];
				$configs[ 'registrableUsers' ] = $THIS_TABLE_REGIST_USER[ $table ];
				$configs[ 'editableUsers' ]    = $THIS_TABLE_EDIT_USER[ $table ];
				$configs[ 'ownerMarks' ]       = $THIS_TABLE_OWNER_COLUM[ $table ];

				self::RegisterTable( $table , $configs );

				self::LoadColumn( $table , $configs[ 'lstFilePath' ] );

				if( is_array( $ADD_LST ) ) //追加カラム設定が存在する場合
				{
					if( array_key_exists( $table , $ADD_LST ) ) //追加カラムが設定されている場合
					{
						foreach( $ADD_LST[ $table ] as $lst ) //追加カラムを処理
							{ self::LoadColumn( $table , $lst_path . $lst ); }
					}
				}
			}
		}

		//■変数
		static private $Initialized = false;   ///<初期化フラグ。
		static private $Tables      = Array(); ///<テーブル設定格納配列。
		static private $Columns     = Array(); ///<カラム設定格納配列。

		static private $RequestConfigTypes = ///<テーブルの設定項目ごとの型
		Array(
			'isUser'           => 'bool' ,
			'lstFilePath'      => 'string' ,
			'tdbFilePath'      => 'string' ,
			'idHeader'         => 'string' ,
			'idLength'         => 'numeric' ,
			'registrableUsers' => 'array/null' ,
			'editableUsers'    => 'array/null' ,
			'ownerMarks'       => 'array/null'
		);

		static private $RequestUserConfigTypes = ///<ユーザーテーブルの設定項目ごとの型
		Array(
			'enableQuickLogin'     => 'bool/null' ,
			'loginIDColumn'        => 'string' ,
			'loginPassColumn'      => 'string' ,
			'loginPassCheckColumn' => 'string/null'
		);
	}
?>