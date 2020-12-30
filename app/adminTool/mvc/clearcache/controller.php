<?php

	//★クラス //

	/**
		@brief 既定の管理ツールのキャッシュ削除処理のコントローラ。
	*/
	class AppClearCacheController extends AppBaseController //
	{
		//■処理 //

		function index() //
		{
			$this->model->clearCache();
			$this->view->drawComplete( $this->model );
		}

		//■データ取得 //

		/**
			@brief  コントローラの動作に必要なインクルードパスの一覧を取得する。
			@return インクルードパスの一覧。
		*/
		static function GetNeedIncludes() //
		{
			global $SQL_MASTER;

			if( 'MySQLDatabase' == $SQL_MASTER ) //MySQLを使用する場合
				{ $path = 'mysql'; }
			else //SQLiteを使用する場合
				{ $path = 'sqlite'; }

			return Array(
				'app/adminTool/lib/' . $path . '/query.php' ,
				'app/adminTool/lib/' . $path . '/queryWriter.php' ,
				'custom/tool/tool.inc' ,
			);
		}

		//■コンストラクタ・デストラクタ //

		/**
			@brief コンストラクタ。
		*/
		function __construct() //
		{
			$this->action = 'index';

			parent::__construct();
		}
	}
