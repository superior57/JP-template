<?php

	//★クラス //

	/**
		@brief 既定のログインフォームのビュー。
	*/
	class AppLoginView extends AppBaseView //
	{
		//■処理 //

		/**
			@brief     ログインフォームページを出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function drawLoginFormPage( $iModel ) //
		{
			ob_start();

			Template::drawTemplate( $iModel->gm , $iModel->rec , $iModel->loginUserType , $iModel->loginUserRank , '' , 'LOGIN_PAGE_DESIGN' );

			$this->drawContentsWithHeadFoot( $iModel , ob_get_clean() );
		}

		/**
			@brief     リダイレクトヘッダを出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function redirectLoginResultURL( $iModel ) //
			{ SystemUtil::innerLocation( $iModel->loginResultURL ); }

		/**
			@brief     トップページへのリダイレクトヘッダを出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function redirectIndexURL( $iModel ) //
			{ SystemUtil::innerLocation( 'index.php' ); }

		/**
			@brief     ログイン失敗ページを出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function drawFailedLoginPage( $iModel ) //
		{
			ob_start();

			Template::drawTemplate( $iModel->gm , $iModel->rec , $iModel->loginUserType , $iModel->loginUserRank , '' , 'LOGIN_FALED_DESIGN' );

			$this->drawContentsWithHeadFoot( $iModel , ob_get_clean() );
		}

		/**
			@brief     アカウントロックページを出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function drawDenyLoginPage( $iModel ) //
		{
			ob_start();

			Template::drawTemplate( $iModel->gm , $iModel->rec , $iModel->loginUserType , $iModel->loginUserRank , '' , 'LOGIN_LOCK_DESIGN' );

			$this->drawContentsWithHeadFoot( $iModel , ob_get_clean() );
		}
	}
