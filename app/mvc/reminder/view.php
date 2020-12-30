<?php

	//★クラス //

	/**
		@brief 既定のパスワードリマインダのビュー。
	*/
	class AppReminderView extends AppBaseView //
	{
		//■処理 //

		/**
			@brief リマインダフォームページを出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function drawReminderFormPage( $iModel ) //
		{
			$iModel->gm->addHiddenForm( 'post' , 'true' );

			if( !$_POST[ 'post' ] ) //パラメータが送信されていない場合
			{
				$iModel->gm->setVariable( 'error_msg' , '' );
				$iModel->gm->setVariable( 'error_style' , '' );
			}
			else //パラメータが送信されている場合
			{
				$error = Template::getTemplateString( $iModel->gm , null , 'reminder' , $this->loginUserRank , '' , 'SEND_FALED_DESIGN' , false , null , 'head' );

				if( !$_POST[ 'mail' ] ) //メールアドレスが空の場合
					{ $error .= Template::getTemplateString( $iModel->gm , null , 'reminder' , $this->loginUserRank , '' , 'SEND_FALED_DESIGN' , false , null , 'mail' ); }
				else //メールアドレスが入力されている場合
					{ $error .= Template::getTemplateString( $iModel->gm , null , 'reminder' , $this->loginUserRank , '' , 'SEND_FALED_DESIGN' , false , null , 'record' ); }

				$error .= Template::getTemplateString( $iModel->gm , null , 'reminder' , $this->loginUserRank , '' , 'SEND_FALED_DESIGN' , false , null , 'foot' );

				$iModel->gm->setVariable( 'error_msg' , $error );
				$iModel->gm->setVariable( 'error_style' , 'validate' );
			}

			ob_start();

			Template::drawTemplate( $iModel->gm , null , 'reminder' , $iModel->loginUserRank , '' , 'SEND_FORM_DESIGN' , 'reminder.php' );

			$this->drawContentsWithHeadFoot( $iModel , ob_get_clean() );
		}

		/**
			@brief リマインダ受付完了ページを出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function drawSucceededPasswordReissuePage( $iModel ) //
		{
			ob_start();

			Template::drawTemplate( $iModel->gm , null , 'reminder' , $iModel->loginUserRank , '' , 'SEND_COMP_DESIGN' , 'reminder.php' );

			$this->drawContentsWithHeadFoot( $iModel , ob_get_clean() );
		}
	}
