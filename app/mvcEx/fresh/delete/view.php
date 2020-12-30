<?php

	//★クラス //

	/**
		@brief 既定のデータ削除処理のビュー。
	*/
	class AppfreshDeleteView extends AppDeleteView //
	{
		//■処理 //

		/**
			@brief     入力画面を出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function drawDeleteFormPage( $iModel ) //
		{
			global $gm;

			$iModel->gm->setForm( $iModel->rec );
			$iModel->gm->addHiddenForm( 'del_edit' , 'true' );
			$iModel->gm->addHiddenForm( 'post' , 'input' );

			print System::getHead( $gm , $iModel->loginUserType , $iModel->loginUserRank );

			$iModel->sys->drawDeleteForm( $gm , $iModel->rec , $iModel->loginUserType , $iModel->loginUserRank );

			print System::getFoot( $gm , $iModel->loginUserType , $iModel->loginUserRank );
		}

		/**
			@brief     削除確認画面を出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function drawConfirmDeletePage( $iModel ) //
		{
			global $gm;

			$iModel->gm->setForm( $iModel->rec );
			$iModel->gm->addHiddenForm( 'post' , 'delete' );

			if( $_POST[ 'del_edit' ] ) //入力フォームを使う場合
				{ $iModel->gm->addHiddenForm( 'del_edit' , 'true' ); }

			print System::getHead( $gm , $iModel->loginUserType , $iModel->loginUserRank );

			$iModel->sys->drawDeleteCheck( $gm , $iModel->rec , $iModel->loginUserType , $iModel->loginUserRank );

			print System::getFoot( $gm , $iModel->loginUserType , $iModel->loginUserRank );
		}

		/**
			@brief     削除成功画面を出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function drawSucceededDeletePage( $iModel ) //
		{
			global $gm;

			print System::getHead( $gm , $iModel->loginUserType , $iModel->loginUserRank );

			$iModel->sys->drawDeleteComp( $gm , $iModel->rec , $iModel->loginUserType , $iModel->loginUserRank );

			print System::getFoot( $gm , $iModel->loginUserType , $iModel->loginUserRank );
		}

		/**
			@brief     削除失敗画面を出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function drawFailedDeletePage( $iModel ) //
		{
			global $gm;

			print System::getHead( $gm , $iModel->loginUserType , $iModel->loginUserRank );

			$iModel->sys->drawDeleteFaled( $gm , $iModel->loginUserType , $iModel->loginUserRank );

			print System::getFoot( $gm , $iModel->loginUserType , $iModel->loginUserRank );
		}
	}
