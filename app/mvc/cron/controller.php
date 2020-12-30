<?php

	//★クラス //

	/**
		@brief 既定のCRONのコントローラ。
	*/
	class AppCRONController extends AppBaseController //
	{
		//■処理 //

		/**
			@brief 通常の処理。
		*/
		function index() //
			{ $this->view->redirectIndex( $this->model ); }

		/**
			@brief CRON実行要求への応答。
		*/
		function doLabelCron() //
		{
			$this->model->initializeCronSetting();
			$this->model->doLabelCron();
		}

		/**
			@brief CRON実行要求への応答。
		*/
		function doArgcCron() //
		{
			$this->model->initializeCronSetting();
			$this->model->doArgcCron();
		}

		//■データ取得 //

		/**
			@brief  コントローラの動作に必要なインクルードパスの一覧を取得する。
			@return インクルードパスの一覧。
		*/
		static function GetNeedIncludes() //
		{
			return Array
			(
				'custom/head_main.php' ,
				'custom/cron/core.inc'
			);
		}

		//■コンストラクタ・デストラクタ //

		/**
			@brief コンストラクタ。
		*/
		function __construct() //
		{
			global $argc;
			global $argv;

			if( isset( $_GET[ 'label' ] ) ) //ラベルが指定されている場合
				{ $this->action = 'doLabelCron'; }
			else if( isset( $argc ) && 1 < $argc ) //引数が指定されている場合
				{ $this->action = 'doArgcCron'; }
			else
				{ $this->action = 'index'; }

			if( class_exists( 'TemplateCache' ) )
				{ TemplateCache::$NoCache = true; }

			parent::__construct();

			if( class_exists( 'CodeScheduler' ) )
				{ CodeScheduler::Run( 'SetCron' ); }
		}
	}
