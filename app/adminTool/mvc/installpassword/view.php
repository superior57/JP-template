<?php

	//★クラス //

	/**
		@brief 既定の管理ツールのインストールウィザードのビュー。
	*/
	class AppInstallPasswordView extends AppBaseView //
	{
		//■処理 //

		/**
			@brief 設定フォーム画面を出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function drawInstallFormPage( $iModel ) //
			{ include $this->templatePath . 'install/password/install.html'; }

		/**
			@brief 設定適用完了画面を出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function drawSucceededInstallPage( $iModel ) //
			{ include $this->templatePath . 'install/password/installComp.html'; }

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
