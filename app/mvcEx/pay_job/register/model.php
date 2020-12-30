<?php

	//★クラス //

	/**
		@brief   既定の汎用データ登録ページのモデル。
	*/
	class Apppay_jobRegisterModel extends AppRegisterModel //
	{
		//■処理 //

		/**
			@brief 登録内容確認に関する処理を実行する。
		*/
		function doConfirm() //
		{
			global $gm;

			$this->sys->registProc( $gm , $this->rec , $this->loginUserType , $this->loginUserRank , true );
		}

		/**
			@brief 登録処理を実行する。
		*/
		function doRegister() //
		{
			global $gm;
			global $CONF_FEED_ENABLE;
			global $CONF_FEED_TABLES;

			if( !$this->canRegister() ) //登録できる状態ではない場合
				{ return; }

			if( !$this->sys->registCompCheck( $gm , $this->rec , $this->loginUserType , $this->loginUserRank ) ) //入力内容に問題がある場合
				{ return; }

			$this->sys->registProc( $gm , $this->rec , $this->loginUserType , $this->loginUserRank );
			$this->db->addRecord( $this->rec );
			$this->sys->registComp( $gm , $this->rec , $this->loginUserType , $this->loginUserRank );

			if( $CONF_FEED_ENABLE && in_array( $this->type , $CONF_FEED_TABLES ) ) //feed更新設定が有効な場合
				{ SystemUtil::async( 'FeedApi' , 'update' , array(  'targetType' => $this->type ) ); }

			$this->succeededRegister = true;
		}

		/**
			@brief 前の画面に戻る。
		*/
		function goBack() //
		{
			if( 2 <= $_POST[ 'step' ] ) //手順が2以上の場合
				{ --$_POST[ 'step' ]; }
		}

		/**
			@brief 次の画面に進む。
		*/
		function goForward() //
		{
			if( $this->noInputError ) //入力内容に問題がない場合
				{ ++$_POST[ 'step' ]; }
		}

		/**
			@brief クエリを初期化する。
		*/
		function initializeQuery() //
		{
			if( $this->sys->copyCheck( $gm , $this->loginUserType , $this->loginUserRank ) ) //コピー元の指定がある場合
				{ $this->rec = $this->db->selectRecord( $_GET[ 'cp' ] ); }
			else //コピー元の指定がない場合
				{ $this->rec = $this->db->getNewRecord( $_GET ); }
		}

		/**
			@brief クエリを更新する。
		*/
		function updateQuery() //
			{ $this->rec = $this->db->getNewRecord( $_POST ); }

		/**
			@brief 入力内容を検証する。
		*/
		function verifyInput() //
		{
			global $gm;

			if( $this->sys->registCheck( $gm , false , $this->loginUserType , $this->loginUserRank ) ) //入力に問題がない場合
				{ $this->noInputError = true; }
		}

		/**
			@brief トークンの有効性を検証する。
		*/
		function verifyToken() //
			{ ConceptSystem::CheckAuthenticityToken()->OrThrow( 'IllegalTokenAccess' ); }

		//■データ取得 //

		/**
			@brief  登録できる状態か確認する。
			@retval true  登録できる状態の場合。
			@retval false 登録できる状態ではない場合。
		*/
		function canRegister() //
		{
			if( !$this->noInputError ) //入力内容に問題がある場合
				{ return false; }

			if( $this->gm->maxStep > $_POST[ 'step' ] ) //最終手順まで完了していない場合
				{ return false; }

			return true;
		}

		/**
			@brief  登録が成功したか確認する。
			@retval true  登録に成功した場合。
			@retval false 登録に失敗した場合。
		*/
		function succeededRegister() //
			{ return $this->succeededRegister; }

		//■コンストラクタ・デストラクタ //

		/**
			@brief コンストラクタ。
		*/
		function __construct() //
		{
			global $gm;

			parent::__construct();

			$this->type = $_GET[ 'type' ];
			$this->sys  = SystemUtil::getSystem( $this->type );
			$this->gm   = $gm[ $this->type ];
			$this->db   = $this->gm->getDB();

			System::$checkData = new CheckData( $gm , false , $this->loginUserType , $this->loginUserRank , $this->type );
		}

		//■変数 //
		var     $type              = null;  ///<ターゲットタイプ。
		var     $sys               = null;  ///<Systemインスタンス。
		var     $db                = null;  ///<DBインスタンス。
		private $noInputError      = false; ///<入力に問題がない場合はtrue。
		private $canRegister       = false; ///<登録処理が実行可能な場合はtrue。
		private $succeededRegister = false; ///<登録処理に成功した場合はtrue。
	}
