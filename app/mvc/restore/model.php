<?php

	//★クラス //

	/**
		@brief   既定の汎用データ復元フォームのモデル。
	*/
	class AppRestoreModel extends AppBaseModel //
	{
		//■処理 //

		/**
			@brief 削除されたレコードを元に戻す。
		*/
		function doRestore() //
		{
			global $gm;

			$newRec = $this->db->restoreRecord( $this->rec );

			$sys->restoreComp( $gm , $newRec , $this->rec , $this->loginUserType , $this->loginUserRank );

			$this->rec = $newRec;
		}

		//■コンストラクタ・デストラクタ //

		/**
			@brief コンストラクタ。
		*/
		function __construct() //
		{
			parent::__construct();

			$this->type = $_GET[ 'type' ];
			$this->sys  = SystemUtil::getSystem( $this->type );
			$this->db   = $this->gm->getDB();
			$this->rec  = System::setPageRecord( $this->db , 'delete' );

			ConceptSystem::CheckRecord( $rec )->OrThrow( 'RecordNotFound' );

			System::$checkData = new CheckData( $gm , true , $this->loginUserType , $this->loginUserRank , $this->type );
		}

		//■変数 //
		var $type = null;  ///<ターゲットタイプ。
		var $sys  = null;  ///<Systemインスタンス。
		var $db   = null;  ///<DBインスタンス。
	}
