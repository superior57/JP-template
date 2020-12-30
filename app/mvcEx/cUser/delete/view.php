<?php

	//★クラス //

	/**
		@brief 既定のデータ削除処理のビュー。
	*/
	class AppcUserDeleteView extends AppDeleteView //
	{
		function drawNoResignPage($iModel){
			global $gm;

			print System::getHead( $gm , $iModel->loginUserType , $iModel->loginUserRank );

			Template::drawTemplate( $gm[ $_GET['type'] ] , $iModel->rec , $iModel->loginUserType , $iModel->loginUserRank , $_GET['type'] , 'NO_RESIGN_PAGE' );

			print System::getFoot( $gm , $iModel->loginUserType , $iModel->loginUserRank );

		}
	}
