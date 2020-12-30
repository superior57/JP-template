<?php

	//★クラス //

	/**
		@brief 既定の汎用データ編集フォームのコントローラ。
	*/
	class AppgiftEditController extends AppBaseController //
	{
		//■処理 //

		/**
			@brief 編集フォーム表示要求への応答。
		*/
		function editForm() //
		{
			$this->view->drawEditFormPage( $this->model );
		}

		/**
			@brief 前画面に戻る要求への応答。
		*/
		function goBack() //
		{
			$this->model->updateQuery();
			$this->model->verifyToken();
			$this->model->goBack();

			$this->view->drawEditFormPage( $this->model );
		}

		/**
			@brief 編集内容の確認要求への応答。
		*/
		function confirmEdit() //
		{
			$this->model->updateQuery();
			$this->model->verifyToken();
			$this->model->verifyInput();
			$this->model->doConfirm();

			if( $this->model->canEdit() ) //編集可能な場合
				{ $this->view->drawConfirmEditPage( $this->model ); }
			else //編集可能でない場合
				{ $this->view->drawEditFormPage( $this->model ); }
		}

		/**
			@brief 編集実行要求への応答。
		*/
		function doEdit() //
		{
			$this->model->updateQuery();
			$this->model->verifyToken();
			$this->model->verifyInput();
			$this->model->doEdit();

			if( $this->model->succeededEdit() ) //登録に成功した場合
				{ $this->view->drawSucceededEditPage( $this->model ); }
			else //登録に失敗した場合
				{ $this->view->drawFailedEditPage( $this->model ); }
		}

		//■コンストラクタ・デストラクタ //

		/**
			@brief コンストラクタ。
		*/
		function __construct() //
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			global $loginUserType;
			global $LOGIN_ID;
			// **************************************************************************************

			ConceptSystem::CheckPostMaxSizeOrver()->OrThrow( 'PostMaxSizeOrver' );
			ConceptSystem::IsNotNull( $_GET[ 'type' ] )->OrThrow( 'InvalidQuery' );
			ConceptSystem::CheckType()->OrThrow( 'InvalidQuery' );
			ConceptSystem::CheckTableNoHTML()->OrThrow( 'IllegalAccess' );

			if ($loginUserType == 'nUser') {
				Concept::isTrue(bankAccountLogic::existsBankAccount($LOGIN_ID))->OrThrow("NotExistsBankAccount");
			}

			if( isset( $_POST[ 'back' ] ) ) //前画面に戻る要求がある場合
				{ $this->action = 'goBack'; }
			else //要求がない場合
			{
				if( isset( $_POST[ 'post' ] ) ) //POSTクエリが送信されている場合
				{
					if( 'edit' == $_POST[ 'post' ] ) //編集実行処理が要求されている場合
						{ $this->action = 'doEdit'; }
					else //要求がない場合
						{ $this->action = 'confirmEdit'; }
				}
				else //要求がない場合
					{ $this->action = 'editForm'; }
			}

			parent::__construct();
		}
	}
