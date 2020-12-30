<?php

	//★クラス //

	/**
		@brief 既定の管理ツールのインストールウィザードのモデル。
	*/
	class AppInstallModel extends AppBaseModel //
	{
		//■処理 //
		function doSkipInstall() //
			{ InstallStatus::DoSkip(); }
	}
