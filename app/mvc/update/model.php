<?php

	//★クラス //

	/**
		@brief   既定のアップデート通知のモデル。
	*/
	class AppUpdateModel extends AppBaseModel //
	{
		//■処理 //

		/**
			@brief アップデート情報のキャッシュを更新する。
		*/
		function saveUpdate() //
		{
			$message = '//パッケージの更新情報取得機能に使用するファイルです。削除すると次の更新チェックまで表示されなくなってしまいますのでご注意下さい。' . "\n";

			file_put_contents( $this->updateLogFile , $message . file_get_contents( $this->updateCheckURL ) );
			chmod( $this->updateLogFile , 0777 );

			setcookie( 'changeLogCache' , 'true' , time() + 60 * 60 * $this->updateInterval );
		}

		//■コンストラクタ・デストラクタ //

		/**
			@brief コンストラクタ。
		*/
		function __construct() //
		{
			global $CHANGELOG_OUTPUT_KEY;

			$this->updateLogFile  = './logs/version.txt';
			$this->updateCheckURL = 'https://www.ws-download.net/log.php?key=' . $CHANGELOG_OUTPUT_KEY;
			$this->updateInterval = 24;
		}

		//■変数 //
		var $updateLogFile  = null; ///<アップデート情報のキャッシュファイル。
		var $updateCheckURL = null; ///<アップデート情報を取得するURL。
		var $updateInterval = null; ///<アップデート確認処理の実行間隔(時)。
	}
