<?php

	//★クラス //

	/**
		@brief 既定の汎用データ編集フォームのコントローラ。
	*/
	class AppfreshEditController extends AppBaseController //
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
			global $LOGIN_ID;
			global $loginUserType;

			ConceptSystem::CheckPostMaxSizeOrver()->OrThrow( 'PostMaxSizeOrver' );
			ConceptSystem::IsNotNull( $_GET[ 'type' ] )->OrThrow( 'InvalidQuery' );
			ConceptSystem::CheckType()->OrThrow( 'InvalidQuery' );
			ConceptSystem::CheckTableNoHTML()->OrThrow( 'IllegalAccess' );

			if ($loginUserType == "cUser") {
				Concept::IsTrue(SystemUtil::getTableData("cUser", $LOGIN_ID, "edit_comp"))->OrThrow("userDataUnedited");
				Concept::IsTrue(pay_jobLogic::isAvailable($LOGIN_ID, "fresh"))->OrThrow("freshContractExpire");
			}
			Concept::IsTrue(JobLogic::isHandle($_GET['type']))->OrThrow("IllegalAccess");

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

			Concept::IsFalse($this->model->db->getData($this->model->rec,"delete_flg"))->OrThrow();
		}
	}
