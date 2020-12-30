<?php

	/**
		@brief   ベースコンセプトクラス。
		@details コンセプトクラスを定義するための基本機能を実装します。
		@author  松木 昌平
		@version 1.0
		@ingroup Utility
	*/
	class ConceptBase
	{
		//■例外

		/**
			@brief     コンセプトに違反している場合は例外をスローする。
			@details   このメソッドは次の可変長の引数を取ります。
				@li $iClassName_ 例外の型。省略時はExceptionが使用されます。
				@li $iMessage_   例外メッセージ。
			@attention 例外の型は接尾子Exceptionを付けずに指定してください。例として RuntimeException を指定する場合は 'Runtime' となります。
			@remarks   このメソッドを呼び出すと、コンセプトの評価は初期化されます。\n
		*/
		static function OrThrow()
		{
			if( self::$IsFailed ) //コンセプトに違反している場合
			{
				List( $iClassName_ , $iMessage_ ) = func_get_args();

				$exception = self::CreateExceptionObject( $iClassName_ , $iMessage_ );

				self::ClearJudge();

				throw $exception;
			}

			self::ClearJudge();
		}

		//■評価

		/**
			@brief コンセプトの評価を初期化する。
		*/
		protected static function ClearJudge()
		{
			self::$SuccessCount = 0;
			self::$FailedCount  = 0;
			self::$IsFailed     = false;
			self::$FailedArgs   = Array();
		}

		/**
			@brief   コンセプトを評価する。
			@details このメソッドは次の可変長の引数を取ります。
				@li $iTerm_ コンセプトの評価値。
				@li $iArg_  コンセプトの評価引数。
			@attention コンセプトの評価を OrThrow に反映するには UnionJudge を呼び出す必要があります。
		*/
		protected static function Judge()
		{
			List( $iTerm_ , $iArg_ ) = func_get_args();

			if( $iTerm_ ) //コンセプトに適合している場合
				{ ++self::$SuccessCount; }
			else //コンセプトに違反している場合
			{
				++self::$FailedCount;

				if( 2 <= func_num_args() ) //評価引数が渡されている場合
					{ self::$FailedArgs[] = $iArg_; }
			}
		}

		/**
			@brief     コンセプトの評価を結合する。
			@exception InvaliidArgumentException 不正な引数を指定した場合。
			@param[in] $iUnionMode_ 結合モード。
				@li and コンセプトの評価の論理積を取る。
				@li or  コンセプトの評価の論理和を取る。
		*/
		protected static function UnionJudge( $iUnionMode_ )
		{
			switch( $iUnionMode_ ) //結合方法で分岐
			{
				case 'and' :
				{
					if( self::$FailedCount ) //違反がある場合
						{ self::$IsFailed = true; }

					break;
				}

				case 'or' :
				{
					if( !self::$SuccessCount ) //適合が1つもない場合
						{ self::$IsFailed = true; }

					break;
				}

				default :
					{ throw new InvalidArgumentException( '不明な結合方法が指定されました[' . $iUnionMode_ . ']' ); }
			}

			self::$SuccessCount = 0;
			self::$FailedCount  = 0;
		}

		//■生成

		/**
			@brief  違反引数リストをメッセージ化する。
			@return メッセージ。
		*/
		private static function CreateErrorArgsMessage()
		{
			if( count( self::$FailedArgs ) ) //違反引数リストが存在する場合
			{
				$argMessages = Array();

				foreach( self::$FailedArgs as $arg ) //違反引数を処理
				{
					if( is_object( $arg ) ) //引数がオブジェクトの場合
						{ $argMessages[] = 'object(' . get_class( $arg ) . ')'; }
					else if( is_array( $arg ) ) //引数が配列の場合
						{ $argMessages[] = 'array(' . count( $arg ) . ')'; }
					else if( is_bool( $arg ) ) //引数が真偽値の場合
						{ $argMessages[] = 'boolean(' . ( $arg ? 'true' : 'false' ) . ')'; }
					else if( is_null( $arg ) ) //引数がnullの場合
						{ $argMessages[] = 'NULL'; }
					else //引数がその他の型の場合
						{ $argMessages[] = $arg; }
				}

				return '[' . join( '][' , $argMessages ) . ']';
			}
			else
				{ return ''; }

		}

		/**
			@brief     エラーメッセージを生成する。
			@param[in] $iMessage_ ベースメッセージ。
			@return    メッセージ。
		*/
		private static function CreateErrorMessage( $iMessage_ )
		{
			$message  = self::$ErrorMessage;
			$message .= self::CreateErrorArgsMessage();

			if( $iMessage_ ) //ベースメッセージが存在する場合
				{ $message .= ' : ' . $iMessage_; }

			return $message;
		}

		/**
			@brief   例外オブジェクトを生成する。
			@param   $iType_    例外の型。
			@param   $iMessage_ 例外メッセージ。
			@return  例外オブジェクト。
			@remarks $iType_ クラスが見つからない場合は Exception オブジェクトが生成されます。
		*/
		private static function CreateExceptionObject( $iType_ , $iMessage_ )
		{
			if( is_string( $iType_ ) ) //例外の型が指定されている場合
			{
				$iType_ .= 'Exception';


				if( !class_exists( $iType_ ) ) //クラスが存在しない場合
					{ $iType_ = 'Exception'; }
			}
			else //例外の型が指定されていない場合
				{ $iType_ = 'Exception'; }

			$iMessage_ = self::CreateErrorMessage( $iMessage_ );

			return new $iType_( $iMessage_ );
		}

		//■パラメータ取得

		/**
			@brief  インスタンスを取得する。
			@return ConceptBase クラスのインスタンス。
		*/
		protected static function Instance()
		{
			if( !$Instance ) //インスタンスが生成されていない場合
				{ $Instance = new ConceptBase(); }

			return $Instance;
		}

		//■パラメータ変更

		/**
			@brief     エラーメッセージを設定する。
			@param[in] $iMessage_ エラーメッセージ。
		*/
		protected static function SetErrorCaseMessage( $iMessage_ )
			{ self::$ErrorMessage = $iMessage_; }

		//■変数

		private static $Instance      = null;    ///<インスタンスを格納する変数
		private static $SuccessCount  = 0;       ///<適合コンセプトの数
		private static $FailedCount   = 0;       ///<違反コンセプトの数
		private static $IsFailed      = false;   ///<コンセプト違反フラグ
		private static $FailedArgs    = Array(); ///<違反引数リスト
		private static $ErrorMessage  = '';      ///<エラーメッセージ
	}
?>