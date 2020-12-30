<?php

	//★クラス //

	/**
		@brief 既定の管理ツールのphpinfo表示処理のビュー。
	*/
	class AppPHPInfoView extends AppBaseView //
	{
		//■処理 //

		/**
			@brief phpinfo画面を出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function drawPHPInfo( $iModel ) //
			{ phpinfo(); }
	}
