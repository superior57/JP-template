<?php

	//★クラス //

	/**
		@brief 既定の管理ツールのログイン・ログアウトのビュー。
	*/
	class AppIndexView extends AppBaseView //
	{
		//■処理 //

		function drawCountUpdateResult( $iModel ) //
			{ include $this->templatePath . 'common/countUpdateResult.html'; }

		function drawExit( $iModel ) //
			{ include $this->templatePath . 'common/exitTool.html'; }

		function drawExitForm( $iModel ) //
			{ include $this->templatePath . 'common/exitForm.html'; }

		/**
			@brief トップページ画面を出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function drawIndex( $iModel ) //
			{ include $this->templatePath . 'common/index.html'; }

		/**
			@brief インストールフォーム画面を出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function drawInstallForm( $iModel ) //
			{ include $this->templatePath . 'install/index.html'; }

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
