<?php

	//★クラス //

	/**
		@brief 既定の詳細情報ページのモデル。
	*/
	class AppnobodyInfoModel extends AppBaseModel //
	{
		//■処理 //

		/**
			@brief 簡易更新処理を実行する。
		*/
		function doQuickUpdate() //
		{
			global $gm;

			if( 'true' == $_POST[ 'post' ] ) //処理要求がある場合
			{
				ConceptSystem::CheckAuthenticityToken()->OrThrow( 'IllegalTokenAccess' );

				$this->sys->infoProc( $gm , $this->rec , $this->loginUserType , $this->loginUserRank );
			}

			$this->sys->doInfo( $gm , $this->rec , $this->loginUserType , $this->loginUserRank );
		}

		/**
			@brief 情報の閲覧権限を検証する。
		*/
		function verifyViewAuthority() //
		{
			global $gm;

			$this->canView = $this->sys->infoCheck( $gm , $this->rec , $this->loginUserType , $this->loginUserRank );
		}

		//■データ取得 //

		/**
			@brief 情報の閲覧権限があるか確認する。
			@retval true  閲覧権限がある場合。
			@retval false 閲覧権限がない場合。
		*/
		function canView() //
			{ return $this->canView; }

		//■コンストラクタ・デストラクタ //

		/**
			@brief コンストラクタ。
		*/
		function __construct() //
		{
			parent::__construct();

		    if( 'admin' == $this->loginUserType ) //管理者でログインしている場合
				{ $tableType = 'all'; }

			$this->sys = SystemUtil::getSystem( $_GET[ 'type' ] );
			$this->db  = $this->gm->getDB();
			$this->rec = System::setPageRecord( $this->db , $table_type );

			SystemUtil::checkTableOwner( $_GET[ 'type' ], $this->db , $this->rec );

			System::$checkData = new CheckData( $gm , false , $this->loginUserType , $this->loginUserRank , $_GET[ 'type' ] );
		}

		//■変数 //
		var     $sys     = null;  ///<システムインスタンス。
		var     $db      = null;  ///<DBインスタンス。
		private $canView = false; ///<閲覧権限がある場合はtrue。
	}
