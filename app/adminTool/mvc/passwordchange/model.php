<?php

	//★クラス //

	/**
		@brief 既定の管理ツールのパスワード変更処理のモデル。
	*/
	class AppPasswordChangeModel extends AppBaseModel //
	{
		//■処理 //

		/**
			@brief パスワードを変更する。
		*/
		function doChangePassword() //
		{
			if( !array_key_exists( 'currentPassword' , $_POST ) || !$_POST[ 'currentPassword' ] ) //パスワードが送信されていない場合
				{ $this->errors[ 'currentPassword' ] = true; }
			else //パスワードが送信されている場合
			{
				$password = GetToolPassword();

				if( $password != md5( $_POST[ 'currentPassword' ] ) ) //パスワードが一致しない場合
					{ $this->errors[ 'currentPassword_confirm' ] = true; }
			}

			if( !array_key_exists( 'newPassword' , $_POST ) || !$_POST[ 'newPassword' ] ) //新しいパスワードが送信されていない場合
				{ $this->errors[ 'newPassword' ] = true; }

			if( count( $this->errors ) ) //エラーがある場合
				{ return false; }

			return UpdateToolPassword( $_POST[ 'newPassword' ] );
		}
	}
