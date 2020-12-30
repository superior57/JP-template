<?php

	//★クラス //

	/**
		@brief 既定のユーザー認証操作のビュー。
	*/
	class AppActivateView extends AppBaseView //
	{
		//■処理 //

		/**
			@brief     アクティベート完了ページを出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function drawSucceededActivatePage( $iModel ) //
		{
			global $gm;

			ob_start();

			$iModel->sys->drawActivateComp( $gm , $iModel->rec , $iModel->loginUserType , $iModel->loginUserRank );

			$this->drawContentsWithHeadFoot( $iModel , ob_get_clean() );
		}

		/**
			@brief     アクティベート失敗ページを出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function drawFailedActivatePage( $iModel ) //
		{
			global $gm;

			ob_start();

			$iModel->sys->drawActivateFaled( $gm , $iModel->rec , $iModel->loginUserType , $iModel->loginUserRank );

			$this->drawContentsWithHeadFoot( $iModel , ob_get_clean() );
		}
	}
