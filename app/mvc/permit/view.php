<?php

	//★クラス //

	/**
		@brief 既定のパーミッション設定ツールのビュー。
	*/
	class AppPermitView extends AppBaseView //
	{
		//■処理 //

		/**
			@brief     接続失敗メッセージを出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function drawConnectFailedMessage( $iModel ) //
			{ print '接続に失敗しました'; }

		/**
			@brief     処理結果のメッセージを出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function drawUpdateResultMessage( $iModel ) //
		{
			print '以下の処理を実行しました<br />';

			foreach( $iModel->results as $result ) //全ての結果を処理
				{ print 'chmod ' . $result . '<br />'; }
		}
	}
