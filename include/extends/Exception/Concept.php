<?php

	include_once './include/extends/Exception/ConceptBase.php';
	/**
		@brief コンセプトクラス。
		@details スクリプトのコンセプトを評価し、評価が真にならない場合は例外をスローします。\n
		         全ての評価メソッドは ConceptBase クラスのインスタンスを返します。\n
		         メソッドチェインを使って、評価メソッド -> OrThrow の順で呼び出してください。
		@note    評価メソッドは次の命名規則に従って定義されます。例外は IsFalse , IsAnyFalse です。
			@li IsFoo    引数が全てFooであることを評価する。
			@li IsNotFoo 引数が全てFooではないことを評価する。
			@li IsAnyFoo 引数のいずれかがFooであることを評価する。
		@author  松木 昌平
		@version 1.0
		@ingroup Utility
	*/
	class Concept extends ConceptBase
	{
		//■and評価

		/**
			@brief   全ての引数が配列であることを評価する。
			@details このメソッドは次の可変長の引数を取ります。
				@li 0~n 評価引数。
			@return  ConceptBase オブジェクト。
		*/
		static function IsArray()
		{
			$iArgs_ = func_get_args();
			parent::SetErrorCaseMessage( 'パラメータは配列ではありません' );
			return self::JudgeInType( $iArgs_ , 'array' , 'and' );
		}

		/**
			@brief   全ての引数が指定クラスのオブジェクトであることを評価する。
			@details このメソッドは次の可変長の引数を取ります。
				@li 0   クラス名。
				@li 1~n 評価引数。
			@return  ConceptBase オブジェクト。
		*/
		static function IsClass()
		{
			$iArgs_    = func_get_args();
			$className = array_shift( $iArgs_ );

			parent::ClearJudge();
			parent::SetErrorCaseMessage( 'パラメータは ' . $className . ' クラスのオブジェクトではありません' );

			foreach( $iArgs_ as $arg )
				{ parent::Judge( ( is_object( $arg ) && $className == get_class( $arg ) ) , $arg ); }

			parent::UnionJudge( 'and' );
			return parent::Instance();
		}

		/**
			@brief   全ての引数が偽であることを評価する。
			@details このメソッドは次の可変長の引数を取ります。
				@li 0~n 評価引数。
			@return  ConceptBase オブジェクト。
		*/
		static function IsFalse()
		{
			$iArgs_ = func_get_args();

			parent::ClearJudge();
			parent::SetErrorCaseMessage( 'パラメータを偽に評価できません' );

			foreach( $iArgs_ as $arg )
				{ parent::Judge( ( $arg ? false : true ) , $arg ); }

			parent::UnionJudge( 'and' );
			return parent::Instance();
		}

		/**
			@brief   全ての引数が正規表現にマッチすることを評価する。
			@details このメソッドは次の可変長の引数を取ります。
				@li 0   正規表現。
				@li 1~n 評価引数。
			@return  ConceptBase オブジェクト。
		*/
		static function IsMatch()
		{
			$iArgs_ = func_get_args();
			$regex  = array_shift( $iArgs_ );

			parent::ClearJudge();
			parent::SetErrorCaseMessage( 'パラメータは ' . $regex . ' にマッチしません' );

			foreach( $iArgs_ as $arg )
				{ parent::Judge( preg_match( $regex , $arg ) , $arg ); }

			parent::UnionJudge( 'and' );
			return parent::Instance();
		}

		/**
			@brief   全ての引数がnullであることを評価する。
			@details このメソッドは次の可変長の引数を取ります。
				@li 0~n 評価引数。
			@return  ConceptBase オブジェクト。
		*/
		static function IsNull()
		{
			$iArgs_ = func_get_args();
			parent::SetErrorCaseMessage( 'パラメータはnullではありません' );
			return self::JudgeInType( $iArgs_ , 'null' , 'and' );
		}

		/**
			@brief   全ての引数が空であることを評価する。
			@details このメソッドは次の可変長の引数を取ります。
				@li 0~n 評価引数。
			@return  ConceptBase オブジェクト。
		*/
		static function IsEmpty()
		{
			$iArgs_ = func_get_args();
			parent::SetErrorCaseMessage( 'パラメータは空ではありません' );
			return self::JudgeInType( $iArgs_ , 'empty' , 'and' );
		}

		/**
			@brief   全ての引数が数値であることを評価する。
			@details このメソッドは次の可変長の引数を取ります。
				@li 0~n 評価引数。
			@return  ConceptBase オブジェクト。
		*/
		static function IsNumeric()
		{
			$iArgs_ = func_get_args();
			parent::SetErrorCaseMessage( 'パラメータは数値ではありません' );
			return self::JudgeInType( $iArgs_ , 'numeric' , 'and' );
		}

		/**
			@brief   全ての引数がオブジェクトであることを評価する。
			@details このメソッドは次の可変長の引数を取ります。
				@li 0~n 評価引数。
			@return  ConceptBase オブジェクト。
		*/
		static function IsObject()
		{
			$iArgs_ = func_get_args();
			parent::SetErrorCaseMessage( 'パラメータはオブジェクトではありません' );
			return self::JudgeInType( $iArgs_ , 'object' , 'and' );
		}

		/**
			@brief   全ての引数がリソースであることを評価する。
			@details このメソッドは次の可変長の引数を取ります。
				@li 0~n 評価引数。
			@return  ConceptBase オブジェクト。
		*/
		static function IsResource()
		{
			$iArgs_ = func_get_args();
			parent::SetErrorCaseMessage( 'パラメータはリソースではありません' );
			return self::JudgeInType( $iArgs_ , 'resource' , 'and' );
		}

		/**
			@brief   全ての引数がスカラであることを評価する。
			@details このメソッドは次の可変長の引数を取ります。
				@li 0~n 評価引数。
			@return  ConceptBase オブジェクト。
		*/
		static function IsScalar()
		{
			$iArgs_ = func_get_args();
			parent::SetErrorCaseMessage( 'パラメータはスカラではありません' );
			return self::JudgeInType( $iArgs_ , 'scalar' , 'and' );
		}

		/**
			@brief   全ての引数が文字列であることを評価する。
			@details このメソッドは次の可変長の引数を取ります。
				@li 0~n 評価引数。
			@return  ConceptBase オブジェクト。
		*/
		static function IsString()
		{
			$iArgs_ = func_get_args();
			parent::SetErrorCaseMessage( 'パラメータは文字列ではありません' );
			return self::JudgeInType( $iArgs_ , 'string' , 'and' );
		}

		/**
			@brief   全ての引数が真であることを評価する。
			@details このメソッドは次の可変長の引数を取ります。
				@li 0~n 評価引数。
			@return  ConceptBase オブジェクト。
		*/
		static function IsTrue()
		{
			$iArgs_ = func_get_args();

			parent::ClearJudge();
			parent::SetErrorCaseMessage( 'パラメータを真に評価できません' );

			foreach( $iArgs_ as $arg )
				{ parent::Judge( ( $arg ? true : false ) , $arg ); }

			parent::UnionJudge( 'and' );
			return parent::Instance();
		}

		/**
			@brief   全ての引数が型指定のいずれかに属することを評価する。
			@details このメソッドは次の可変長の引数を取ります。
				@li 1   型指定。複数指定する場合は/で区切る。
				@li 0~n 評価引数。
			@return  ConceptBase オブジェクト。
		*/
		static function IsInType()
		{
			$iArgs_  = func_get_args();
			$typeSet = array_shift( $iArgs_ );
			parent::SetErrorCaseMessage( 'パラメータは ' . $typeSet . ' のいずれにも属しません' );
			return self::JudgeInType( $iArgs_ , $typeSet , 'and' );
		}

		//■and/not評価

		/**
			@brief   全ての引数が配列ではないことを評価する。
			@details このメソッドは次の可変長の引数を取ります。
				@li 0~n 評価引数。
			@return  ConceptBase オブジェクト。
		*/
		static function IsNotArray()
		{
			$iArgs_ = func_get_args();
			parent::SetErrorCaseMessage( 'パラメータに配列は指定できません' );
			return self::JudgeNotInType( $iArgs_ , 'array' , 'and' );
		}

		/**
			@brief   全ての引数が指定クラスのオブジェクトではないことを評価する。
			@details このメソッドは次の可変長の引数を取ります。
				@li 0   クラス名。
				@li 1~n 評価引数。
			@return  ConceptBase オブジェクト。
		*/
		static function IsNotClass()
		{
			$iArgs_    = func_get_args();
			$className = array_shift( $iArgs_ );

			parent::ClearJudge();
			parent::SetErrorCaseMessage( 'パラメータに ' . $className . ' クラスのオブジェクトは指定できません' );

			foreach( $iArgs_ as $arg )
				{ parent::Judge( !( is_object( $arg ) && $className == get_class( $arg ) ) , $arg ); }

			parent::UnionJudge( 'and' );
			return parent::Instance();
		}

		/**
			@brief   全ての引数が正規表現にマッチしないことを評価する。
			@details このメソッドは次の可変長の引数を取ります。
				@li 0   正規表現。
				@li 1~n 評価引数。
			@return  ConceptBase オブジェクト。
		*/
		static function IsNotMatch()
		{
			$iArgs_ = func_get_args();
			$regex  = array_shift( $iArgs_ );

			parent::ClearJudge();
			parent::SetErrorCaseMessage( 'パラメータに ' . $regex . ' にマッチする値は指定できません' );

			foreach( $iArgs_ as $arg )
				{ parent::Judge( !preg_match( $regex , $arg ) , $arg ); }

			parent::UnionJudge( 'and' );
			return parent::Instance();
		}

		/**
			@brief   全ての引数がnullではないことを評価する。
			@details このメソッドは次の可変長の引数を取ります。
				@li 0~n 評価引数。
			@return  ConceptBase オブジェクト。
		*/
		static function IsNotNull()
		{
			$iArgs_ = func_get_args();
			parent::SetErrorCaseMessage( 'パラメータにnullは指定できません' );
			return self::JudgeNotInType( $iArgs_ , 'null' , 'and' );
		}

		/**
			@brief   全ての引数が空ではないことを評価する。
			@details このメソッドは次の可変長の引数を取ります。
				@li 0~n 評価引数。
			@return  ConceptBase オブジェクト。
		*/
		static function IsNotEmpty()
		{
			$iArgs_ = func_get_args();
			parent::SetErrorCaseMessage( 'パラメータに空は指定できません' );
			return self::JudgeNotInType( $iArgs_ , 'empty' , 'and' );
		}
		
		/**
			@brief   全ての引数が数値ではないことを評価する。
			@details このメソッドは次の可変長の引数を取ります。
				@li 0~n 評価引数。
			@return  ConceptBase オブジェクト。
		*/
		static function IsNotNumeric()
		{
			$iArgs_ = func_get_args();
			parent::SetErrorCaseMessage( 'パラメータに数値は指定できません' );
			return self::JudgeNotInType( $iArgs_ , 'numeric' , 'and' );
		}

		/**
			@brief   全ての引数がオブジェクトではないことを評価する。
			@details このメソッドは次の可変長の引数を取ります。
				@li 0~n 評価引数。
			@return  ConceptBase オブジェクト。
		*/
		static function IsNotObject()
		{
			$iArgs_ = func_get_args();
			parent::SetErrorCaseMessage( 'パラメータにオブジェクトは指定できません' );
			return self::JudgeNotInType( $iArgs_ , 'object' , 'and' );
		}

		/**
			@brief   全ての引数がリソースではないことを評価する。
			@details このメソッドは次の可変長の引数を取ります。
				@li 0~n 評価引数。
			@return  ConceptBase オブジェクト。
		*/
		static function IsNotResource()
		{
			$iArgs_ = func_get_args();
			parent::SetErrorCaseMessage( 'パラメータにリソースは指定できません' );
			return self::JudgeNotInType( $iArgs_ , 'resource' , 'and' );
		}

		/**
			@brief   全ての引数がスカラではないことを評価する。
			@details このメソッドは次の可変長の引数を取ります。
				@li 0~n 評価引数。
			@return  ConceptBase オブジェクト。
		*/
		static function IsNotScalar()
		{
			$iArgs_ = func_get_args();
			parent::SetErrorCaseMessage( 'パラメータにスカラは指定できません' );
			return self::JudgeNotInType( $iArgs_ , 'scalar' , 'and' );
		}

		/**
			@brief   全ての引数が文字列ではないことを評価する。
			@details このメソッドは次の可変長の引数を取ります。
				@li 0~n 評価引数。
			@return  ConceptBase オブジェクト。
		*/
		static function IsNotString()
		{
			$iArgs_ = func_get_args();
			parent::SetErrorCaseMessage( 'パラメータに文字列は指定できません' );
			return self::JudgeNotInType( $iArgs_ , 'string' , 'and' );
		}

		/**
			@brief   全ての引数が型指定のいずれかにも属さないことを評価する。
			@details このメソッドは次の可変長の引数を取ります。
				@li 1   型指定。複数指定する場合は/で区切る。
				@li 0~n 評価引数。
			@return  ConceptBase オブジェクト。
		*/
		static function IsNotInType()
		{
			$iArgs_  = func_get_args();
			$typeSet = array_shift( $iArgs_ );
			parent::SetErrorCaseMessage( 'パラメータに ' . $typeSet . ' に属する値は指定できません' );
			return self::JudgeNotInType( $iArgs_ , $typeSet , 'and' );
		}

		//■or評価

		/**
			@brief   引数のいずれかが配列であることを評価する。
			@details このメソッドは次の可変長の引数を取ります。
				@li 0~n 評価引数。
			@return  ConceptBase オブジェクト。
		*/
		static function IsAnyArray()
		{
			$iArgs_ = func_get_args();
			parent::SetErrorCaseMessage( 'パラメータのいずれも配列ではありません' );
			return self::JudgeInType( $iArgs_ , 'array' , 'or' );
		}

		/**
			@brief   引数のいずれかが指定クラスのオブジェクトであることを評価する。
			@details このメソッドは次の可変長の引数を取ります。
				@li 0   クラス名。
				@li 1~n 評価引数。
			@return  ConceptBase オブジェクト。
		*/
		static function IsAnyClass()
		{
			$iArgs_    = func_get_args();
			$className = array_shift( $iArgs_ );

			parent::ClearJudge();
			parent::SetErrorCaseMessage( 'パラメータのいずれも ' . $className . ' クラスのオブジェクトではありません' );

			foreach( $iArgs_ as $arg )
				{ parent::Judge( ( is_object( $arg ) && $className == get_class( $arg ) ) , $arg ); }

			parent::UnionJudge( 'or' );
			return parent::Instance();
		}

		/**
			@brief   引数のいずれかが偽であることを評価する。
			@details このメソッドは次の可変長の引数を取ります。
				@li 0~n 評価引数。
			@return  ConceptBase オブジェクト。
		*/
		static function IsAnyFalse()
		{
			$iArgs_ = func_get_args();

			parent::ClearJudge();
			parent::SetErrorCaseMessage( 'パラメータのいずれも偽に評価できません' );

			foreach( $iArgs_ as $arg )
				{ parent::Judge( ( $arg ? false : true ) , $arg ); }

			parent::UnionJudge( 'or' );
			return parent::Instance();
		}

		/**
			@brief   引数のいずれかが正規表現にマッチすることを評価する。
			@details このメソッドは次の可変長の引数を取ります。
				@li 0   正規表現。
				@li 1~n 評価引数。
			@return  ConceptBase オブジェクト。
		*/
		static function IsAnyMatch()
		{
			$iArgs_ = func_get_args();
			$regex  = array_shift( $iArgs_ );

			parent::ClearJudge();
			parent::SetErrorCaseMessage( 'パラメータのいずれも ' . $regex . ' にマッチしません' );

			foreach( $iArgs_ as $arg )
				{ parent::Judge( preg_match( $regex , $arg ) , $arg ); }

			parent::UnionJudge( 'or' );
			return parent::Instance();
		}

		/**
			@brief   引数のいずれかがnullであることを評価する。
			@details このメソッドは次の可変長の引数を取ります。
				@li 0~n 評価引数。
			@return  ConceptBase オブジェクト。
		*/
		static function IsAnyNull()
		{
			$iArgs_ = func_get_args();
			parent::SetErrorCaseMessage( 'パラメータのいずれもnullではありません' );
			return self::JudgeInType( $iArgs_ , 'null' , 'or' );
		}

		/**
			@brief   引数のいずれかがnull空評価する。
			@details このメソッドは次の可変長の引数を取ります。
				@li 0~n 評価引数。
			@return  ConceptBase オブジェクト。
		*/
		static function IsAnyEmpty()
		{
			$iArgs_ = func_get_args();
			parent::SetErrorCaseMessage( 'パラメータのいずれも空ではありません' );
			return self::JudgeInType( $iArgs_ , 'empty' , 'or' );
		}

		/**
			@brief   引数のいずれかが数値であることを評価する。
			@details このメソッドは次の可変長の引数を取ります。
				@li 0~n 評価引数。
			@return  ConceptBase オブジェクト。
		*/
		static function IsAnyNumeric()
		{
			$iArgs_ = func_get_args();
			parent::SetErrorCaseMessage( 'パラメータのいずれも数値ではありません' );
			return self::JudgeInType( $iArgs_ , 'numeric' , 'or' );
		}

		/**
			@brief   引数のいずれかがオブジェクトであることを評価する。
			@details このメソッドは次の可変長の引数を取ります。
				@li 0~n 評価引数。
			@return  ConceptBase オブジェクト。
		*/
		static function IsAnyObject()
		{
			$iArgs_ = func_get_args();
			parent::SetErrorCaseMessage( 'パラメータのいずれもオブジェクトではありません' );
			return self::JudgeInType( $iArgs_ , 'object' , 'or' );
		}

		/**
			@brief   引数のいずれかがリソースであることを評価する。
			@details このメソッドは次の可変長の引数を取ります。
				@li 0~n 評価引数。
			@return  ConceptBase オブジェクト。
		*/
		static function IsAnyResource()
		{
			$iArgs_ = func_get_args();
			parent::SetErrorCaseMessage( 'パラメータのいずれもリソースではありません' );
			return self::JudgeInType( $iArgs_ , 'resource' , 'or' );
		}

		/**
			@brief   引数のいずれかがスカラであることを評価する。
			@details このメソッドは次の可変長の引数を取ります。
				@li 0~n 評価引数。
			@return  ConceptBase オブジェクト。
		*/
		static function IsAnyScalar()
		{
			$iArgs_ = func_get_args();
			parent::SetErrorCaseMessage( 'パラメータのいずれもスカラではありません' );
			return self::JudgeInType( $iArgs_ , 'string' , 'or' );
		}

		/**
			@brief   引数のいずれかが文字列であることを評価する。
			@details このメソッドは次の可変長の引数を取ります。
				@li 0~n 評価引数。
			@return  ConceptBase オブジェクト。
		*/
		static function IsAnyString()
		{
			$iArgs_ = func_get_args();
			parent::SetErrorCaseMessage( 'パラメータのいずれも文字列ではありません' );
			return self::JudgeInType( $iArgs_ , 'string' , 'or' );
		}

		/**
			@brief   引数のいずれかが真であることを評価する。
			@details このメソッドは次の可変長の引数を取ります。
				@li 0~n 評価引数。
			@return  ConceptBase オブジェクト。
		*/
		static function IsAnyTrue()
		{
			$iArgs_ = func_get_args();

			parent::ClearJudge();
			parent::SetErrorCaseMessage( 'パラメータのいずれも真に評価できません' );

			foreach( $iArgs_ as $arg )
				{ parent::Judge( ( $arg ? true : false ) , $arg ); }

			parent::UnionJudge( 'or' );
			return parent::Instance();
		}

		/**
			@brief   引数のいずれかが型指定のいずれかに属することを評価する。
			@details このメソッドは次の可変長の引数を取ります。
				@li 1   型指定。複数指定する場合は/で区切る。
				@li 0~n 評価引数。
			@return  ConceptBase オブジェクト。
		*/
		static function IsAnyInType()
		{
			$iArgs_  = func_get_args();
			$typeSet = array_shift( $iArgs_ );
			parent::SetErrorCaseMessage( 'パラメータのいずれも ' . $typeSet . ' のいずれにも属しません' );
			return self::JudgeInType( $iArgs_ , $typeSet , 'or' );
		}

		/**
			@brief   引数のいずれかがnullであることを評価する。
			@details このメソッドは次の可変長の引数を取ります。
				@li 0~n 評価引数。
			@return  ConceptBase オブジェクト。
		*/
		static function IsAnyNotNull()
		{
			$iArgs_ = func_get_args();
			parent::SetErrorCaseMessage( 'パラメータが全てnullです' );
			return self::JudgeNotInType( $iArgs_ , 'null' , 'or' );
		}

		/**
			@brief   引数のいずれかがnullであることを評価する。
			@details このメソッドは次の可変長の引数を取ります。
				@li 0~n 評価引数。
			@return  ConceptBase オブジェクト。
		*/
		static function IsAnyNotEmpty()
		{
			$iArgs_ = func_get_args();
			parent::SetErrorCaseMessage( 'パラメータが全て空です' );
			return self::JudgeNotInType( $iArgs_ , 'empty' , 'or' );
		}

		//■評価

		/**
			@brief     引数が型指定のいずれかに属することを評価する。
			@exception InvalidArgumentException 型指定に不明な型が含まれる場合。
			@param[in] $iArgs_      引数リスト。
			@param[in] $iTypeSet_   型指定。
			@param[in] $iUnionMode_ 評価の結合方法。
			@return    ConceptBase オブジェクト。
		*/
		private static function JudgeInType( $iArgs_ , $iTypeSet_ , $iUnionMode_ )
		{
			parent::ClearJudge();

			$typeSet = explode( '/' , $iTypeSet_ );

			foreach( $iArgs_ as &$arg ) //引数を処理
			{
				$result = false;

				foreach( $typeSet as $type ) //型指定を処理
				{
					switch( $type ) //型指定で分岐
					{
						case 'array' : //配列
						{
							$result |= is_array( $arg );
							break;
						}

						case 'bool' : //真偽値
						{
							$result |= is_bool( $arg );
							break;
						}

						case 'null' : //null
						{
							$result |= is_null( $arg );
							break;
						}

						case 'numeric' : //数値
						{
							$result |= is_numeric( $arg );
							break;
						}

						case 'object' : //オブジェクト
						{
							$result |= is_object( $arg );
							break;
						}

						case 'resource' : //リソース
						{
							$result |= is_resource( $arg );
							break;
						}

						case 'scalar' : //スカラ
						{
							$result |= is_scalar( $arg );
							break;
						}

						case 'string' : //文字列
						{
							$result |= is_string( $arg );
							break;
						}

						case 'empty' : //空
						{
							$result |= empty( $arg );
							break;
						}

						default : //不明な指定
							{ throw new InvalidArgumentException( '不明な型指定が含まれています : ' . $iTypeSet_ ); }
					}

					if( $result ) //結果が確定した場合
						{ break; }
				}

				parent::Judge( $result , $arg );
			}

			parent::UnionJudge( $iUnionMode_ );
			return parent::Instance();
		}

		/**
			@brief     引数が型指定のいずれにも属さないことを評価する。
			@exception InvalidArgumentException 型指定に不明な型が含まれる場合。
			@param[in] $iArgs_      引数リスト。
			@param[in] $iTypeSet_   型指定。
			@param[in] $iUnionMode_ 評価の結合方法。
			@return    ConceptBase オブジェクト。
		*/
		private static function JudgeNotInType( $iArgs_ , $iTypeSet_ , $iUnionMode_ )
		{
			parent::ClearJudge();

			$typeSet = explode( '/' , $iTypeSet_ );

			foreach( $iArgs_ as &$arg ) //引数を処理
			{
				$result = true;

				foreach( $typeSet as $type ) //型指定を処理
				{
					switch( $type ) //型指定で分岐
					{
						case 'array' : //配列
						{
							$result &= !is_array( $arg );
							break;
						}

						case 'bool' : //真偽値
						{
							$result &= !is_bool( $arg );
							break;
						}


						case 'null' : //null
						{
							$result &= !is_null( $arg );
							break;
						}

						case 'numeric' : //数値
						{
							$result &= !is_numeric( $arg );
							break;
						}

						case 'object' : //オブジェクト
						{
							$result &= !is_object( $arg );
							break;
						}

						case 'resource' : //リソース
						{
							$result &= !is_resource( $arg );
							break;
						}

						case 'scalar' : //スカラ
						{
							$result &= !is_scalar( $arg );
							break;
						}

						case 'string' : //文字列
						{
							$result &= !is_string( $arg );
							break;
						}

						case 'empty' : //空
						{
							$result &= !empty( $arg );
							break;
						}

						default : //不明な指定
							{ throw new InvalidArgumentException( '不明な型指定が含まれています : ' . $typeSet ); }
					}

					if( !$result ) //結果が確定した場合
						{ break; }
				}

				parent::Judge( $result , $arg );
			}

			parent::UnionJudge( $iUnionMode_ );
			return parent::Instance();
		}
	}
?>