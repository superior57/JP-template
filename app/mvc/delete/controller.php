<?php

	//★クラス //

	/**
		@brief 既定のデータ削除処理のコントローラ。
	*/
	class AppDeleteController extends AppBaseController //
	{
		//■処理 //

		/**
			@brief 削除フォーム処理。
		*/
		function deleteForm() //
			{ $this->view->drawDeleteFormPage( $this->model ); }

		/**
			@brief 前の画面に戻る処理。
		*/
		function goBack() //
		{
			$this->model->verifyToken();

			$this->view->drawDeleteFormPage( $this->model );
		}

		/**
			@brief 削除確認処理。
		*/
		function confirmDelete() //
		{
			$this->model->verifyToken();
			$this->model->verifyInput();

			if( $this->model->canDelete() ) //削除可能な場合
				{ $this->view->drawConfirmDeletePage( $this->model ); }
			else //削除可能でない場合
				{ $this->view->drawDeleteFormPage( $this->model ); }
		}

		/**
			@brief 削除確認処理。
		*/
		function confirmDeletePage() //
			{ $this->view->drawConfirmDeletePage( $this->model ); }

		/**
			@brief 削除実行処理。
		*/
		function doDelete() //
		{
			$this->model->verifyToken();
			$this->model->verifyInput();
			$this->model->doDelete();

			if( $this->model->succeededDelete() ) //削除に成功した場合
				{ $this->view->drawSucceededDeletePage( $this->model ); }
			else //削除に失敗した場合
				{ $this->view->drawFailedDeletePage( $this->model ); }
		}

		//■コンストラクタ・デストラクタ //

		/**
			@brief コンストラクタ。
		*/
		function __construct() //
		{
			ConceptSystem::IsNotNull( $_GET[ 'type' ] )->OrThrow( 'InvalidQuery' );
			ConceptSystem::CheckType()->OrThrow( 'InvalidQuery' );
			ConceptSystem::CheckTableNoHTML()->OrThrow( 'IllegalAccess' );

			unset( $_SESSION[ 'previous_page' ] );
			unset( $_SESSION[ 'previous_page_admin' ] );

			if( $_REQUEST[ 'del_edit' ] ) //入力フォームを使用する場合
			{
				if( isset( $_POST[ 'back' ] ) ) //前画面に戻る要求がある場合
					{ $this->action = 'goBack'; }
				else //前画面に戻る要求がない場合
				{
					if( isset( $_POST[ 'post' ] ) ) //POSTクエリが送信されている場合
					{
						if( 'delete' == $_POST[ 'post' ] ) //削除実行処理が要求されている場合
							{ $this->action = 'doDelete'; }
						else //要求がない場合
							{ $this->action = 'confirmDelete'; }
					}
					else //POSTクエリが送信されていない場合
						{ $this->action = 'deleteForm'; }
				}
			}
			else //入力フォームを使用しない場合
			{
				if( 'delete' == $_POST[ 'post' ] ) //削除実行処理が要求されている場合
					{ $this->action = 'doDelete'; }
				else //要求がない場合
					{ $this->action = 'confirmDeletePage'; }
			}

			parent::__construct();
		}
	}
