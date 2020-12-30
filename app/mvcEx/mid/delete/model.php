<?php

	//★クラス //

	/**
		@brief   既定のデータ削除処理のモデル。
	*/
	class AppmidDeleteModel extends AppDeleteModel //
	{
		//■処理 //

		/**
			@brief 削除処理を実行する。
		*/
		function doDelete() //
		{
			global $gm;
			global $CONF_FEED_ENABLE;
			global $CONF_FEED_TABLES;

			$this->sys->deleteProc( $gm , $this->rec , $this->loginUserType , $this->loginUserRank );
			$this->sys->deleteComp( $gm , $this->rec , $this->loginUserType , $this->loginUserRank );

			if( $CONF_FEED_ENABLE && in_array( $this->type , $CONF_FEED_TABLES ) ) //feed更新設定が有効な場合
				{ SystemUtil::async( 'FeedApi' , 'update' , array( 'targetType' => $this->type ) ); }

			$this->succeededDelete = true;
		}

		/**
			@brief 入力内容を検証する。
		*/
		function verifyInput() //
		{
			if( $this->sys->deleteCheck( $gm , $this->rec , $this->loginUserType , $this->loginUserRank ) ) //入力に問題がない場合
				{ $this->noInputError = true; }
		}

		/**
			@brief トークンの有効性を検証する。
		*/
		function verifyToken() //
			{ ConceptSystem::CheckAuthenticityToken()->OrThrow( 'IllegalTokenAccess' ); }

		//■データ取得 //

		/**
			@brief  削除可能な状態か確認する。
			@retval true  削除可能である場合。
			@retval false 削除可能ではない場合。
		*/
		function canDelete() //
			{ return $this->noInputError; }

		/**
			@brief  削除処理が成功したか確認する。
			@retval true  成功した場合。
			@retval false 失敗した場合。
		*/
		function succeededDelete() //
			{ return $this->succeededDelete; }

		//■コンストラクタ・デストラクタ //

		/**
			@brief コンストラクタ。
		*/
		function __construct() //
		{
			global $gm;

			parent::__construct();

		    if( 'admin' == $this->loginUserType ) //管理者でログインしている場合
				{ $tableType = 'all'; }

			$this->type = $_GET[ 'type' ];
			$this->sys  = SystemUtil::getSystem( $this->type );
			$this->db   = $this->gm->getDB();
			$this->rec  = System::setPageRecord( $this->db , $tableType );

			ConceptSystem::CheckTableEditUser( $this->db , $this->rec )->OrThrow( 'IllegalAccess' );

			System::$checkData = new CheckData( $gm , false , $this->loginUserType , $this->loginUserRank , $this->type );
		}

		//■変数 //
		var     $type            = null;  ///<ターゲットタイプ。
		var     $sys             = null;  ///<Systemインスタンス。
		var     $db              = null;  ///<DBインスタンス。
		private $noInputError    = false; ///<入力に問題がない場合はtrue。
		private $canDelete       = false; ///<削除処理が実行可能な場合はtrue。
		private $succeededDelete = false; ///<削除処理に成功した場合はtrue。
	}
