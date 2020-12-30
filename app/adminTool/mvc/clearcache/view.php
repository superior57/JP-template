<?php

	//★クラス //

	/**
		@brief 既定の管理ツールのキャッシュ削除処理のビュー。
	*/
	class AppClearCacheView extends AppBaseView //
	{
		function drawComplete( $iModel ) //
			{ include $this->templatePath . 'common/clearCache.html'; }

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
