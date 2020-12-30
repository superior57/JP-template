<?php

	//★クラス //

	/**
		@brief 既定の汎用データ復元フォームのコントローラ。
	*/
	class AppRestoreController extends AppBaseController //
	{
		//■処理 //

		/**
			@brief 復元内容確認要求への応答。
		*/
		function confirmRestore() //
			{ $this->view->drawConfirmRestorePage( $this->model ); }

		/**
			@brief 復元実行要求への応答。
		*/
		function doRestore() //
		{
			$this->model->doRestore();

			$this->view->drawRestoreCompletePage( $this->model );
		}

		//■コンストラクタ・デストラクタ //

		/**
			@brief コンストラクタ。
		*/
		function __construct() //
		{
			ConceptSystem::IsNotNull( $_GET[ 'type' ] )->OrThrow( 'InvalidQuery' );
			ConceptSystem::CheckType()->OrThrow( 'InvalidQuery' );
			ConceptSystem::CheckLoginType( 'admin' )->OrThrow( 'IllegalAccess' );

			unset( $_SESSION[ 'previous_page' ] );
			unset( $_SESSION[ 'previous_page_admin' ] );

			if( $_POST[ 'post' ] ) //POSTクエリが存在する場合
			{
				if( 'restore' == $_POST[ 'post' ] ) //復元処理の実行が要求されている場合
					{ $this->action = 'doRestore'; }
				else //要求がない場合
					{ $this->action = 'confirmRestore'; }
			}
			else //要求がない場合
				{ $this->action = 'confirmRestore'; }
		}
	}
