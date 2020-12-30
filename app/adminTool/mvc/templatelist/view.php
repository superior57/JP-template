<?php

	//★クラス //

	/**
		@brief 既定の管理ツールのテンプレート一覧表示処理のビュー。
	*/
	class AppTemplateListView extends AppBaseView //
	{
		//■処理 //

		/**
			@brief テンプレート一覧表示画面を出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function drawTemplateList( $iModel ) //
			{ include_once $this->templatePath . 'templateList/templateList.html'; }

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
