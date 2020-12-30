<?php

	//★クラス //

	/**
		@brief 既定のパーミッション設定ツールのコントローラ。
	*/
	class AppPermitController extends AppBaseController //
	{
		//■処理 //

		/**
			@brief パーミッション設定要求への応答。
		*/
		function index() //
		{
			$this->model->connectFTP();

			if( !$this->model->canConnect() ) //接続に失敗した場合
				{ $this->view->drawConnectFailedMessage( $this->model ); }
			else //接続に成功した場合 //
			{
				$this->model->updatePermit();

				$this->view->drawUpdateResultMessage( $this->model );
			}

		}

		//■データ取得

		/**
			@brief  コントローラの動作に必要なインクルードパスの一覧を取得する。
			@return インクルードパスの一覧。
		*/
		static function GetNeedIncludes() //
		{
			return Array
			(
				'custom/extends/ftpConf.php' ,
				'custom/logic/ftpLogic.php' ,
				'custom/model/ftp.php'
			);
		}
	}
