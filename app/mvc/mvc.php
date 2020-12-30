<?php

	//★クラス //

	/**
		@brief MVC管理クラス。
	*/
	class MVC //
	{
		//■処理 //

		/**
			@brief     コントローラを起動する。
			@exception InvalidArgumentException $iControllerName , $iTableName に無効な値が指定された場合。
			@exception RuntimeException         コントローラのアクションを実行できない場合。
			@param[in] $iControllerName コントローラの名前。AppFooControllerのFooの部分を指定します。
			@param[in] $iTableName      テーブル名。
		*/
		static function Call( $iControllerName , $iTableName = Null ) //
		{
			if( !$iControllerName || preg_match( '/\W/' , $iControllerName ) ) //コントローラ名に無効な値が指定されている場合
				{ throw new InvalidArgumentException( '引数 $iControllerName は無効です [' . $iControllerName . ']' ); }

			if( $iTableName ) //テーブル名の指定がある場合
			{
				if( preg_match( '/\W/' , $iTableName ) ) //テーブル名に無効な値が指定されている場合
					{ throw new InvalidArgumentException( '引数 $iTableName は無効です [' . $iTableName . ']' ); }

				$controllerName = 'App' . $iTableName . $iControllerName . 'Controller';

				if( class_exists( $controllerName ) )
					{ $controller = new $controllerName() ; }
				else
				{
					$controllerName = 'App' . $iControllerName . 'Controller';
					$controller     = new $controllerName();
				}
			}
			else //テーブル名の指定がない場合
			{
				$controllerName = 'App' . $iControllerName . 'Controller';
				$controller     = new $controllerName();
			}

			$action = $controller->action;

			$controller->$action();
		}

		/**
			@brief     必要なファイルを読み込む。
			@param[in] $iClassName クラス名。
		*/
		static function Load( $iClassName ) //
		{
			if( preg_match( '/^App(\w+)Model$/' , $iClassName , $matches ) ) //Modelと一致する場合
			{
				foreach( self::GetPackedMVCTypes() as $type ) //全てのパックタイプを処理
				{
					if( preg_match( '/^' . $type . '(\w+)$/' , $matches[ 1 ] , $matchesEx ) ) //パックModelと一致する場合
					{
						if( is_file( self::$PackedMVCPath . $type . '/mvc/' . strtolower( $matchesEx[ 1 ] ) . '/model.php' ) ) //パックファイルがある場合
						{
							include_once self::$PackedMVCPath . $type . '/mvc/' . strtolower( $matchesEx[ 1 ] ) . '/model.php';
							return;
						}
					}
				}

				foreach( self::GetExtendMVCTypes() as $type ) //全ての拡張タイプを処理
				{
					if( preg_match( '/^' . $type . '(\w+)$/' , $matches[ 1 ] , $matchesEx ) ) //拡張Modelと一致する場合
					{
						if( is_file( self::$ExMVCPath . $type . '/' . strtolower( $matchesEx[ 1 ] ) . '/model.php' ) ) //拡張ファイルがある場合
						{
							include_once self::$ExMVCPath . $type . '/' . strtolower( $matchesEx[ 1 ] ) . '/model.php';
							return;
						}
					}
				}

				if( is_file( self::$CommonPackedMVCPath . strtolower( $matches[ 1 ] ) . '/model.php' ) ) //汎用パックmodelがある場合
				{
					include_once self::$CommonPackedMVCPath . strtolower( $matches[ 1 ] ) . '/model.php';
					return;
				}

				if( is_file( self::$MVCPath . strtolower( $matches[ 1 ] ) . '/model.php' ) ) //汎用modelがある場合
					{ include_once self::$MVCPath . strtolower( $matches[ 1 ] ) . '/model.php'; }

				return;
			}

			if( preg_match( '/^App(\w+)View$/' , $iClassName , $matches ) ) //Viewと一致する場合
			{
				foreach( self::GetPackedMVCTypes() as $type ) //全てのパックタイプを処理
				{
					if( preg_match( '/^' . $type . '(\w+)$/' , $matches[ 1 ] , $matchesEx ) ) //パックViewと一致する場合
					{
						if( is_file( self::$PackedMVCPath . $type . '/mvc/' . strtolower( $matchesEx[ 1 ] ) . '/view.php' ) ) //パックファイルがある場合
						{
							include_once self::$PackedMVCPath . $type . '/mvc/' . strtolower( $matchesEx[ 1 ] ) . '/view.php';
							return;
						}
					}
				}

				foreach( self::GetExtendMVCTypes() as $type ) //全ての拡張タイプを処理
				{
					if( preg_match( '/^' . $type . '(\w+)$/' , $matches[ 1 ] , $matchesEx ) ) //拡張Viewと一致する場合
					{
						if( is_file( self::$ExMVCPath . $type . '/' . strtolower( $matchesEx[ 1 ] ) . '/view.php' ) ) //拡張ファイルがある場合
						{
							include_once self::$ExMVCPath . $type . '/' . strtolower( $matchesEx[ 1 ] ) . '/view.php';
							return;
						}
					}
				}

				if( is_file( self::$CommonPackedMVCPath . strtolower( $matches[ 1 ] ) . '/view.php' ) ) //汎用パックViewがある場合
				{
					include_once self::$CommonPackedMVCPath . strtolower( $matches[ 1 ] ) . '/view.php';
					return;
				}

				if( is_file( self::$MVCPath . strtolower( $matches[ 1 ] ) . '/view.php' ) ) //汎用modelがある場合
					{ include_once self::$MVCPath . strtolower( $matches[ 1 ] ) . '/view.php'; }

				return;
			}

			if( preg_match( '/^App(\w+)Controller$/' , $iClassName , $matches ) ) //Controllerと一致する場合
			{
				foreach( self::GetPackedMVCTypes() as $type ) //全てのパックタイプを処理
				{
					if( preg_match( '/^' . $type . '(\w+)$/' , $matches[ 1 ] , $matchesEx ) ) //パックControllerと一致する場合
					{
						if( is_file( self::$PackedMVCPath . $type . '/mvc/' . strtolower( $matchesEx[ 1 ] ) . '/controller.php' ) ) //パックファイルがある場合
						{
							include_once self::$PackedMVCPath . $type . '/mvc/' . strtolower( $matchesEx[ 1 ] ) . '/controller.php';
							return;
						}
					}
				}

				foreach( self::GetExtendMVCTypes() as $type ) //全ての拡張タイプを処理
				{
					if( preg_match( '/^' . $type . '(\w+)$/' , $matches[ 1 ] , $matchesEx ) ) //拡張Controllerと一致する場合
					{
						if( is_file( self::$ExMVCPath . $type . '/' . strtolower( $matchesEx[ 1 ] ) . '/controller.php' ) ) //拡張ファイルがある場合
						{
							include_once self::$ExMVCPath . $type . '/' . strtolower( $matchesEx[ 1 ] ) . '/controller.php';
							return;
						}
					}
				}

				if( is_file( self::$CommonPackedMVCPath . strtolower( $matches[ 1 ] ) . '/controller.php' ) ) //汎用パックControllerがある場合
				{
					include_once self::$CommonPackedMVCPath . strtolower( $matches[ 1 ] ) . '/controller.php';
					return;
				}

				if( is_file( self::$MVCPath . strtolower( $matches[ 1 ] ) . '/controller.php' ) ) //汎用controllerがある場合
					{ include_once self::$MVCPath . strtolower( $matches[ 1 ] ) . '/controller.php'; }

				return;
			}
		}

		//■データ変更 //

		/**
			@brief     MVCファイルの格納パスを変更する。
			@exception InvalidArgumentException $iPath に存在しないパスが指定された場合。
			@param[in] $iPath ディレクトリのパス。
		*/
		static function SetMVCPath( $iPath ) //
		{
			if( !is_dir( $iPath ) ) //ディレクトリが存在しない場合
				{ throw new InvalidArgumentException( '引数 $iPath は無効です' ); }

			self::$MVCPath = $iPath;
		}

		/**
			@brief     拡張MVCファイルの格納パスを変更する。
			@exception InvalidArgumentException $iPath に存在しないパスが指定された場合。
			@param[in] $iPath ディレクトリのパス。
		*/
		static function SetExMVCPath( $iPath ) //
		{
			if( !is_dir( $iPath ) ) //ディレクトリが存在しない場合
				{ throw new InvalidArgumentException( '引数 $iPath は無効です' ); }

			self::$ExMVCPath = $iPath;
		}

		//■データ取得 //

		/**
			@brief     コントローラの動作に必要なインクルードパスの一覧を取得する。
			@param[in] $iControllerName コントローラの名前。AppFooControllerのFooの部分を指定します。
			@return    インクルードパスの一覧。
		*/
		static function GetNeedIncludes( $iControllerName ) //
		{
			$controllerName = 'App' . $iControllerName . 'Controller';

			if( !class_exists( $controllerName ) )
			{
				header( 'Location: index.php' );
				exit;
			}

			return call_user_func( Array( $controllerName , 'GetNeedIncludes' ) );
		}

		/**
			@brief  拡張MVCが存在するテーブル名の一覧を取得する。
			@return テーブル名の配列。
		*/
		private static function GetExtendMVCTypes() //
		{
			if( count( self::$ExtendMVCTypes ) ) //取得済みの場合
				{ return self::$ExtendMVCTypes; }

			if( !is_dir( self::$ExMVCPath ) ) //ディレクトリがない場合
				{ return self::$ExtendMVCTypes; }

			$dir = opendir( self::$ExMVCPath );

			while( $entry = readdir( $dir ) ) //全てのエントリを処理
			{
				if( '.' == $entry || '..' == $entry ) //無効なエントリの場合
					{ continue; }

				self::$ExtendMVCTypes[] = $entry;
			}

			return self::$ExtendMVCTypes;
		}

		/**
			@brief  モジュールパックMVCが存在するテーブル名の一覧を取得する。
			@return テーブル名の配列。
		*/
		private static function GetPackedMVCTypes() //
		{
			if( !count( self::$PackedMVCTypes ) )
			{
				$entries = glob( './module/*/meta.inc' );

				foreach( $entries as $entry )
				{
					preg_match( '/^\.\/module\/(\w+)\/meta.inc$/' , $entry , $matches );

					self::$PackedMVCTypes[] = $matches[ 1 ];
				}
			}

			return self::$PackedMVCTypes;
		}

		static private $MVCPath             = 'app/mvc/';    ///<MVCファイルの格納パス。
		static private $ExMVCPath           = 'app/mvcEx/';  ///<拡張MVCファイルの格納パス。
		static private $PackedMVCPath       = 'module/';     ///<パックMVCファイルの格納パス。
		static private $CommonPackedMVCPath = 'module/mvc/'; ///<汎用パックMVCファイルの格納パス。
		static private $ExtendMVCTypes      = Array();       ///<拡張MVC対応テーブルの一覧。
		static private $PackedMVCTypes      = Array();       ///<パックMVC対応テーブルの一覧。
	}

	spl_autoload_register( array( 'MVC' , 'Load' ) );
