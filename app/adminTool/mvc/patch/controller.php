<?php

	//★クラス //

	/**
		@brief 既定の管理ツールのパッチ処理のコントローラ。
	*/
	class AppPatchController extends AppBaseController //
	{
		//■処理 //

		/**
			@brief パッチ選択フォーム表示要求への応答。
		*/
		function drawPatchSelectForm() //
			{ $this->view->drawPatchFormPage( $this->model ); }

		/**
			@brief パッチ適用確認要求への応答。
		*/
		function confirmPatch() //
			{ $this->view->drawConfirmPatchPage( $this->model ); }

		/**
			@brief パッチ適用要求への応答。
		*/
		function doPatch() //
		{
			$this->model->doPatch( $_POST[ 'patchName' ] );
			$this->view->drawSucceededPatchPage( $this->model );
		}

		/**
			ログインフォーム表示要求への応答。
		*/
		function drawLoginForm() //
			{ $this->view->drawLoginForm( $this->model ); }

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
				'custom/tool/tool.inc' ,
			);
		}

		//■コンストラクタ・デストラクタ //

		/**
			@brief コンストラクタ。
		*/
		function __construct() //
		{
			if( !$_SESSION[ 'loginedAdminTool' ] ) //ログインしていない場合
			{
				$this->action = 'drawLoginForm';
				parent::__construct();
				return;
			}

			if( $_POST[ 'method' ] ) //要求がある場合
				{ $this->action = $_POST[ 'method' ]; }
			else //要求がない場合
				{ $this->action = 'drawPatchSelectForm'; }

			parent::__construct();
		}
	}
