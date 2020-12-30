<?php

	//★クラス //

	/**
		@brief 既定の一般ページのコントローラ。
	*/
	class AppPageController extends AppBaseController //
	{
		//■処理 //

		/**
			@brief ページ表示要求への応答。
		*/
		function viewPage() //
		{
			$this->model->searchPage();

			if( $this->model->hasSearchResult() ) //ページがある場合
				{ $this->view->drawPage( $this->model ); }
			else //ページがない場合
				{ $this->view->drawErrorPage( $this->model ); }
		}

		/**
			@brief ページのプレビュー要求への応答。
		*/
		function previewPage() //
		{
			$this->model->searchPreviewPage();

			if( $this->model->hasSearchResult() ) //ページがある場合
				{ $this->view->drawPreviewPage( $this->model ); }
			else //ページがない場合
				{ $this->view->drawErrorPage( $this->model ); }
		}

		/**
			@brief ページのプレビューリスト表示要求への応答。
		*/
		function previewPageList() //
		{
			$this->model->searchPreviewList();

			if( $this->model->hasSearchResult() ) //一覧がある場合
				{ $this->view->drawPreviewListPage( $this->model ); }
			else //一覧がない場合
				{ $this->view->drawErrorPage( $this->model ); }
		}

		//■コンストラクタ・デストラクタ //

		/**
			@brief コンストラクタ。
		*/
		function __construct() //
		{
			global $loginUserType;

			unset( $_SESSION[ 'previous_page' ] );
			unset( $_SESSION[ 'previous_page_admin' ] );

			if( 'admin' == $loginUserType ) //管理者でログインしている場合
			{
				if( isset( $_GET[ 'authority' ] ) ) //ページ指定がある場合
					{ $this->action = 'previewPage'; }
				else //ページ指定がない場合
					{ $this->action = 'previewPageList'; }
			}
			else //管理者以外のユーザーの場合
				{ $this->action = 'viewPage'; }

			parent::__construct();
		}
	}
