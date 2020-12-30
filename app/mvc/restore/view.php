<?php

	//★クラス //

	/**
		@brief 既定の汎用データ復元フォームのビュー。
	*/
	class AppRestoreView extends AppBaseView //
	{
		//■処理 //

		/**
			@brief     復元確認画面を出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function drawConfirmFormPage( $iModel ) //
		{
			global $gm;

			$iModel->gm->addHiddenForm( 'post' , 'restore' );

			ob_start();

			$iModel->sys->drawRestoreCheck( $gm , $iModel->rec , $iModel->loginUserType , $iModel->loginUserRank );

			$this->drawContentsWithHeadFoot( $iModel , ob_get_clean() );
		}

		/**
			@brief     復元完了画面を出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function drawRestoreCompletePage( $iModel ) //
		{
			global $gm;

			ob_start();

			$iModel->sys->drawRestoreComp( $gm , $iModel->rec , $iModel->loginUserType , $iModel->loginUserRank );

			$this->drawContentsWithHeadFoot( $iModel , ob_get_clean() );
		}
	}
