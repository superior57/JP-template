<?php

	//★クラス //

	/**
		@brief 既定の管理ツールのパッチ処理のモデル。
	*/
	class AppPatchModel extends AppBaseModel //
	{
		//■処理 //

		/**
			@brief パッチ適用処理を実行する。
		*/
		function doPatch( $iPatchName ) //
		{
			global $UPDATE_CLASS;
			global $UPDATE_METHOD;

			$class_name = $UPDATE_CLASS[ $iPatchName ];
			$method     = $UPDATE_METHOD[ $iPatchName ];

			$class = new $class_name;

			ob_start();

			if( method_exists( $class , $method ) )
				{ $class->$method(); }
			else
				{ print '<p>更新失敗：メソッドが存在しません。 (' . $class_name . '->' . $method . '</p>'; }

			$this->result = ob_get_clean();
		}

		var $result;
	}
