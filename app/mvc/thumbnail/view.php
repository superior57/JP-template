<?php

	//★クラス //

	/**
		@brief 既定のサムネイル中継APIのビュー。
	*/
	class AppThumbnailView extends AppBaseView //
	{
		//■処理 //

		/**
			@brief     インデックスページにリダイレクトする。
			@param[in] $iModel modelインスタンス。
		*/
		function redirectIndex( $iModel ) //
		{
			header( 'Location: index.php' );
			exit;
		}

		/**
			@brief     サムネイル画像にリダイレクトする。
			@param[in] $iModel モデルインスタンス。
		*/
		function redirectThubnailURL( $iModel ) //
		{
			header( 'Location: ' . $iModel->src, true, 301 );
			exit;
		}
	}
