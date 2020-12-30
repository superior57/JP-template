<?php

	//★クラス //

	/**
		@brief 既定のユーザー認証のコントローラ。
	*/
	class AppActivateController extends AppBaseController //
	{
		//■処理 //

		/**
			@brief アカウント認証要求への応答。
		*/
		function doActivate() //
		{
			$this->model->doActivate();

			if( $this->model->succeededActivate() ) //認証に成功した場合
				{ $this->view->drawSucceededActivatePage( $this->model ); }
			else //認証に失敗した場合
				{ $this->view->drawFailedActivatePage( $this->model ); }
		}

		//■コンストラクタ・デストラクタ //

		/**
			@brief コンストラクタ。
		*/
		function __construct() //
		{
			ConceptSystem::IsNotNull( $_GET[ 'type' ] , $_GET[ 'id' ] , $_GET[ 'md5' ] )->OrThrow( 'InvalidQuery' );
			ConceptSystem::IsScalar( $_GET[ 'type' ] , $_GET[ 'id' ] , $_GET[ 'md5' ] )->OrThrow( 'InvalidQuery' );
			ConceptSystem::CheckType()->OrThrow( 'InvalidQuery' );
			ConceptSystem::CheckThisUserTable()->OrThrow( 'IllegalAccess' );

			if( 'admin' == $_GET[ 'type' ] ) //管理者を認証しようとした場合
					{ throw new IllegalAccessException( $_GET[ 'type' ] . 'は操作できません' ); }

			$this->action = 'doActivate';

			parent::__construct();
		}
	}
