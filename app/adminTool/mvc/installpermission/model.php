<?php

	//★クラス //

	/**
		@brief 既定の管理ツールの書き込み権限設定処理のモデル。
	*/
	class AppInstallPermissionModel extends AppBaseModel //
	{
		//■処理 //

		/**
			@brief 書き込み権限適用処理を実行する。
		*/
		function doInstall() //
		{
			$result = InstallLogic::SetPermissionByFunction();

			if( $result ) //書き込み権限の設定に成功した場合
				{ InstallStatus::Set( 'permission' , true ); }

			return $result;
		}

		//■データ取得 //

		/**
			@brief 書き込み権限の適用が成功したか確認する。
		*/
		function succeededInstall() //
			{ return ( 0 >= count( InstallLogic::GetNeedPermissionEntries() ) ); }
	}
