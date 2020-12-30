<?php

	//★クラス //

	/**
		@brief 既定の静的ページのビュー。
	*/
	class AppnUserOtherView extends AppBaseView //
	{
		//■処理 //

		/**
			@brief     ページを出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function drawPage( $iModel ) //
			{
			$this->drawContentsWithHeadFoot( $iModel , $iModel->gm->getString( $iModel->staticTemplatePath , null , null ) ); }

		/**
			@brief     エラーページを出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function drawErrorPage( $iModel ) //
			{ $this->drawContentsWithHeadFoot( $iModel , $iModel->gm->getString( Template::getLabelFile( 'ERROR_PAGE_DESIGN' ) , null , null ) ); }
	}
