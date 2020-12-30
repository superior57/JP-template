<?php

	//★クラス //

	/**
		@brief 既定のインデックスページのビュー。
	*/
	class AppIndexView extends AppBaseView //
	{
		//■処理 //

		/**
			@brief     インデックスページの画面を出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function drawIndexPage( $iModel ) //
		{
			ob_start();

			Template::drawTemplate( $iModel->gm , $iModel->rec , $iModel->loginUserType , $iModel->loginUserRank , '' , 'TOP_PAGE_DESIGN' );

			$this->drawContentsWithHeadFoot( $iModel , ob_get_clean() );
		}
	}
