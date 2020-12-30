<?php

	/**
		@brief   システム設定情報管理クラス。
		@details システム全体の設定情報を管理します。
		@author  松木 昌平
		@version 1.0
		@ingroup Information
	*/
	class SystemInfo
	{
		//■初期化

		/**
			@brief システム設定を初期化する。
		*/
		private static function Initialize()
		{
			if( !self::$Initialized ) //初期化されていない場合
			{
				//systemテーブルから設定を取得
				$db    = SystemUtil::getGMforType( 'system' )->getDB();
				$table = $db->getTable();
				$rec   = $db->getRecord( $table , 0 );

				self::$HomeURL     = $db->getData( $rec , 'home' );
				self::$MailAddress = $db->getData( $rec , 'mail_address' );
				self::$MailName    = $db->getData( $rec , 'mail_name' );
				self::$CSSName     = $db->getData( $rec , 'main_css' );

				//設置先に関する設定を取得
				preg_match( '/(.*?)([^\/]+)$/' , $_SERVER[ 'SCRIPT_NAME' ] , $match );
				self::$RealBaseURL  = $match[ 1 ];
				self::$ScriptName   = $match[ 2 ];

				//グローバル変数の設定をインポート
				self::ImportGlobalVarConfigs();

				self::$DB     = $db;
				self::$Record = $rec;

				self::$Initialized = true;
			}
		}

		//■取得

		/**
			@brief  システムが生成するフォームの形式を取得する。
			@retval buffer   テンプレートに自動的に埋め込まれる場合
			@retval variable コマンドコメントで任意の位置に出力する場合
		*/
		static function GetAutoFormType()
		{
			self::Initialize();

			return self::$AutoFormType;
		}

		/**
			@brief     システムレコードに設定されているホームURLを取得する。
			@return    ホームURL。
			@attention 実際のURLとは異なる可能性があります。
		*/
		static function GetHomeURL()
		{
			self::Initialize();

			return self::$HomeURL;
		}

		/**
			@brief  システムが発行するcookieの有効パスを取得する。
			@return cookieの有効パス。
		*/
		static function GetCookieEnablePath()
		{
			self::Initialize();

			return self::$CookieEnablePath;
		}

		/**
			@brief  システムレコードに設定されているcss名設定を取得する。
			@return css名設定。
		*/
		static function GetCSSName()
		{
			self::Initialize();

			return self::$CSSName;
		}

		/**
			@brief  カスタムページを保存するディレクトリパスを取得する。
			@return ディレクトリパス。
		*/
		static function GetCustomPageDir()
		{
			self::Initialize();

			return self::$CustomPageDir;
		}

		/**
			@brief  ログイン情報のIDとして認識するフォーム名を取得する。
			@return IDフォーム名。
		*/
		static function GetLoginIDFormName()
		{
			self::Initialize();

			return self::$LoginIDFormName;
		}

		/**
			@brief  ログインIDを保存するセッションのキー名を取得する。
			@return キー名。
		*/
		static function GetLoginIDSaveName()
		{
			self::Initialize();

			return self::$LoginIDSaveName;
		}

		/**
			@brief  ログイン情報のパスワードとして認識するフォーム名を取得する。
			@return パスワードフォーム名。
		*/
		static function GetLoginPassFormName()
		{
			self::Initialize();

			return self::$LoginPassFormName;
		}

		/**
			@brief  システムがメール送信の署名に使用するメールアドレスを取得する。
			@return メールアドレス。
		*/
		static function GetMailAddress()
		{
			self::Initialize();

			return self::$MailAddress;
		}

		/**
			@brief  システムがメール送信の署名に使用する名前を取得する。
			@return 送信者名。
		*/
		static function GetMailName()
		{
			self::Initialize();

			return self::$MailName;
		}

		/**
			@brief  アップロード可能なファイルの最大サイズを取得する。
			@return 最大サイズ設定。
		*/
		static function GetMaxUploadByteSize()
		{
			self::Initialize();

			return self::$MaxUploadByteSize;
		}

		/**
			@brief  画像が見つからない場合の代替出力テキストを取得する。
			@return 代替テキスト。
		*/
		static function GetNullImageString()
		{
			self::Initialize();

			if( UserInfo::IsMobile() ) //携帯からアクセスしている場合
				{ return self::$NullImageStringMobile; }
			else //その他の端末からアクセスしている場合
				{ return self::$NullImageString; }
		}

		/**
			@brief  ログインしていないユーザーのユーザー種別名を取得する。
			@return 非ログインユーザー種別名。
		*/
		static function GetNotLoginUserType()
		{
			self::Initialize();

			return self::$NotLoginUserType;
		}

		/**
			@brief     システムレコードデータを取得する。
			@exception InvalidArgumentException 引数に不正な値を指定した場合。
			@exception LogicException           レコードに存在しないカラムを指定した場合。
			@param[in] $iColumn_ カラム名。
			@return    レコード格納値。
		*/
		static function GetParam( $iColumn_ )
		{
			self::Initialize();

			Concept::IsString( $iColumn_ )->OrThrow( 'InvalidArgument' , 'カラム名が無効です' );
			Concept::IsTrue( self::HasColumn() )->OrThrow( 'Logic' , 'systemテーブルにはカラム[' . $iColumn_ . ']は存在しません' );

			return self::$DB->getData( self::$Record , $iColumn_ );
		}

		/**
			@brief  環境変数から取得したシステムのベースURLを取得する。
			@return ベースURL。
		*/
		static function GetRealBaseURL()
		{
			self::Initialize();

			return self::$RealBaseURL;
		}

		/**
			@brief  ベースURLを保存するセッションのキー名を取得する。
			@return キー名。
		*/
		static function GetRealBaseURLSaveName()
		{
			self::Initialize();

			return self::$RealBaseURLSaveName;
		}

		/**
			@brief  環境変数から取得したアクセス中のphpファイル名を取得する。
			@return phpファイル名。
		*/
		static function GetScriptName()
		{
			self::Initialize();

			return self::$ScriptName;
		}

		/**
			@brief  環境変数から取得したアクセス中のphpファイル名を取得する。
			@return phpファイル名。
		*/
		static function GetSystemClassDir()
		{
			self::Initialize();

			return self::$SystemClassDir;
		}

		/**
			@brief     システムテーブルが指定のカラムを持っているか確認する。
			@param[in] $iColumn_ カラム名。
			@retval    true  カラムを持っている場合。
			@retval    false カラムを持っていない場合。
		*/
		static function HasColumn( $iColumn_ )
		{
			self::Initialize();

			return TableInfo::ExistsColumn( 'system' , $iColumn_ );
		}

		//■特殊

		/**
			@brief     グローバル変数から設定値をインポートする。
			@attention 移行が完了するまでの仮機能です。
		*/
		private static function ImportGlobalVarConfigs()
		{
			global $NOT_LOGIN_USER_TYPE;
			global $LOGIN_KEY_FORM_NAME;
			global $LOGIN_PASSWD_FORM_NAME;
			global $SESSION_NAME;
			global $SESSION_PATH_NAME;
			global $IMAGE_NOT_FOUND;
			global $IMAGE_NOT_FOUND_MOBILE;
			global $system_path;
			global $page_path;
			global $FORM_TAG_DRAW_FLAG;
			global $COOKIE_PATH;
			global $MAX_FILE_SIZE;

			self::$NotLoginUserType      = $NOT_LOGIN_USER_TYPE;
			self::$LoginIDFormName       = $LOGIN_KEY_FORM_NAME;
			self::$LoginPassFormName     = $LOGIN_PASSWD_FORM_NAME;
			self::$LoginIDSaveName       = $SESSION_NAME;
			self::$RealBaseURLSaveName   = $SESSION_PATH_NAME;
			self::$NullImageString       = $IMAGE_NOT_FOUND;
			self::$NullImageStringMobile = $IMAGE_NOT_FOUND_MOBILE;
			self::$SystemClassDir        = $system_path;
			self::$CustomPageDir         = $page_path;
			self::$AutoFormType          = $FORM_TAG_DRAW_FLAG;
			self::$CookieEnablePath      = $COOKIE_PATH;
			self::$MaxFileUploadByteSize = $MAX_FILE_SIZE;
		}

		//■変数
		private static $Initialized           = false;                           ///<初期化フラグ
		private static $DB                    = null;                            ///<データベースオブジェクト
		private static $Record                = null;                            ///<レコードデータ
		private static $HomeURL               = null;                            ///<ホームURL
		private static $MailAddress           = null;                            ///<署名メールアドレス
		private static $MailName              = null;                            ///<署名送信者名
		private static $CSSName               = null;                            ///<標準のcss名
		private static $RealBaseURL           = null;                            ///<環境変数から取得したベースURL
		private static $ScriptName            = null;                            ///<環境変数から取得したphpファイル名
		private static $NotLoginUserType      = 'nobody';                        ///<非ログインユーザー種別名
		private static $LoginIDFormName       = 'mail';                          ///<ログインIDフォーム名
		private static $LoginPassFormName     = 'passwd';                        ///<ログインパスワードフォーム名
		private static $LoginIDSaveName       = 'loginid';                       ///<ログインIDを保存するセッションキー
		private static $RealBaseURLSaveName   = 'system_path';                   ///<ベースURLを保存するセッションキー
		private static $NullImageString       = '<span>No Image</span>';         ///<画像が見つからない場合の代替テキスト
		private static $NullImageStringMobile = 'common/img/no_image_80x60.gif'; ///<携帯端末で画像が見つからない場合の代替テキスト
		private static $SystemClassDir        = 'custom/system/';                ///<システムクラス格納ディレクトリ
		private static $CustomPageDir         = 'file/page/';                    ///<カスタムページ保存ディレクトリ
		private static $AutoFormType          = 'variable';                      ///<システムフォームの形式
		private static $CookieEnablePath      = '/';                             ///<cookieの有効パス
		private static $MaxFileUploadByteSize = 512000;                          ///<アップロードファイルの最大サイズ
	}

?>