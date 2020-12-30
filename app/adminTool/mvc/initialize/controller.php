<?php

	//★クラス //

	/**
		@brief 既定の管理ツールの初期化処理のコントローラ。
	*/
	class AppInitializeController extends AppBaseController //
	{
		//■処理 //

		/**
			初期化処理要求への応答。
		*/
		function doInitialize() //
		{
			if( $this->model->doInitialize( $_POST[ 'type' ] ) ) //初期化に成功した場合
				{ $this->view->drawSuccessAction( $this->model ); }
			else //初期化に失敗した場合
				{ $this->view->drawFailedAction( $this->model ); }
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
				{ $this->action = 'doInitialize'; }
			else //ログインしていない場合
				{ $this->action = 'error'; }

			parent::__construct();
		}
	}
