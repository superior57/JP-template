<?php

	//★クラス //

	/**
		@brief 既定の汎用データ検索フォームのコントローラ。
	*/
	class AppmidSearchController extends AppSearchController //
	{
		//■処理 //

		/**
			@brief 問い合わせ要求への応答。
		*/
		function doInquiry() //
		{
			$this->model->initializeInquiryParameter();

			$this->view->drawInquiryPage( $this->model );
		}

		/**
			@brief 検索フォーム表示要求への応答。
		*/
		function searchForm() //
			{ $this->view->drawSearchFormPage( $this->model ); }

		/**
			@brief 検索実行要求への応答。
		*/
		function doSearch() //
		{
			$this->model->doSearch();

			if( $this->model->hasSearchResult() ) //検索結果がある場合
				{ $this->view->drawSearchResultPage( $this->model ); }
			else //検索結果がない場合
				{ $this->view->drawSearchResultEmptyPage( $this->model ); }
		}

		//■コンストラクタ・デストラクタ //

		/**
			@brief コンストラクタ。
		*/
		function __construct() //
		{
			global $gm;

			ConceptSystem::IsNotNull( $_GET[ 'type' ] )->OrThrow( 'InvalidQuery' );
			ConceptSystem::CheckType()->OrThrow( 'InvalidQuery' );

			Concept::IsTrue(JobLogic::isHandle($_GET['type']))->OrThrow("IllegalAccess");

			unset( $_SESSION[ 'previous_page' ] );
			unset( $_SESSION[ 'previous_page_admin' ] );

			if( $_GET[ 'inquiry' ] ) //問い合わせ要求がある場合
				{ $this->action = 'doInquiry'; }
			else //要求がない場合
			{
				if( $_GET[ 'run' ] ) //検索要求がある場合
					{ $this->action = 'doSearch'; }
				else //要求がない場合
					{ $this->action = 'searchForm'; }
			}

			parent::__construct();
		}
	}
