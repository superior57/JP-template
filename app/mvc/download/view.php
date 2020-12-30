<?php

	//★クラス //

	/**
		@brief 既定のデータ削除処理のビュー。
	*/
	class AppDownloadView extends AppBaseView //
	{
		function undefinedData($iModel){
			global $gm;
			ob_start();
			Template::drawTemplate($iModel->gm, null, $iModel->loginUserType, $iModel->loginUserRank, null, "UNDEFINED_DOWNLOAD_DATA");
			$this->drawContentsWithHeadFoot( $iModel , ob_get_clean() );
		}
	}
