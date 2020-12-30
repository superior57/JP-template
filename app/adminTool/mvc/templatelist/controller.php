<?php

	//★クラス //

	/**
		@brief 既定の管理ツールのテンプレート一覧表示処理のコントローラ。
	*/
	class AppTemplateListController extends AppBaseController //
	{
		//■処理 //

		/**
			テンプレート一覧表示要求への応答。
		*/
		function templateList() //
		{
			$this->model->getTemplateList();
			$this->view->drawTemplateList( $this->model );
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
			);
		}

		//■コンストラクタ・デストラクタ //

		/**
			@brief コンストラクタ。
		*/
		function __construct() //
		{
			if( $_SESSION[ 'loginedAdminTool' ] ) //ログインしている場合
				{ $this->action = 'templateList'; }
			else //ログインしていない場合
				{ $this->action = 'error'; }

			parent::__construct();
		}
	}
