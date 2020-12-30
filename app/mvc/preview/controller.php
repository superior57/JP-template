<?php

	//★クラス //

	/**
		@brief 既定の詳細情報ページのコントローラ。
	*/
	class AppPreviewController extends AppBaseController //
	{
		//■処理 //

		/**
			@brief 詳細情報表示要求への応答。
		*/
		function preview() //
			{ $this->view->drawPreview( $this->model ); }

		//■コンストラクタ・デストラクタ //

		/**
			@brief コンストラクタ。
		*/
		function __construct() //
		{
			ConceptSystem::IsNotNull( $_GET[ 'type' ] , $_GET[ 'id' ] )->OrThrow( 'InvalidQuery' );
			ConceptSystem::CheckType()->OrThrow( 'InvalidQuery' );

			$this->action = 'preview';

			parent::__construct();
		}
	}
