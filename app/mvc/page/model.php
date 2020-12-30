<?php

	//★クラス //

	/**
		@brief   既定の一般ページのモデル。
	*/
	class AppPageModel extends AppBaseModel //
	{
		//■処理 //

		/**
			@brief ページを検索する。
		*/
		function searchPage() //
		{
			$this->table = $this->db->getTable();
			$this->table = $this->db->searchTable( $this->table , 'name'      , '=' , ( string )$_GET[ 'p' ] );
			$this->table = $this->db->searchTable( $this->table , 'authority' , '=' , '%' . $this->loginUserType . '%' );
			$this->table = $this->db->searchTable( $this->table , 'open'      , '= ', true );

			$this->hasSearchResult = $this->db->existsRow( $this->table );
		}

		/**
			@brief プレビュー用のページを検索する。
		*/
		function searchPreviewPage() //
		{
			$this->table = $this->db->getTable();
			$this->table = $this->db->searchTable( $this->table , 'id' , '==' , ( string )$_GET[ 'id' ] );

			$this->hasSearchResult = $this->db->existsRow( $this->table );
		}

		/**
			@brief プレビュー一覧を検索する。
		*/
		function searchPreviewList() //
		{
			$this->table = $this->db->getTable();

			if( isset( $_GET[ 'p' ] ) ) //名前で指定されている場合
				{ $this->table = $this->db->searchTable( $this->table , 'name' , '=' , ( string )$_GET[ 'p' ] ); }
			else if( isset( $_GET[ 'id' ] ) ) //IDで指定されている場合
				{ $this->table = $this->db->searchTable( $this->table , 'id' , '==' , ( string )$_GET[ 'id' ] ); }
			else //指定がない場合
				{ $this->table = $this->db->getEmptyTable(); }

			$this->hasSearchResult = $this->db->existsRow( $this->table );
		}

		//■データ取得 //

		/**
			@brief  ページが見つかったか確認する。
			@retval true  ページが見つかった場合。
			@retval false ページが見つからない場合。
		*/
		function hasSearchResult() //
			{ return $this->hasSearchResult; }

		//■コンストラクタ・デストラクタ //

		/**
			@brief コンストラクタ。
		*/
		function __construct() //
		{
			$this->gm = GMList::getGM( 'page' );
			$this->db = GMList::getDB( 'page' );

			parent::__construct();
		}

		//■変数 //
		var     $db              = null;  ///<DBインスタンス。
		var     $table           = null;  ///<テーブル。
		private $hasSearchResult = false; ///<ページが見つかった場合はtrue。
	}
