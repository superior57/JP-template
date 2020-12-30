<?php

	//★クラス //

	/**
		@brief   既定の汎用データ検索フォームのモデル。
	*/
	class AppclipSearchModel extends AppSearchModel //
	{
		//■処理 //

		/**
			@brief テーブルを検索する。
		*/
		function doSearch() //
		{
			global $gm;
			global $magic_quotes_gpc;

			$this->search = new Search( $this->gm , $this->type );
			$searchParam  = $_GET;

			if( !$magic_quotes_gpc && 'sjis' == $this->db->char_code ) //文字化け対策が必要な環境の場合
				{ $searchParam = addslashes_deep( $searchParam ); }

			$this->search->setParamertorSet( $searchParam );
			$this->sys->searchResultProc( $gm , $this->search , $this->loginUserType , $this->loginUserRank );

			$this->table = $this->search->getResult();

			$this->sys->searchProc( $gm , $this->table , $this->loginUserType , $this->loginUserRank );

			if( $this->paldb->existsRow( $this->table ) ) //検索結果がある場合
				{ $this->hasSearchResult = true; }
		}

		/**
			@brief 問い合わせパラメータを初期化する。
		*/
		function initializeInquiryParameter() //
		{
			$_GET[ 'type' ]  = 'inquiry';
			$_GET[ 'items' ] = implode( '/' , $_GET[ 'items' ] );
		}

		//■データ取得 //

		/**
			@brief  検索結果があるか確認する。
			@retval true  検索結果がある場合。
			@retval false 検索結果がない場合。
		*/
		function hasSearchResult() //
			{ return $this->hasSearchResult; }

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
			$this->paldb = GMList::getDB($_GET["pal"]);
		}

		//■変数 //
		var     $type            = null;  ///<ターゲットタイプ。
		var     $sys             = null;  ///<Systemインスタンス。
		var     $db              = null;  ///<DBインスタンス。
		var     $paldb              = null;  ///クリップ対象のDBインスタンス。
		var     $table           = null;  ///<検索結果のテーブル。
		private $hasSearchResult = false; ///<検索結果がある場合はtrue。
	}
