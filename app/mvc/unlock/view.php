<?php

	//★クラス //

	/**
		@brief 既定のログインロック解除フォームのビュー。
	*/
	class AppUnlockView extends AppBaseView //
	{
		//■処理 //

		/**
			@brief     ログインロック解除フォームページを出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function DrawUnlockFormPage( $iModel ) //
		{
			ob_start();

			Template::drawTemplate( $iModel->gm , $iModel->rec , '' , $iModel->loginUserRank , 'accountLock' , 'ACCOUNT_UNLOCK_PAGE_DESIGN' );

			$this->drawContentsWithHeadFoot( $iModel , ob_get_clean() );
		}

		/**
			@brief     ログインロック解除完了ページを出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function DrawSucceededUnlockPage( $iModel ) //
		{
			ob_start();

			Template::drawTemplate( $iModel->gm , $iModel->rec , '' , $iModel->loginUserRank , 'accountLock' , 'ACCOUNT_UNLOCK_SUCCESS_PAGE_DESIGN' );

			$this->drawContentsWithHeadFoot( $iModel , ob_get_clean() );
		}

		/**
			@brief     ログインロック解除失敗ページを出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function DrawFailedUnlockPage( $iModel ) //
		{
			ob_start();

			Template::drawTemplate( $iModel->gm , $iModel->rec , '' , $iModel->loginUserRank , 'accountLock' , 'ACCOUNT_UNLOCK_FAILED_PAGE_DESIGN' );

			$this->drawContentsWithHeadFoot( $iModel , ob_get_clean() );
		}
	}
