<?php

	//★クラス //

	/**
		@brief 既定の詳細情報ページのモデル。
	*/
	class AppPreviewModel extends AppBaseModel //
	{
		//■コンストラクタ・デストラクタ //

		/**
			@brief コンストラクタ。
		*/
		function __construct() //
		{
			parent::__construct();

			$this->loginUserType = 'nobody';
			$this->loginUserRank = $ACTIVE_ACTIVATE;

			$this->sys = SystemUtil::getSystem( $_GET[ 'type' ] );
			$this->db  = $this->gm->getDB();
			$this->rec = $this->sys->setPreviewRecord( $this->db , $table_type , $_GET[ 'mode' ] );

			SystemUtil::checkTableOwner( $_GET[ 'type' ], $this->db , $this->rec );

			System::$checkData = new CheckData( $gm , false , $this->loginUserType , $this->loginUserRank , $_GET[ 'type' ] );
		}

		//■変数 //
		var     $sys     = null;  ///<システムインスタンス。
		var     $db      = null;  ///<DBインスタンス。
		private $canView = false; ///<閲覧権限がある場合はtrue。
	}
