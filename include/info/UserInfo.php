<?php

	/**
		@brief   ユーザー設定情報管理クラス。
		@details システムにアクセスしているユーザーの情報を管理します。
		@author  松木 昌平
		@version 1.0
		@ingroup Information
	*/
	class UserInfo
	{
		//■初期化

		/**
			@brief     ユーザー情報を初期化する。
			@exception IllegalAccessException ログインIDに不正な値が設定されている場合。
		*/
		private static function Initialize()
		{
			if( !self::$Initialized ) //初期化されていない場合
			{
				if( SystemInfo::GetRealBaseURL() == $_SESSION[ SystemInfo::GetRealBaseURLSaveName() ] ) //URLが一致する場合
				{
					self::$ID = $_SESSION[ SystemInfo::GetLoginIDSaveName() ];

					if( self::$ID ) //IDがセットされている場合
					{
						Concept::IsNotMatch( '/\W/' , self::$ID )->OrThrow( 'IllegalAccess' );

						//ユーザー種別を特定する
						foreach( TableInfo::GetTableNames() as $tableName ) //全てのテーブルを処理
						{
							if( !TableInfo::IsUser( $tableName ) ) //ユーザーテーブルではない場合
								{ continue; }

							$db    = SystemUtil::getGMforType( $tableName )->getDB();
							$table = $db->getTable();
							$table = $db->searchTable( $table , 'id' , '=' , self::$ID );
							$table = $db->LimitOffset( $table , 0 , 1 );

							if( $rec = $db->getFirstRecord( $table ) ) //IDが一致するレコードがある場合
							{
								self::$DB       = $db;
								self::$Record   = $rec;
								self::$Type     = $tableName;
								self::$Activate = $db->getData( $rec , 'activate' );
								break;
							}
						}

						Concept::IsNotNull( self::$Record )->OrThrow( 'IllegalAccess' );
					}
				}

				if( !self::$Record ) //ユーザーレコードが見つからない場合
				{
					self::$Type     = SystemInfo::GetNotLoginUserType();
					self::$Activate = ActivateInfo::GetNoneBit();
				}

				//アクセス端末を特定する
				self::$Terminal = MobileUtil::getTerminal();

				self::$Initialized = true;
			}
		}

		//■取得

		/**
			@brief  ユーザーのアクティベートレベルを取得する。
			@return アクティベートレベル。
		*/
		static function GetActivate()
		{
			self::Initialize();

			return self::$Activate;
		}

		/**
			@brief  ユーザーのIDを取得する。
			@return レコードID。
		*/
		static function GetID()
		{
			self::Initialize();

			return self::$ID;
		}

		/**
			@brief     ユーザーのレコードデータを取得する。
			@exception InvalidArgumentException 引数に不正な値を指定した場合。
			@exception LogicException           ユーザーがログインしていない、またはレコードに存在しないカラムを指定した場合。
			@param[in] $iColumn_ カラム名。
			@return    レコード格納値。
		*/
		static function GetParam( $iColumn_ )
		{
			self::Initialize();

			Concept::IsString( $iColumn_ )->OrThrow( 'InvalidArgument' );
			Concept::IsTrue( self::IsLogin() )->OrThrow( 'Logic' );
			Concept::IsTrue( self::HasColumn() )->OrThrow( 'Logic' );

			return self::$DB->getData( self::$Record , $iColumn_ );
		}

		/**
			@brief     ユーザーのレコードを取得する。
			@exception LogicException ユーザーがログインしていない場合。
			@return    レコードデータ。
		*/
		static function GetRecord()
		{
			self::Initialize();

			Concept::IsTrue( self::IsLogin() )->OrThrow( 'Logic' );

			return self::$Record;
		}

		/**
			@brief     ユーザーのアクセス端末を取得する。
			@exception LogicException ユーザーが携帯以外の端末からアクセスしている場合。
			@return    アクセス端末の種別。
		*/
		static function GetTerminal()
		{
			self::Initialize();

			Concept::IsTrue( self::IsMobile() )->OrThrow( 'Logic' );

			return ( 0 < self::$Terminal ? true : false );
		}

		/**
			@brief  ユーザーの種別を取得する。
			@return ユーザー種別名。
		*/
		static function GetType()
		{
			self::Initialize();

			return self::$Type;
		}

		/**
			@brief     ユーザーテーブルが指定のカラムを持っているか確認する。
			@param[in] $iColumn_ カラム名。
			@retval    true  カラムを持っている場合。
			@retval    false カラムを持っていない、またはログインしていない場合。
		*/
		static function HasColumn( $iColumn_ )
		{
			self::Initialize();

			if( !self::IsLogin() ) //ログインしていない場合
				{ return false; }

			return TableInfo::ExistsColumn( self::GetType() , $iColumn_ );
		}

		/**
			@brief  ユーザーがログインしているか確認する。
			@retval true  ログインしている場合。
			@retval false ログインしていない場合。
		*/
		static function IsLogin()
		{
			self::Initialize();

			return ( self::$Record ? true : false );
		}

		/**
			@brief  ユーザーのアクセス端末を確認する。
			@retval true  携帯からアクセスしている場合。
			@retval false その他の端末からアクセスしている場合。
		*/
		static function IsMobile()
		{
			self::Initialize();

			return ( 0 < self::$Terminal ? true : false );
		}

		//■変数
		private static $Initialized = false; ///<初期化フラグ
		private static $DB          = null;  ///<データベースオブジェクト
		private static $Record      = null;  ///<レコードデータ
		private static $ID          = null;  ///<レコードID
		private static $Type        = null;  ///<ユーザー種別
		private static $Activate    = null;  ///<アクティベートレベル
		private static $Terminal    = null;  ///<アクセス端末
	}

?>