<?php

	//★クラス //

	/**
		@brief 既定のインデックスページのコントローラ。
	*/
	class AppIndexController extends AppBaseController //
	{
		//■処理 //

		/**
			@brief 通常の処理。
		*/
		function index() //
			{ $this->view->drawIndexPage( $this->model ); }

		//■コンストラクタ・デストラクタ //

		/**
			@brief コンストラクタ。
		*/
		function __construct() //
		{
			parent::__construct();

			unset( $_SESSION[ 'previous_page' ] );
			unset( $_SESSION[ 'previous_page_admin' ] );
		}
	}
