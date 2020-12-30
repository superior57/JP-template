<?php

	//★クラス //

	/**
		@brief 既定の管理ツールのパスワード暗号化処理のビュー。
	*/
	class AppCryptView extends AppBaseView //
	{
		//■処理 //

		/**
			@brief 暗号化実行画面を出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function doCrypt( $iModel ) //
			{ include_once $this->templatePath . 'crypt/doCrypt.html'; }

		/**
			@brief 暗号化済み画面を出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function drawExists( $iModel ) //
			{ include_once $this->templatePath . 'crypt/exists.html'; }

		/**
			@brief 暗号化の確認画面を出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function drawCryptForm( $iModel ) //
			{ include_once $this->templatePath . 'crypt/cryptForm.html'; }

		//■コンストラクタ・デストラクタ //

		/**
			@brief コンストラクタ。
		*/
		function __construct() //
		{
			global $TOOL_TEMPLATE_PATH;

			$this->templatePath = $TOOL_TEMPLATE_PATH;
		}
	}
