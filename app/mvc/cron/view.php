<?php

	//★クラス //

	/**
		@brief 既定のCRONのビュー。
	*/
	class AppCRONView extends AppBaseView //
	{
		/**
			@brief     インデックスページにリダイレクトする。
			@param[in] $iModel modelインスタンス。
		*/
		function redirectIndex( $iModel ) //
			{ header( 'Location: index.php' ); }
	}
