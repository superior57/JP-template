<?php

	//★クラス //

	/**
		@brief 既定の管理ツールのパッチ処理のビュー。
	*/
	class AppPatchView extends AppBaseView //
	{
		//■処理 //

		/**
			@brief パッチ選択フォーム画面を出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function drawPatchFormPage( $iModel ) //
			{ include $this->templatePath . 'patch/patch.html'; }

		/**
			@brief パッチ確認画面を出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function drawConfirmPatchPage( $iModel ) //
			{ include $this->templatePath . 'patch/patchCheck.html'; }

		/**
			@brief パッチ適用完了画面を出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function drawSucceededPatchPage( $iModel ) //
			{ include $this->templatePath . 'patch/patchComp.html'; }

		/**
			@brief ログインフォーム画面を出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function drawLoginForm( $iModel ) //
			{ include $this->templatePath . 'common/loginForm.html'; }

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
