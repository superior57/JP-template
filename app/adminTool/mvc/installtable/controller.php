<?php

	//★クラス //

	/**
		@brief 既定の管理ツールのインストールウィザードのコントローラ。
	*/
	class AppInstallTableController extends AppBaseController //
	{
		//■処理 //

		/**
			@brief 設定適用要求への応答。
		*/
		function doInstall() //
		{
			$this->model->verifyInput();

			if( $this->model->canInstall() )
			{
				$this->model->updateInstallStatus();
				$this->model->login();
				$this->view->drawSucceededInstallPage( $this->model );
			}
			else
				{ $this->view->drawInstallFormPage( $this->model ); }
		}

		/**
			@brief 設定フォーム表示要求への応答。
		*/
		function drawInstallForm() //
			{ $this->view->drawInstallFormPage( $this->model ); }

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
				{ $this->action = 'drawInstallForm'; }

			parent::__construct();
		}
	}
