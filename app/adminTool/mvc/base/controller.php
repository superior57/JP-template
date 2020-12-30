<?php

	//★クラス //

	/**
		@brief コントローラの基底クラス。
	*/
	abstract class AppBaseController //
	{
		//■データ取得 //

		/**
			@brief  コントローラの動作に必要なインクルードパスの一覧を取得する。
			@return インクルードパスの一覧。
		*/
		static function GetNeedIncludes() //
		{
			global $SQL_MASTER;

			if( 'MySQLDatabase' == $SQL_MASTER ) //MySQLを使用する場合
				{ $path = 'mysql'; }
			else //SQLiteを使用する場合
				{ $path = 'sqlite'; }

			return Array(
				'app/adminTool/lib/' . $path . '/query.php' ,
				'app/adminTool/lib/' . $path . '/queryWriter.php' ,
			);
		}

		//■コンストラクタ・デストラクタ //

		/**
			@brief コンストラクタ。
		*/
		function __construct() //
		{
			if( !$this->action ) //要求処理が特定されていない場合
				{ $this->action = ( isset( $_GET[ 'app_action' ] ) ? $_GET[ 'app_action' ] : 'index' ); }

			$modelName = preg_replace( '/^App(\w+)Controller$/' , 'App$1Model' , get_class( $this ) );

			if( !$this->model ) //modelが生成されていない場合
				{ $this->model = new $modelName(); }

			$viewName = preg_replace( '/^App(\w+)Controller$/' , 'App$1View' , get_class( $this ) );

			if( !$this->view ) //modelが生成されていない場合
				{ $this->view = new $viewName(); }
		}

		//■変数 //
		var $action = null; ///<要求された操作。
		var $model  = null; ///<modelインスタンス。
		var $view   = null; ///<viewインスタンス。
	}
