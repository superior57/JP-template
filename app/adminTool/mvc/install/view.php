<?php

	//★クラス //

	/**
		@brief 既定の管理ツールのインストールウィザードのビュー。
	*/
	class AppInstallView extends AppBaseView //
	{
		//■処理 //

		/**
			@brief トップページ画面を出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function drawIndex( $iModel ) //
			{ include $this->templatePath . 'install/index.html'; }

		/**
			@brief インストールのスキップ確認フォーム画面を出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function drawInstallSkipForm( $iModel ) //
			{ include $this->templatePath . 'install/installSkipForm.html'; }

		/**
			@brief インストールのスキップ完了画面を出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function drawSkipInstallSuccess( $iModel ) //
			{ include $this->templatePath . 'install/skipInstallSuccess.html'; }

		/**
			@brief ログインフォーム画面を出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function drawLoginForm( $iModel ) //
			{ include $this->templatePath . 'common/loginForm.html'; }

		/**
			@brief 再インストールフォーム画面を出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function drawReInstallForm( $iModel ) //
			{ include $this->templatePath . 'reinstall/index.html'; }

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
