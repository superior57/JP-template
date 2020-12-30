<?php

	//★クラス //

	/**
		@brief 既定の詳細情報ページのコントローラ。
	*/
	class AppfreshInfoController extends AppInfoController //
	{
		//■処理 //

		/**
			@brief 詳細情報表示要求への応答。
		*/
		function viewDetail() //
		{
			$this->model->verifyViewAuthority();

			if( $this->model->canView() ) //閲覧できる場合
			{
				$this->model->doQuickUpdate();

				$this->view->drawInfoPage( $this->model );
			}
			else //閲覧できない場合
				{ $this->view->drawErrorPage( $this->model ); }
		}

		//■コンストラクタ・デストラクタ //

		/**
			@brief コンストラクタ。
		*/
		function __construct() //
		{
			ConceptSystem::IsNotNull( $_GET[ 'type' ] )->OrThrow( 'InvalidQuery' );
			ConceptSystem::CheckType()->OrThrow( 'InvalidQuery' );

			Concept::IsTrue(JobLogic::isHandle($_GET['type']))->OrThrow("IllegalAccess");

			unset( $_SESSION[ 'previous_page' ] );
			unset( $_SESSION[ 'previous_page_admin' ] );

			$this->action = 'viewDetail';

			parent::__construct();
		}
	}
