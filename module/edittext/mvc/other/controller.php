<?php

	//★クラス //

	/**
		@brief 既定の静的ページのコントローラ。
	*/
	class AppedittextOtherController extends AppOtherController //
	{
		//■処理 //

		/**
			@brief ページ表示要求への応答。
		*/
		function viewPage() //
		{
			$this->model->doQuickUpdate();
			parent::viewPage();
		}

		//■コンストラクタ・デストラクタ //

		/**
			@brief コンストラクタ。
		*/
		function __construct() //
		{
			ConceptSystem::IsNotNull( $_GET[ 'type' ] )->OrThrow( 'InvalidQuery' );
			parent::__construct();
		}
	}
