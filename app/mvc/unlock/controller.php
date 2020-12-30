<?php

	//★クラス //

	/**
		@brief 既定のログインロック解除フォームのコントローラ。
	*/
	class AppUnlockController extends AppBaseController //
	{
		//■処理 //
		/**
			@brief アカウントロック解除フォーム表示要求への応答。
		*/
		function unlockForm() //
		{
			$this->model->verifyUnlockToken();

			$this->view->drawUnlockFormPage( $this->model );
		}

		/**
			@brief アカウントロック解除要求への応答。
		*/
		function doUnlock() //
		{
			$this->model->verifyUnlockToken();
			$this->model->doUnlock();

			if( $this->model->succeededUnlock() ) //ロック解除に成功した場合
				{ $this->view->drawSucceededUnlockPage( $this->model ); }
			else //ロック解除に失敗した場合
				{ $this->view->drawFailedUnlockPage( $this->model ); }
		}

		//■コンストラクタ・デストラクタ //

		/**
			@brief コンストラクタ。
		*/
		function __construct() //
		{
			unset( $_SESSION[ 'previous_page' ] );
			unset( $_SESSION[ 'previous_page_admin' ] );

			if( $_POST[ 'password' ] ) //パスワードが送信されている場合
				{ $this->action = 'doUnlock'; }
			else //パスワードが送信されていない場合
				{ $this->action = 'unlockForm'; }

			parent::__construct();
		}
	}
