<?php

	//★クラス //

	/**
		@brief 既定のアップデート通知のコントローラ。
	*/
	class AppUpdateController extends AppBaseController //
	{
		//■処理 //

		/**
			@brief 終了要求への応答。
		*/
		function quit() //
			{ exit; }

		/**
			@brief アップデート確認要求への応答。
		*/
		function checkUpdate() //
			{ $this->model->saveUpdate(); }

		/**
			@brief アップデートスクリプト出力要求への応答。
		*/
		function updateScript() //
			{ $this->view->drawUpdateScriptCode( $this->model ); }

		//■データ取得 //

		/**
			@brief  コントローラの動作に必要なインクルードパスの一覧を取得する。
			@return インクルードパスの一覧。
		*/
		static function GetNeedIncludes() //
		{
			if( file_exists( './custom/extends/changeLogConf.php' ) ) //設定ファイルがある場合
			{
				return Array
				(
					'custom/head_main.php' ,
					'custom/extends/changeLogConf.php'
				);
			}

			return parent::GetNeedIncludes();
		}

		//■コンストラクタ・デストラクタ //

		/**
			@brief コンストラクタ。
		*/
		function __construct() //
		{
			global $UPDATE_NOTICE;
			global $loginUserType;

			if( 'save' == $_GET[ 'mode' ] ) //アップデートチェックが要求されている場合
				{ $this->action = 'checkUpdate'; }
			else //要求がない場合
				{ $this->action = 'updateScript'; }

			if( !$UPDATE_NOTICE || 'admin' != $loginUserType ) //実行権限がない場合
				{ $this->action = 'quit'; }

			parent::__construct();
		}
	}
