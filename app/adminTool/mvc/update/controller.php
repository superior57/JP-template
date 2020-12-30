<?php

	//★クラス //

	/**
		@brief 既定の管理ツールの更新処理のコントローラ。
	*/
	class AppUpdateController extends AppBaseController //
	{
		//■処理 //

		/**
			構成情報の更新処理要求への応答。
		*/
		function doUpdate() //
		{
			$main = new TableName( $_POST[ 'type' ] );

			if( !in_array( $main->real() , Query::ShowTables() ) ) //テーブルが存在しない場合
				{ MVC::Call( 'initialize' ); }
			else
			{
				List( $changeStruct , $changeIndex ) = $this->model->isNoChange( $_POST[ 'type' ] );

				if( !$changeStruct && !$changeIndex ) //構成情報に変化がない場合
					{ $this->view->drawSkipAction( $this->model ); }
				else if( !$changeStruct && $changeIndex ) //インデックスだけが変更された場合
				{
					if( $this->model->doUpdateIndex( $_POST[ 'type' ] ) ) //インデックスの更新に成功した場合
						{ $this->view->drawSuccessAction( $this->model ); }
					else //インデックスの更新に失敗した場合
						{ $this->view->drawFailedAction( $this->model ); }
				}
			else if( $this->model->doUpdate( $_POST[ 'type' ] ) ) //構成情報の更新に成功した場合
				{ $this->view->drawSuccessAction( $this->model ); }
			else //構成情報の更新に失敗した場合
				{ $this->view->drawFailedAction( $this->model ); }
		}
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
				{ $this->action = 'doUpdate'; }
			else //ログインしていない場合
				{ $this->action = 'error'; }

			parent::__construct();
		}
	}
