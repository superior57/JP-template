<?php

	//★クラス //

	/**
		@brief 既定の汎用データ登録フォームのコントローラ。
	*/
	class AppnobodyRegisterController extends AppRegisterController //
	{
		//■コンストラクタ・デストラクタ //

		/**
			@brief コンストラクタ。
		*/
		function __construct() //
		{
			global $gm;

			ConceptSystem::CheckPostMaxSizeOrver()->OrThrow( 'PostMaxSizeOrver' );
			ConceptSystem::IsNotNull( $_GET[ 'type' ] )->OrThrow( 'InvalidQuery' );
			ConceptSystem::CheckType()->OrThrow( 'InvalidQuery' );
			ConceptSystem::CheckTableRegistUser()->OrThrow( 'IllegalAccess' );

			unset( $_SESSION[ 'previous_page' ] );
			unset( $_SESSION[ 'previous_page_admin' ] );

			if( !$this->action ) //要求処理が特定されていない場合
			{
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
			}

			parent::__construct();
			if($_GET["mid_id"]){
				$owner = SystemUtil::getTableData("mid",h($_GET["mid_id"]),"owner");
				Concept::IsTrue(pay_jobLogic::isAvailable($owner, "mid"))->OrThrow("expiredJob");
			}elseif($_GET["fresh_id"]){
				$owner = SystemUtil::getTableData("fresh",h($_GET["fresh_id"]),"owner");
				Concept::IsTrue(pay_jobLogic::isAvailable($owner, "fresh"))->OrThrow("expiredJob");
			}
		}
	}
