<?php

	//★クラス //

	/**
		@brief   既定のデータ削除処理のモデル。
	*/
	class AppDownloadModel extends AppBaseModel //
	{
		function doDownload(){
			$className = 'dl_' . $this->type;
			$methodName = $this->param[ 'm' ];

			unset( $this->param[ 'm' ]);
			unset( $_POST );

			$dl = new $className($this->param);
			$dl->$methodName();
		}

		//■コンストラクタ・デストラクタ //
		/**
			@brief コンストラクタ。
		*/
		function __construct() //
		{
			global $gm;

			parent::__construct();

			$this->type 	= $_GET[ 'type' ];
			$this->db   	= $this->gm->getDB();
			$this->param 	= ( empty($_POST) ? $_GET : $_POST );
		}


		//■変数 //
		var     $type            = null;  ///<ターゲットタイプ。
		var     $db              = null;  ///<DBインスタンス。
		var 	$param			 = null;
	}
