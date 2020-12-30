<?php

	//★クラス //

	/**
		@brief ビューの基底クラス。
	*/
	abstract class AppBaseView //
	{
		//■処理 //

		/**
			@brief     ページを出力する。
			@param[in] $iModel    modelインスタンス。
			@param[in] $iContents ページの内容。
		*/
		function drawContentsWithHeadFoot( $iModel , $iContents ) //
		{
			print System::getHead( $gm , $iModel->loginUserType , $iModel->loginUserRank );
			print $iContents;
			print System::getFoot( $gm , $iModel->loginUserType , $iModel->loginUserRank );
		}
	}
