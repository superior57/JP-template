<?php

	//★クラス //

	/**
		@brief 既定の管理ツールのパスワード変更処理のコントローラ。
	*/
	class AppPasswordChangeController extends AppBaseController //
	{
		//■処理 //

		/**
			管理ツールパスワード変更要求への応答。
		*/
		function doChangePassword() //
		{
			if( $this->model->doChangePassword() ) //パスワードの変更に成功した場合
				{ $this->view->drawSuccessPasswordChange( $this->model ); }
			else //パスワードの変更に失敗した場合
				{ $this->view->drawPasswordChangeForm( $this->model ); }
		}

		/**
			管理ツールパスワード変更フォーム表示要求への応答。
		*/
		function passwordChangeForm() //
			{ $this->view->drawPasswordChangeForm( $this->model ); }

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
				switch( $_POST[ 'method' ] )
				{
					case 'doChangePassword' : //パスワード変更処理
					{
						$this->action = $_POST[ 'method' ];
						break;
					}

					case 'passwordChangeForm' : //パスワード変更フォーム
					default                   : //その他
					{
						$this->action = 'passwordChangeForm';
						break;
					}
				}
			}
			else //ログインしていない場合
				{ $this->action = 'error'; }

			parent::__construct();
		}
	}
