<?php

	//★クラス //

	/**
		@brief 既定のパスワードリマインダのコントローラ。
	*/
	class AppReminderController extends AppBaseController //
	{
		//■処理 //

		/**
			@brief パスワード再発行の入力フォーム処理。
		*/
		function reminderForm() //
			{ $this->view->drawReminderFormPage( $this->model ); }

		/**
			@brief パスワード再発行実行処理。
		*/
		function reissuePassword() //
		{
			$this->model->verifyToken();
			$this->model->reissuePassword();

			if( $this->model->succeededPasswordReissue() ) //再発行に成功した場合
				{ $this->view->drawSucceededPasswordReissuePage( $this->model ); }
			else //再発行に失敗した場合
				{ $this->view->drawReminderFormPage( $this->model ); }
		}

		//■コンストラクタ・デストラクタ //

		/**
			@brief コンストラクタ。
		*/
		function __construct() //
		{
			if( $_POST[ 'mail' ] ) //メールアドレスが送信されている場合
				{ $this->action = 'reissuePassword'; }
			else //メールアドレスが送信されていない場合
				{ $this->action = 'reminderForm'; }

			parent::__construct();
		}
	}
