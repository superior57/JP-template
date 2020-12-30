<?php

	//★クラス //

	/**
		@brief   既定のAPIのモデル。
	*/
	class AppAPIModel extends AppBaseModel //
	{
		//■処理 //

		/**
			@brief APIを呼び出す。
		*/
		function doCoreAPI() //
		{
			global $USE_API_ALLOW_LIST_CHECK;
			global $USE_API_INHERIT_CHECK;
			global $API_ALLOW_LIST;

			$className  = 'Api_core';
			$methodName = h($this->param[ 'post' ]);
			$callable   = false;

			$this->param[ 'info_change_flg' ] = false;

			if( strlen( $this->param[ 'js' ] ) || strlen( $this->param[ 'jump' ] ) ) //フラグが設定されている場合
				{ $this->param[ 'info_change_flg' ] = true; }


			if( $USE_API_ALLOW_LIST_CHECK ) //ホワイトリストチェックを使う場合
			{
				if( isset( $API_ALLOW_LIST[ $className ] ) && ( $API_ALLOW_LIST[ $className][ 0 ] == 'all' || array_search( $methodName , $API_ALLOW_LIST[ $className ] ) ) ) //ホワイトリストに登録されている場合
					{ $callable = true; }
			}

			if( !$callable && $USE_API_INHERIT_CHECK ) //継承チェックを使う場合
			{
				$parentName = get_parent_class( $className );

				while( FALSE != get_parent_class( $parentName ) ) //親クラスが取れる間繰り返し
					{ $parentName = get_parent_class( $parentName ); }

				if( 'apiClass' == $parentName ) //API用のクラスの場合
					{ $callable = true; }
			}

			if( !$callable ) //呼び出し権限がない場合
				{ throw new LogicException( $className . '->' . $methodName . ' はAPIとして起動することはできません' ); }

			$api = new $className();
			$api->$methodName( $this->param );
		}

		/**
			@brief APIを呼び出す。
		*/
		function doExtendAPI() //
		{
			global $USE_API_ALLOW_LIST_CHECK;
			global $USE_API_INHERIT_CHECK;
			global $API_ALLOW_LIST;

			$className = $this->param[ 'c' ];

			switch( $this->param[ 't' ] ) //APIの種類で分岐
			{
				case 'mod' : //mod系
				default    : //その他
				{
					$className = 'mod_' . $className;
					break;
				}

				case 'view': //view系
				{
					$className = $className . 'View';
					break;
				}

				case 'none': //修飾なし
					{ break; }
			}

			$methodName = h($this->param[ 'm' ]);
			$callable   = false;

			unset( $this->param[ 'm' ]);
			unset( $this->param[ 'c' ]);
			unset( $this->param[ 't' ]);
			unset( $_POST );

			if( $USE_API_ALLOW_LIST_CHECK ) //ホワイトリストチェックを使う場合
			{
				if( isset( $API_ALLOW_LIST[ $className ] ) && ( $API_ALLOW_LIST[ $className][ 0 ] == 'all' || array_search( $methodName , $API_ALLOW_LIST[ $className ] ) ) ) //ホワイトリストに登録されている場合
					{ $callable = true; }
			}

			if( !$callable && $USE_API_INHERIT_CHECK ) //継承チェックを使う場合
			{
				$parentName = get_parent_class( $className );

				while( FALSE != get_parent_class( $parentName ) ) //親クラスが取れる間繰り返し
					{ $parentName = get_parent_class( $parentName ); }

				if( 'apiClass' == $parentName ) //API用のクラスの場合
					{ $callable = true; }
			}

			if( !$callable ) //呼び出し権限がない場合
				{ throw new LogicException( $className . '->' . $methodName . ' はAPIとして起動することはできません' ); }

			$api = new $className();
			$api->$methodName( $this->param );
		}

		//■コンストラクタ・デストラクタ //

		/**
			@brief コンストラクタ。
		*/
		function __construct() //
		{
			if( isset( $_GET[ 'get_p' ] ) ) //GETクエリを使用する場合
				{ $this->param = $_GET; }
			else //POSTクエリを使用する場合
				{ $this->param = $_POST; }
		}
	}
