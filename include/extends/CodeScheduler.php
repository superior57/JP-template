<?php

	//★クラス //

	/**
		@brief   タスク管理クラス。
		@details include順序等で実行したい処理がその場で呼び出せない場合などに。
	*/
	class CodeScheduler //
	{
		/**
			@brief     タスクを追加する。
			@param[in] $iLabel   タスクのラベル。同じラベルを持つタスクはキューに追加されます。
			@param[in] $iClosure タスクとして扱うクロージャ。
		*/
		static function Push( $iLabel , $iClosure ) //
			{ self::$Schedules[ $iLabel ][] = $iClosure; }

		/**
			@brief     タスクを追加する。
			@remarks   PHP5.3以前のバージョン用。call_user_func_arrayと同等の引数を指定してください。
			@param[in] $iLabel    タスクのラベル。同じラベルを持つタスクはキューに追加されます。
			@param[in] $iCallBack コールバック関数。
			@param[in] $iArray    コールバック関数の引数。
		*/
		static function PushCallBack( $iLabel , $iCallBack , $iArray ) //
			{ self::$Schedules[ $iLabel ][] = Array( $iCallBack , $iArray ); }

		/**
			@brief     タスクを実行する。
			@param[in] $iLabel タスクのラベル。
		*/
		static function Run( $iLabel ) //
		{
			if( !isset( self::$Schedules[ $iLabel ] ) ) //タスクが空の場合
				{ return; }

			foreach( self::$Schedules[ $iLabel ] as $iClosure ) //全てのタスクを処理
			{
				if( is_array( $iClosure ) ) //配列の場合
					{ call_user_func_array( $iClosure[ 0 ] , $iClosure[ 1 ] ); }
				else //クロージャの場合
					{ $iClosure(); }
			}

			self::$Schedules[ $iLabel ] = Array();
		}

		//■糖衣構文 //

		/**
			@brief     SetCronの実行タスクを追加する。
			@param[in] $iLabel  ラベル名。
			@param[in] $iClass  クラス名。
			@param[in] $iMethod メソッド名。
		*/
		static function SetCron( $iLabel , $iClass , $iMethod ) //
			{ self::PushCallBack( 'SetCron' , Array( 'cron_master' , 'SetCron' ) , Array( $iLabel , $iClass , $iMethod ) ); }

		/**
			@brief     トップスクリプトの事前処理タスクを追加する。
			@param[in] $iLabel  ラベル名。
			@param[in] $iClass  クラス名。
			@param[in] $iMethod メソッド名。
			@param[in] $iArgs   引数配列。
		*/
		static function BeforeMain( $iClass , $iMethod , $iArgs ) //
			{ self::PushCallBack( 'BeforeMain' , Array( $iClass , $iMethod ) , $iArgs ); }

		//■変数 //

		private static $Schedules = Array(); ///<タスク配列。
	}
