<?php

	//★クラス //

	/**
		@brief 既定の管理ツールの比較機能のコントローラ。
	*/
	class AppMergeController extends AppBaseController //
	{
		//■処理 //

		/**
			@brief 比較項目選択フォーム表示要求への応答。
		*/
		function mergeCheckForm() //
			{ $this->view->drawMergeCheckForm( $this->model ); }

		/**
			@brief 比較実行要求への応答。
		*/
		function doMerge() //
		{
			$this->model->doBackup( $_POST[ 'tableName' ] );
			$this->view->drawMergeResult( $this->model );
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
			{
				switch( $_POST[ 'method' ] ) //要求で分岐
				{
					case 'mergeCheckForm' : //比較項目選択フォーム
					case 'doMerge'        : //比較実行
					{
						$this->action = $_POST[ 'method' ];
						break;
					}

					default : //その他
					{
						$this->action = 'error';
						break;
					}
				}
			}
			else //ログインしていない場合
				{ $this->action = 'error'; }

			parent::__construct();
		}
	}
