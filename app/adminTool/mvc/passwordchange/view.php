<?php

	//★クラス //

	/**
		@brief 既定の管理ツールのパスワード変更処理のビュー。
	*/
	class AppPasswordChangeView extends AppBaseView //
	{
		//■処理 //

		/**
			@brief 管理ツールパスワード変更フォーム画面を出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function drawPasswordChangeForm( $iModel ) //
			{ include_once $this->templatePath . 'passwordChange/passwordChangeForm.html'; }

		/**
			@brief 管理ツールパスワード変更の結果画面を出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function drawSuccessPasswordChange( $iModel ) //
			{ include_once $this->templatePath . 'passwordChange/successPasswordChange.html'; }

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
