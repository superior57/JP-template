<?php

	//★クラス //

	/**
		@brief 既定の管理ツールの書き込み権限設定処理のコントローラ。
	*/
	class AppInstallPermissionController extends AppBaseController //
	{
		//■処理 //

		/**
			インストール処理結果表示要求への応答。
		*/
		function doInstall() //
		{
			$this->model->doInstall();

			if( $this->model->succeededInstall() ) //書き込み権限の設定に成功した場合
				{ $this->view->drawSucceededInstallPage( $this->model ); }
			else //書き込み権限の設定に失敗した場合
				{ $this->view->drawFailedInstallPage( $this->model ); }
		}

		/**
			ログインフォーム表示要求への応答。
		*/
		function drawLoginForm() //
			{ $this->view->drawLoginForm( $this->model ); }

		/**
			再インストールフォーム表示要求への応答。
		*/
		function drawReInstallForm() //
			{ $this->view->drawReInstallForm( $this->model ); }

		//■コンストラクタ・デストラクタ //

		/**
			@brief コンストラクタ。
		*/
		function __construct() //
		{
			if( InstallStatus::IsComplete() ) //インストールが完了している場合
			{
				if( InstallStatus::Skipable() ) //インストールをスキップ出来る場合
				{
					if( !$_SESSION[ 'loginedAdminTool' ] ) //ログインしていない場合
					{
						$this->action = 'drawLoginForm';
						parent::__construct();
						return;
					}
				}
				else //インストールをスキップできない場合
					{ $this->action = 'drawReInstallForm'; }
			}

			if( $_POST[ 'method' ] ) //要求がある場合
				{ $this->action = $_POST[ 'method' ]; }
			else //要求がない場合
				{ $this->action = 'doInstall'; }

			parent::__construct();
		}
	}
