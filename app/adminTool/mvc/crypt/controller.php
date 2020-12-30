<?php

	//★クラス //

	/**
		@brief 既定の管理ツールのパスワード暗号化処理のコントローラ。
	*/
	class AppCryptController extends AppBaseController //
	{
		//■処理 //

		/**
			暗号化適用要求への応答。
		*/
		function doCrypt() //
		{
			if( $this->model->isEnableCrypt() ) //暗号化が有効になっている場合
				{ $this->view->drawExists( $this->model ); }
			else //暗号化が有効になっていない場合
			{
				$this->model->updateCryptConfig( $this->model );
				$this->model->doCrypt( $this->model );
				$this->view->doCrypt( $this->model );
			}
		}

		/**
			暗号化確認フォーム表示要求への応答。
		*/
		function drawCryptForm() //
		{
			if( $this->model->isEnableCrypt() ) //暗号化が有効になっている場合
				{ $this->view->drawExists( $this->model ); }
			else //暗号化が有効になっていない場合
				{ $this->view->drawCryptForm( $this->model ); }
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
				switch( $_POST[ 'method' ] )
				{
					case 'doCrypt' : //パスワード変更処理
					{
						$this->action = $_POST[ 'method' ];
						break;
					}

					case 'drawCryptForm' : //パスワード変更フォーム
					default              : //その他
					{
						$this->action = 'drawCryptForm';
						break;
					}
				}

			}
			else //ログインしていない場合
				{ $this->action = 'loginForm'; }

			parent::__construct();
		}
	}
