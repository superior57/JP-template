<?php

	//★クラス //

	/**
		@brief 既定の管理ツールのダウンロード処理のコントローラ。
	*/
	class AppDownloadController extends AppBaseController //
	{
		//■処理 //

		/**
			ダウンロードヘッダ出力処理要求への応答。
		*/
		function doDownload() //
			{ $this->view->drawDownloadHeader( $this->model ); }

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
				{ $this->action = 'doDownload'; }
			else //ログインしていない場合
				{ $this->action = 'error'; }

			parent::__construct();
		}
	}
