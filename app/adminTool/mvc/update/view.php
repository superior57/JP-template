<?php

	//★クラス //

	/**
		@brief 既定の管理ツールの更新処理のビュー。
	*/
	class AppUpdateView extends AppBaseView //
	{
		//■処理 //

		/**
			@brief 処理失敗画面を出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function drawFailedAction( $iModel ) //
			{ include_once $this->templatePath . 'action/failedAction.html'; }

		/**
			@brief 処置スキップ画面を出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function drawSkipAction( $iModel ) //
			{ include_once $this->templatePath . 'action/skipAction.html'; }

		/**
			@brief 処理成功画面を出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function drawSuccessAction( $iModel ) //
			{ include_once $this->templatePath . 'action/successAction.html'; }

		//■コンストラクタ・デストラクタ //

		/**
			@brief コンストラクタ。
		*/
		function __construct() //
		{
			global $TOOL_TEMPLATE_PATH;

			$this->templatePath = $TOOL_TEMPLATE_PATH;
		}

		//■変数 //
		private $templatePath = ''; ///<テンプレートファイルの格納パス。
	}
