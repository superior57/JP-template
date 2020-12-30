<?php

	//★クラス //

	/**
		@brief 既定の汎用データ登録フォームのコントローラ。
	*/
	class AppnUserRegisterController extends AppRegisterController //
	{
		//■処理 //

		/**
			@brief 登録フォーム表示要求への応答。
		*/
		function registerForm() //
		{
			if(!$this->model->nobodyRegistValid())
				$this->model->checkValidity();
			$this->model->initializeQuery();
			$this->view->drawRegisterFormPage( $this->model );
		}

		/**
			@brief 前画面に戻る要求への応答。
		*/
		function goBack() //
		{
			$this->model->updateQuery();
			$this->model->verifyToken();
			$this->model->goBack();

			$this->view->drawRegisterFormPage( $this->model );
		}

		/**
			@brief 次画面に進む要求への応答。
		*/
		function goForward() //
		{
			$this->model->updateQuery();
			$this->model->verifyToken();
			$this->model->verifyInput();
			$this->model->goForward();

			$this->view->drawRegisterFormPage( $this->model );
		}

		/**
			@brief 登録内容の確認要求への応答。
		*/
		function confirmRegister() //
		{
			$this->model->updateQuery();
			$this->model->verifyToken();
			$this->model->verifyInput();
			$this->model->goForward();

			if( $this->model->canRegister() ) //登録可能な場合
			{
				$this->model->doConfirm();
				$this->view->drawConfirmRegisterPage( $this->model );
			}
			else //登録可能でない場合
				{ $this->view->drawRegisterFormPage( $this->model ); }
		}

		/**
			@brief 登録実行要求への応答。
		*/
		function doRegister() //
		{
			$this->model->updateQuery();
			$this->model->verifyToken();
			$this->model->verifyInput();
			$this->model->doRegister();

			if( $this->model->succeededRegister() ) //登録に成功した場合
				{ $this->view->drawSucceededRegisterPage( $this->model ); }
			else //登録に失敗した場合
				{ $this->view->drawFailedRegisterPage( $this->model ); }
		}

		//■コンストラクタ・デストラクタ //

		/**
			@brief コンストラクタ。
		*/
		function __construct() //
		{
			global $gm;
			global $LOGIN_ID;
			global $loginUserType;

			ConceptSystem::CheckPostMaxSizeOrver()->OrThrow( 'PostMaxSizeOrver' );
			ConceptSystem::IsNotNull( $_GET[ 'type' ] )->OrThrow( 'InvalidQuery' );
			ConceptSystem::CheckType()->OrThrow( 'InvalidQuery' );
			ConceptSystem::CheckTableRegistUser()->OrThrow( 'IllegalAccess' );
			Concept::IsTrue(Conf::checkData("user", "use_nuser", "on"))->OrThrow("IllegalAccess");


			if( isset( $_POST[ 'back' ] ) ) //前画面に戻る要求がある場合
				{ $this->action = 'goBack'; }
			else //要求がない場合
			{
				if( !isset( $_POST[ 'step' ] ) || !strlen( $_POST[ 'step' ] ) || !$_POST[ 'step' ] )
					{ $_POST['step'] = 1; }

				if( isset( $_POST[ 'post' ] ) ) //POSTクエリが送信されている場合
				{
					if( 'register' == $_POST[ 'post' ] ) //登録実行処理が要求されている場合
						{ $this->action = 'doRegister'; }
					else if( !$gm[ $_GET[ 'type' ] ]->maxStep || $gm[ $_GET[ 'type' ] ]->maxStep <= $_POST[ 'step' ] ) //最終手順まで完了している場合
						{ $this->action = 'confirmRegister'; }
					else //最終手順に至っていない場合
						{ $this->action = 'goForward'; }
				}
				else //要求がない場合
					{ $this->action = 'registerForm'; }
			}
			parent::__construct();
		}
	}
