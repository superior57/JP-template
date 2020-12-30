<?php

	//★クラス //

	/**
		@brief 既定の管理ツールのphpinfo表示処理のコントローラ。
	*/
	class AppPHPInfoController extends AppBaseController //
	{
		//■処理 //

		/**
			phpinfo表示要求への応答。
		*/
		function phpinfo() //
			{ $this->view->drawPHPInfo( $this->model ); }

		//■データ取得 //

		/**
			@brief  コントローラの動作に必要なインクルードパスの一覧を取得する。
			@return インクルードパスの一覧。
		*/
		static function GetNeedIncludes() //
			{ return Array(); }

		//■コンストラクタ・デストラクタ //

		/**
			@brief コンストラクタ。
		*/
		function __construct() //
		{
			if( $_SESSION[ 'loginedAdminTool' ] ) //ログインしている場合
				{ $this->action = 'phpinfo'; }
			else //ログインしていない場合
				{ $this->action = 'loginForm'; }

			parent::__construct();
		}
	}
