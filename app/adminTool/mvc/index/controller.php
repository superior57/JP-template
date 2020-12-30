<?php

	//★クラス //

	/**
		@brief 既定の管理ツールのログイン・ログアウトのコントローラ。
	*/
	class AppIndexController extends AppBaseController //
	{
		//■処理 //

		function doCountUpdate() //
		{
			$this->model->doCountUpdate();
			$this->view->drawCountUpdateResult( $this->model );
		}

		/**
			管理ツールの操作終了要求への応答。
		*/
		function doExit() //
		{
			$this->model->doExit();
			$this->view->drawExit( $this->model );
		}

		/**
			ログイン処理要求への応答。
		*/
		function doLogin() //
		{
			if( $this->model->doLogin() ) //ログインに成功した場合
			{
				$this->model->setRenderStatus();

				$this->view->drawIndex( $this->model );
			}
			else //ログインに失敗した場合
				{ $this->view->drawLoginForm( $this->model ); }
		}

		/**
			ログアウト処理要求への応答。
		*/
		function doLogout() //
		{
			$this->model->doLogout();
			$this->view->drawLoginForm( $this->model );
		}

		/**
			トップページ表示要求への応答。
		*/
		function index() //
		{
			$this->model->setRenderStatus();

			$this->view->drawIndex( $this->model );
		}

		function drawExitForm() //
			{ $this->view->drawExitForm( $this->model ); }

		/**
			インストールフォーム表示要求への応答。
		*/
		function drawInstallForm() //
			{ $this->view->drawInstallForm( $this->model ); }

		/**
			再インストールフォーム表示要求への応答。
		*/
		function drawReInstallForm() //
			{ $this->view->drawReInstallForm( $this->model ); }

		/**
			ログインフォーム表示要求への応答。
		*/
		function drawLoginForm() //
			{ $this->view->drawLoginForm( $this->model ); }

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
					if( $_SESSION[ 'loginedAdminTool' ] ) //ログインしている場合
					{
						if( $_POST[ 'method' ] ) //要求がある場合
							{ $this->action = $_POST[ 'method' ]; }
						else //要求がない場合
							{ $this->action = 'index'; }
					}
					else //ログインしていない場合
					{
						if( 'doLogin' == $_POST[ 'method' ] ) //要求がある場合
							{ $this->action = 'doLogin'; }
						else if( 'drawExitForm' == $_POST[ 'method' ] ) //要求がある場合
							{ $this->action = 'drawExitForm'; }
						else if( 'doExit' == $_POST[ 'method' ] ) //要求がある場合
							{ $this->action = 'doExit'; }
						else //要求がない場合
							{ $this->action = 'drawLoginForm'; }
					}
				}
				else //インストールをスキップできない場合
					{ $this->action = 'drawReInstallForm'; }
			}
			else //インストールが完了していない場合
				{ $this->action = 'drawInstallForm'; }

			parent::__construct();
		}
	}
