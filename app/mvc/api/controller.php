<?php

	//★クラス //

	/**
		@brief 既定のAPIのコントローラ。
	*/
	class AppAPIController extends AppBaseController //
	{
		//■処理 //

		/**
			@brief API呼び出し要求への応答。
		*/
		function callCoreAPI() //
			{ $this->model->doCoreAPI(); }

		/**
			@brief API呼び出し要求への応答。
		*/
		function callExtendAPI() //
			{ $this->model->doExtendAPI(); }

		//■データ取得 //

		/**
			@brief  コントローラの動作に必要なインクルードパスの一覧を取得する。
			@return インクルードパスの一覧。
		*/
		static function GetNeedIncludes() //
		{
			return Array
			(
				'custom/head_main.php' ,
				'custom/api/core.php'
			);
		}

		//■コンストラクタ・デストラクタ //

		/**
			@brief コンストラクタ。
		*/
		function __construct() //
		{
			$param = ( $_GET[ 'get_p' ] ? $_GET : $_POST );

			if( isset( $param[ 'c' ] ) ) //クラス指定がある場合
			{
				Concept::IsNotNull( $param[ 'c' ] , $param[ 'm' ] )->OrThrow( 'InvalidQuery' );

				$this->action = 'callExtendAPI';
			}
			else //クラス指定がない場合
			{
				Concept::IsNotNull( $param[ 'post' ] )->OrThrow( 'InvalidQuery' );

				$this->action = 'callCoreAPI';
			}

			parent::__construct();
		}
	}
