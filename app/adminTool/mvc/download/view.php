<?php

	//★クラス //

	/**
		@brief 既定の管理ツールのダウンロード処理のビュー。
	*/
	class AppDownloadView extends AppBaseView //
	{
		//■処理 //

		/**
			@brief ダウンロード用ヘッダを出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function drawDownloadHeader( $iModel ) //
		{
			$main = new TableName( $_GET[ 'type' ] );
			$path = $main->exportFile();

			header( 'Cache-Control: no-cache, must-revalidate' );
			header( 'Pragma: no-cache' );
			header( 'Content-Type: application/octet-stream' );
			header( 'Content-Disposition: attachment; filename="' . strtolower( $_GET[ 'type' ] ) . '.csv"' );
			header( 'Content-Length: ' . filesize( $path ) );
			readfile( $path );
		}
	}
