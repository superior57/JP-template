<?php

	//★クラス //

	/**
		@brief 既定の静的ページのコントローラ。
	*/
	class AppgiftOtherController extends AppBaseController //
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

		//■コンストラクタ・デストラクタ //

		/**
			@brief コンストラクタ。
		*/
		function __construct() //
		{
			ConceptSystem::IsAnyNotNull( $_GET[ 'page' ] , $_GET[ 'key' ] )->OrThrow( 'InvalidQuery' );
			ConceptSystem::IsAnyNotEmpty( $_GET[ 'page' ] , $_GET[ 'key' ] )->OrThrow( 'InvalidQuery' );

			Concept::IsTrue(Conf::checkData("job", "nobody_apply", "on"))->OrThrow("IllegalAccess");
			Concept::IsTrue(Conf::getData("charges", "gift")!="")->OrThrow("IllegalAccess");

			$this->action = 'viewPage';

			parent::__construct();
		}
	}
