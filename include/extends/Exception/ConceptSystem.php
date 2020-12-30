<?php

	include_once './include/extends/Exception/Concept.php';
	/**
		@brief システムコンセプトクラス。
		@details フレームワーク固有の例外チェック用Utilityクラス\n
		       使い方はConceptクラスに順序。
		@author  koichiro yoshioka
		@version 1.0
		@ingroup Utility
	*/
	class ConceptSystem extends Concept
	{
		/**
			@brief   post_max_size をオーバーしていないかを評価する。
			@details このメソッドは次の可変長の引数を取ります。
				@li 0~n 評価引数。
			@return  ConceptBase オブジェクト。
		*/
		static function CheckPostMaxSizeOrver()
		{
			$iArgs_ = func_get_args();
			parent::SetErrorCaseMessage( 'uploadされたファイルがpost_max_sizeを越えています。' );
			parent::Judge( !(empty($_FILES) && empty($_POST) && isset($_SERVER['REQUEST_METHOD']) && strtolower($_SERVER['REQUEST_METHOD']) == 'post') , $iArgs_ );
			parent::UnionJudge( 'and' );
			return parent::Instance();
		}
		
		/**
			@brief   getで指定されたtypeのテーブルが存在するを評価する。
			@details このメソッドは次の可変長の引数を取ります。
				@li 0~n 評価引数。
			@return  ConceptBase オブジェクト。
		*/
		static function CheckAuthenticityToken()
		{
			$iArgs_ = func_get_args();
			parent::SetErrorCaseMessage( 'アクセストークンが無効です。' );
			parent::Judge( SystemUtil::checkAuthenticityToken( $_POST['authenticity_token'] ) , $iArgs_ );
			parent::UnionJudge( 'and' );
			return parent::Instance();
		}
		
		/**
			@brief   getで指定されたtypeのテーブルが存在するを評価する。
			@details このメソッドは次の可変長の引数を取ります。
				@li 0~n 評価引数。
			@return  ConceptBase オブジェクト。
		*/
		static function CheckType()
		{
			global $TABLE_NAME;
			
			$iArgs_ = func_get_args();
			parent::SetErrorCaseMessage( $_GET[ 'type' ] . 'は定義されていません' );
			parent::Judge( in_array(  $_GET[ 'type' ], $TABLE_NAME ) , $iArgs_ );
			parent::UnionJudge( 'and' );
			return parent::Instance();
		}
		/**
			@brief   getで指定されたテーブルへの項目の作成権限を持っているかを評価する。
			@details このメソッドは次の可変長の引数を取ります。
				@li 0~n 評価引数。
			@return  ConceptBase オブジェクト。
		*/
		static function CheckTableRegistUser()
		{
			$iArgs_ = func_get_args();
			parent::SetErrorCaseMessage( $_GET[ 'type' ] . 'へのレコード作成権限がありません。' );
			parent::Judge( SystemUtil::checkTableRegistUser( $_GET['type'] ) , $iArgs_ );
			parent::UnionJudge( 'and' );
			return parent::Instance();
		}
		/**
			@brief   getで指定されたテーブルへの項目の編集権限を持っているかを評価する。
			@details このメソッドは次の可変長の引数を取ります。
				@li 0~n 評価引数。
			@return  ConceptBase オブジェクト。
		*/
		static function CheckTableEditUser()
		{
			$iArgs_ = func_get_args();
			parent::SetErrorCaseMessage( $_GET[ 'type' ] . 'のレコード編集権限がありません。' );
			parent::Judge( SystemUtil::checkTableEditUser( $_GET['type'], $iArgs_[0], $iArgs_[1] ) , $iArgs_ );
			parent::UnionJudge( 'and' );
			return parent::Instance();
		}
	
		/**
			@brief   getで指定されたテーブルがサイト上からの変更が許可されたテーブルかどうかを評価する。
			@details このメソッドは次の可変長の引数を取ります。
				@li 0~n 評価引数。
			@return  ConceptBase オブジェクト。
		*/
		static function CheckTableNoHTML()
		{
			global $THIS_TABLE_IS_NOHTML;
			
			$iArgs_ = func_get_args();
			parent::SetErrorCaseMessage( $_GET[ 'type' ] . 'は操作できません' );
			parent::Judge( !isset($THIS_TABLE_IS_NOHTML[ $_GET[ 'type' ] ]) || !$THIS_TABLE_IS_NOHTML[ $_GET[ 'type' ] ] , $iArgs_ );
			parent::UnionJudge( 'and' );
			return parent::Instance();
		}
	
		/**
			@brief   スクリプトで評価するレコードが存在するかどうかを評価する。
			@details このメソッドは次の可変長の引数を取ります。
				@li 0~n 評価引数。
			@return  ConceptBase オブジェクト。
		*/
		static function CheckRecord()
		{
			$iArgs_ = func_get_args();
			$rec  = array_shift( $iArgs_ );
			parent::SetErrorCaseMessage( '対象となるレコードがありません。' );
			parent::Judge(  isset($rec)  , $iArgs_ );
			parent::UnionJudge( 'and' );
			return parent::Instance();
		}
	
		/**
			@brief   getで指定されたテーブルがユーザーテーブルかどうかを評価する。
			@details このメソッドは次の可変長の引数を取ります。
				@li 0~n 評価引数。
			@return  ConceptBase オブジェクト。
		*/
		static function CheckThisUserTable()
		{
			global $THIS_TABLE_IS_USERDATA;
			
			$iArgs_ = func_get_args();
			parent::SetErrorCaseMessage( $_GET[ 'type' ] . 'はユーザーテーブルではありません' );
			parent::Judge(  $THIS_TABLE_IS_USERDATA[ $_GET[ 'type' ] ] , $iArgs_ );
			parent::UnionJudge( 'and' );
			return parent::Instance();
		}
		
		/**
			@brief   getで指定されたテーブルへの項目の編集権限を持っているかを評価する。
			@details このメソッドは次の可変長の引数を取ります。
				@li 0~n 評価引数。
			@return  ConceptBase オブジェクト。
		*/
		static function CheckLoginType()
		{
			global $loginUserType;
			
			$iArgs_ = func_get_args();
			$checkType_  = array_shift( $iArgs_ );
			parent::SetErrorCaseMessage( $checkType_ . 'のみがアクセス可能です。' );
			parent::Judge(  $loginUserType == $checkType_ , $iArgs_ );
			parent::UnionJudge( 'and' );
			return parent::Instance();
		}
	}
?>