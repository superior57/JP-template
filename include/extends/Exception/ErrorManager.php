<?php

	/**
		@brief   エラー管理クラス。
		@details エラーログの出力と例外への変換を管理します。
	*/
	class ErrorManager
	{
		private $errorToException = null; ///<例外変換設定
		private $shutdownErrorLog = null; ///<致命的エラーのログ設定
		private $errorLogFile     = null; ///<ログを出力するファイル名
		private $workDirectory    = null; ///<スクリプトの動作パス
		private $maxlogFileSize   = 20971520; ///<ログファイルの最大サイズ

		/**
			@brief コンストラクタ。
		*/
		function __Construct()
		{
			global $EXCEPTION_CONF;

			$this->errorToException = $EXCEPTION_CONF[ 'UseErrorToException' ];
			$this->shutdownErrorLog = $EXCEPTION_CONF[ 'UseShutdownErrorLog' ];
			$this->errorLogFile     = $EXCEPTION_CONF[ 'ErrorLogFile' ];
			$this->workDirectory    = $EXCEPTION_CONF[ 'WorkDirectory' ];
		}

		/**
			@brief     エラーハンドラ。
			@exception ErrorException 例外変換が有効な場合。
			@exception Exception      例外変換が有効で、ErrorExceptionクラスが存在しない場合。
			@details   エラーメッセージが発生した場合の処理を記述します。
		*/
		function ErrorHandler( $errNo_ , $errStr_ , $errFile_ , $errLine_ , $errContext_ )
		{
			if( class_exists( 'ErrorException' ) )
				$exception = new ErrorException( $errStr_ );
			else
				$exception = new Exception( $errStr_ );

			$excStr = $this->GetExceptionStr( $exception );

			$this->OutputErrorLog( $excStr );

			if( $this->errorToException )
				throw $exception;
		}

		/**
			@brief   シャットダウンハンドラ。
			@details スクリプト終了時の処理を記述します。
		*/
		function ShutdownHandler()
		{
			if( $this->shutdownErrorLog )
			{
				$errStr = $this->GetFatalErrorStr();

				if( !is_null( $errStr ) )
					$this->OutputErrorLog( $errStr , $this->workDirectory . $this->errorLogFile );
			}
		}

		/**
			@brief 例外変換の有効・無効を設定する。
			@param $usage_ エラーメッセージを例外に変換する場合はtrue。変換しない場合はfalse。
		*/
		function SetErrorToException( $usage_ )
		{
			$this->ErrorToException = $usage_;
		}

		/**
			@brief   エラーメッセージ取得。
			@details 例外をエラーメッセージに変換して取得します。
			@param   $e_ 例外オブジェクト。
		*/
		function GetExceptionStr( $e_ )
		{
			//スタックトレースを取得する
			$array = $e_->getTrace();
			krsort( $array );

			$result  = "\t" . $e_->getMessage() . "\n\t\t" . preg_replace( '/(.*)\\\\([^\\\\]+)$/' , '($1) $2' , str_replace( getcwd() . '\\' , '' , $e_->getFile() ) ) . ' ' . $e_->getLine() . "\n\n";
			$result .= '▽trace : ' . "\n\n";

			$row = count( $array );

			//バックトレースと構造が違うので、引数をずらして対応
			for( $i = $row - 1 ; $i > 0 ; $i-- )
				$array[ $i ][ 'args' ] = $array[ $i - 1 ][ 'args' ];

			//呼び出し順に整形して格納
			foreach( $array as $trace )
			{
				if( array_key_exists( 'file' , $trace ) ){
					$file = sprintf( '%s %04d' , preg_replace( '/(.*)\\\\([^\\\\]+)$/' , '($1) $2' , str_replace( getcwd() . '\\' , '' , $trace[ 'file' ] ) ) , $trace[ 'line' ] );
				}else{
					$file = sprintf( '%s %04d' , preg_replace( '/(.*)\\\\([^\\\\]+)$/' , '($1) $2' , str_replace( getcwd() . '\\' , '' , $trace[ 'args' ][2] ) ) , $trace[ 'args' ][3] );
				}

				if( array_key_exists( 'function' , $trace ) )
				{
					$result .= "\t" . $trace[ 'function' ] . "\n";
					$result .= "\t\t" . $file . "\n";
				}
				else
				{
					$result .= "\t" . $trace[ 'line' ] . "\n";
					$result .= "\t\t" . $file . "\n";
				}

				//引数
				if( array_key_exists( 'args' , $trace ) && count( (array)$trace[ 'args' ] ) )
				{
					foreach( $trace[ 'args' ] as $key => $value )
					{
						if( is_object( $value ) )
							$result .= "\t\t\t" . sprintf( 'object   : %s' , get_class( $value ) ) . "\n";
						else if( is_array( $value ) )
							$result .= "\t\t\t" . sprintf( 'array    : %s' , count( $value ) ) . "\n";
						else
							$result .= "\t\t\t" . sprintf( '%-8s : %s' , gettype( $value ) , $value ) . "\n";
					}
				}

				$result .= "\n";
			}

			return $result;
		}

		/**
			@brief   エラーメッセージ取得。
			@details 例外をエラーメッセージに変換して取得します。
			@param   $e_ 例外オブジェクト。
		*/
		function GetFatalErrorStr()
		{
			if( function_exists( 'error_get_last' ) )
			{
				$error = error_get_last();
			}

			if( is_null( $error ) )
				return null;

			switch( $error[ 'type' ] )
			{
				case E_ERROR :
				case E_PARSE :
				case E_CORE_ERROR :
				case E_CORE_WARNING :
				case E_COMPILE_ERROR :
				case E_COMPILE_WARNING :
					$result  = 'fatal error : ' . $error[ 'message' ] . "\n";
					$result .= sprintf( '%s,%04d' , $error[ 'file' ] , $error[ 'line' ] ) . "\n";

					return $result;

				default :
					return null;
			}
		}

		/**
			@brief エラーログを出力する。
			@param $str_      エラーメッセージ。
			@param $filePath_ 出力するファイルのパス。
		*/
		function OutputErrorLog( $str_ , $filePath_ = null )
		{
			if( $filePath_ )
				{ $path = $filePath_; }
			else
				{ $path = $this->errorLogFile; }

			$fp = fopen( $path , 'a' );

			if( $fp )
			{
				fputs( $fp , date( '▼Y/n/j G:i:s' . "\n\n" ) );
				fputs( $fp , $str_ . "\n" );
				fputs( $fp , '-----------------------------------------------------' . "\n\n" );
				fclose( $fp );

				if( $this->maxlogFileSize < filesize( $this->errorLogFile ) ) //ログファイルの最大サイズを超えている場合
				{
					$nowDateString = date( '_Y_m_d_H_i_s' );

					rename( $path , $path . $nowDateString );

					$fp = fopen( $path , 'a' );

					fclose( $fp );
					chmod( $path, 0666 );
				}
			}
		}
	}

	//ハンドラ登録
	function ErrorManager_ErrorHandler( $errNo_ , $errStr_ , $errFile_ , $errLine_ , $errContext_ )
	{
		$object = new ErrorManager();
		$object->ErrorHandler( $errNo_ , $errStr_ , $errFile_ , $errLine_ , $errContext_ );
	}

	function ErrorManager_ShutdownHandler()
	{
		$object = new ErrorManager();
		$object->ShutdownHandler();
	}

	set_error_handler( 'ErrorManager_ErrorHandler' , $EXCEPTION_CONF[ 'ErrorHandlerLevel' ] );
	register_shutdown_function( 'ErrorManager_ShutdownHandler' );
	
