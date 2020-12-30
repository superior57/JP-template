<?php

	//★クラス //

	/**
		@brief 既定のサムネイル中継APIのコントローラ。
	*/
	class AppThumbnailController extends AppBaseController //
	{
		//■処理 //

		/**
			@brief 通常の処理。
		*/
		function index() //
			{ $this->view->redirectIndex( $this->model ); }

		/**
			@brief サムネイル出力要求への応答。
		*/
		function makeThumbnail() //
		{
			$this->model->makeThumbnail();

			$this->view->redirectThubnailURL( $this->model );
		}

		//■データ取得 //

		/**
			@brief  コントローラの動作に必要なインクルードパスの一覧を取得する。
			@return インクルードパスの一覧。
		*/
		static function GetNeedIncludes() //
		{
			return Array
			(
				'custom/conf.php' ,
				'custom/extends/filebaseConf.php',
				'custom/extends/systemConf.php' ,
				'include/base/CommandBase.php' ,
				'module/thumbnail.inc'
			);
		}

		//■コンストラクタ・デストラクタ //

		/**
			@brief コンストラクタ。
		*/
		function __construct() //
		{
			if( isset( $_GET[ 'src' ] ) )
				{ $this->action = 'makeThumbnail'; }
			else
				{ $this->action = 'index'; }

			parent::__construct();
		}
	}
