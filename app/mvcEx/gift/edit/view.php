<?php

	//★クラス //

	/**
		@brief 既定のデータ編集処理のビュー。
	*/
	class AppgiftEditView extends AppBaseView //
	{
		//■処理 //

		/**
			@brief     編集フォームページを出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function drawEditFormPage( $iModel ) //
		{
			global $gm;

			$iModel->gm->setForm( $iModel->rec );
			$iModel->gm->addHiddenForm( 'post' , 'input' );

			ob_start();

			$iModel->sys->drawEditForm( $gm , $iModel->rec , $iModel->loginUserType , $iModel->loginUserRank );

			$this->drawContentsWithHeadFoot( $iModel , ob_get_clean() );
		}

		/**
			@brief     編集内容確認ページを出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function drawConfirmEditPage( $iModel ) //
		{
			global $gm;

			$iModel->gm->setForm( $iModel->rec );
			$iModel->gm->addHiddenForm( 'post' , 'edit' );
			$iModel->gm->setHiddenFormRecordEdit( $iModel->rec );

			ob_start();

			$iModel->sys->drawEditCheck( $gm , $iModel->rec , $iModel->loginUserType , $iModel->loginUserRank );

			$this->drawContentsWithHeadFoot( $iModel , ob_get_clean() );
		}

		/**
			@brief     編集完了ページを出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function drawSucceededEditPage( $iModel ) //
		{
			global $gm;

			ob_start();

			$iModel->sys->drawEditComp( $gm , $iModel->rec , $iModel->loginUserType , $iModel->loginUserRank );

			$this->drawContentsWithHeadFoot( $iModel , ob_get_clean() );
		}

		/**
			@brief     編集失敗ページを出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function drawFailedEditPage( $iModel ) //
		{
			global $gm;

			ob_start();

			$iModel->sys->drawEditFaled( $gm , $iModel->loginUserType , $iModel->loginUserRank );

			$this->drawContentsWithHeadFoot( $iModel , ob_get_clean() );
		}
	}
