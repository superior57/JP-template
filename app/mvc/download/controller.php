<?php

	//★クラス //

	/**
		@brief 既定のデータ削除処理のコントローラ。
	*/
	class AppDownloadController extends AppBaseController //
	{
		function index(){
			$this->view->undefinedData($this->model);
		}

		function callDownload(){
			$this->model->doDownload();
		}

		//■コンストラクタ・デストラクタ //
		/**
			@brief コンストラクタ。
		*/
		function __construct() //
		{
			ConceptSystem::IsNotNull( $_GET[ 'type' ] )->OrThrow( 'InvalidQuery' );
			ConceptSystem::CheckType()->OrThrow( 'InvalidQuery' );
			ConceptSystem::CheckTableNoHTML()->OrThrow( 'IllegalAccess' );

			$param = ( empty($_POST) ? $_GET : $_POST );
			$class = "dl_".$_GET["type"];

			Concept::IsTrue(class_exists($class))->OrThrow("InvalidQuery");

			$this->action = 'callDownload';
			parent::__construct();
		}

		static function GetNeedIncludes() //
		{
			return Array
			(
				'custom/head_main.php',
				'./custom/extends/downloadConf.php'
			);
		}
	}
