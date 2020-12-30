<?php

	//★クラス //

	/**
		@brief 既定の管理ツールのファイル出力処理のコントローラ。
	*/
	class AppExportController extends AppBaseController //
	{
		//■処理 //

		/**
			CSVファイルへの出力処理要求への応答。
		*/
		function doExport() //
		{
			$main = new TableName( $_POST[ 'type' ] );

			if( !in_array( $main->real() , Query::ShowTables() ) ) //テーブルが存在しない場合
				{ $this->view->drawSkipAction( $this->model ); }
			else if( $this->model->doExport( $_POST[ 'type' ] ) ) //CSVファイルへの出力に成功した場合
				{ $this->view->drawSuccessAction( $this->model ); }
			else //CSVファイルへの出力に失敗した場合
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
				{ $this->action = 'doExport'; }
			else //ログインしていない場合
				{ $this->action = 'error'; }

			parent::__construct();
		}
	}
