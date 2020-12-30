<?php

	/**
		@brief   アクティベートレベル管理クラス。
		@details アクティベートレベルの設定情報を管理します。
		@author  松木 昌平
		@version 1.0
		@ingroup Information
	*/
	class ActivateInfo
	{
		//■初期化

		/**
			@brief アクティベートレベル設定を初期化する。
		*/
		private static function Initialize()
		{
			if( !self::$Initialized ) //初期化されていない場合
			{
				self::ImportGlobalVarConfigs();

				self::$Initialized = true;
			}
		}

		//■取得

		/**
			@brief  アクティベートレベル[認証]を表すビット値を取得する。
			@return ビット値。
		*/
		static function GetAcceptBit()
		{
			self::Initialize();

			return self::$ActiveAcceptBit;
		}

		/**
			@brief  アクティベートレベル[仮認証]を表すビット値を取得する。
			@return ビット値。
		*/
		static function GetActivateBit()
		{
			self::Initialize();

			return self::$ActiveActivateBit;
		}

		/**
			@brief  全てのアクティベートレベルを表すビット値を取得する。
			@return ビット値。
		*/
		static function GetAllBit()
		{
			self::Initialize();

			return self::$ActiveAllBit;
		}

		/**
			@brief  アクティベートレベル[拒否]を表すビット値を取得する。
			@return ビット値。
		*/
		static function GetDenyBit()
		{
			self::Initialize();

			return self::$ActiveDenyBit;
		}

		/**
			@brief  アクティベートレベル[未認証]を表すビット値を取得する。
			@return ビット値。
		*/
		static function GetNoneBit()
		{
			self::Initialize();

			return self::$ActiveNoneBit;
		}

		//■特殊

		/**
			@brief     グローバル変数から設定値をインポートする。
			@attention 移行が完了するまでの仮機能です。
		*/
		private static function ImportGlobalVarConfigs()
		{
			global $ACTIVE_NONE;
			global $ACTIVE_ACTIVATE;
			global $ACTIVE_ACCEPT;
			global $ACTIVE_DENY;
			global $ACTIVE_ALL;

			self::$ActiveNoneBit     = $ACTIVE_NONE;
			self::$ActiveActivateBit = $ACTIVE_ACTIVATE;
			self::$ActiveAcceptBit   = $ACTIVE_ACCEPT;
			self::$ActiveDenyBit     = $ACTIVE_DENY;
			self::$ActiveAllBit      = $ACTIVE_ALL;
		}

		//■変数
		private static $Initialized         = false; ///<初期化フラグ
		private static $ActiveNoneBit     = 1;       ///<未認証
		private static $ActiveActivateBit = 2;       ///<仮認証
		private static $ActiveAcceptBit   = 4;       ///<認証
		private static $ActiveDenyBit     = 8;       ///<拒否
		private static $ActiveAllBit      = 15;      ///<全て
	}

?>