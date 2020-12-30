<?php

	//★クラス //

	/**
		@brief 既定の汎用データ登録ページのビュー。
	*/
	class AppnUserRegisterView extends AppRegisterView //
	{
		//■処理 //

		/**
			@brief     登録フォームページを出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function drawRegisterFormPage( $iModel ) //
		{
			global $gm;

			$iModel->gm->setForm( $iModel->rec );
			$iModel->gm->addHiddenForm( 'step' , $_POST[ 'step' ] );
			$iModel->gm->addHiddenForm( 'post' , 'input' );

			foreach( $iModel->gm->colStep as $column => $step ) //全ての手順設定を処理
			{
				if( $step && $iModel->step != $step ) //別の手順で入力されている場合
					{ $iModel->gm->addHiddenForm( $column , $_POST[ $column ] ); }
			}

			ob_start();

			$iModel->sys->drawRegistForm( $gm , $iModel->rec , $iModel->loginUserType , $iModel->loginUserRank );

			$this->drawContentsWithHeadFoot( $iModel , ob_get_clean() );
		}

		/**
			@brief     登録内容確認ページを出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function drawConfirmRegisterPage( $iModel ) //
		{
			global $gm;

			$iModel->gm->setForm( $iModel->rec );
			$iModel->gm->addHiddenForm( 'step' , $_POST[ 'step' ] );
			$iModel->gm->addHiddenForm( 'post' , 'register' );
			$iModel->gm->setHiddenFormRecord( $iModel->rec );

			ob_start();

			$iModel->sys->drawRegistCheck( $gm , $iModel->rec , $iModel->loginUserType , $iModel->loginUserRank );

			$this->drawContentsWithHeadFoot( $iModel , ob_get_clean() );
		}

		/**
			@brief     登録完了ページを出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function drawSucceededRegisterPage( $iModel ) //
		{
			global $gm;

			ob_start();

			$iModel->sys->drawRegistComp( $gm , $iModel->rec , $iModel->loginUserType , $iModel->loginUserRank );

			$this->drawContentsWithHeadFoot( $iModel , ob_get_clean() );
		}

		/**
			@brief     登録失敗ページを出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function drawFailedRegisterPage( $iModel ) //
		{
			global $gm;

			ob_start();

			$iModel->sys->drawRegistFaled( $gm , $iModel->loginUserType , $iModel->loginUserRank );

			$this->drawContentsWithHeadFoot( $iModel , ob_get_clean() );
		}
	}
