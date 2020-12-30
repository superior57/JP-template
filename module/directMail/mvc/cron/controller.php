<?php

	//★クラス //

	/**
		@brief 既定のCRONのコントローラ。
	*/
	class AppdirectMailCRONController extends AppCRONController //
	{
		//■処理 //

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

			if( isset( $_GET[ 'label' ] ) ) //ラベルが指定されている場合
				{ $this->action = 'doLabelCron'; }
			else if( isset( $argc ) && 1 < $argc ) //引数が指定されている場合
				{ $this->action = 'doArgcCron'; }

			parent::__construct();
		}
	}
