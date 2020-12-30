<?php

	//★クラス //

	/**
		@brief 既定の詳細情報ページのビュー。
	*/
	class AppPreviewView extends AppBaseView //
	{
		//■処理 //

		/**
			@brief     詳細情報ページを出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function drawPreview( $iModel ) //
		{
			global $loginUserType;
			global $loginUserRank;
			global $gm;

			$loginUserType = $iModel->loginUserType;
			$loginUserRank = $iModel->loginUserRank;

			ob_start();

			$iModel->gm->setForm( $iModel->rec );
			$iModel->gm->addHiddenForm( 'post' , 'true' );
			$iModel->gm->setVariable( 'prevID' , '' );
			$iModel->gm->setVariable( 'nextID' , '' );

			$iModel->sys->drawInfo( $gm , $iModel->rec , $iModel->loginUserType , $iModel->loginUserRank );

			$this->drawContentsWithHeadFoot( $iModel , ob_get_clean() );
		}
	}
