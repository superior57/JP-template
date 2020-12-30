<?php

	//★クラス //

	/**
		@brief 既定の詳細情報ページのビュー。
	*/
	class AppInfoView extends AppBaseView //
	{
		//■処理 //

		/**
			@brief     詳細情報ページを出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function drawInfoPage( $iModel ) //
		{
			global $gm;

			$iModel->gm->setForm( $iModel->rec );
			$iModel->gm->addHiddenForm( 'post' , 'true' );

			ob_start();

			$iModel->sys->drawInfo( $gm , $iModel->rec , $iModel->loginUserType , $iModel->loginUserRank );

			$this->drawContentsWithHeadFoot( $iModel , ob_get_clean() );
		}

		/**
			@brief     エラーページを出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function drawErrorPage( $iModel ) //
		{
			global $gm;

			ob_start();

			$iModel->sys->drawInfoError( $gm , $iModel->rec , $iModel->loginUserType , $iModel->loginUserRank );

			$this->drawContentsWithHeadFoot( $iModel , ob_get_clean() );
		}
	}
