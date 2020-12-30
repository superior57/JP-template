<?php

	include_once './include/extends/Exception/Exception.php';

	/**
		@brief   例外ユーティリティクラス。
		@details 例外に関する関数をまとめたクラスです。
	*/
	class ExceptionManager
	{
		var  $_DEBUG	 = DEBUG_FLAG_EXCEPTION;

		function ExceptionHandler( $exception ){
			global $EXCEPTION_CONF;

			ob_end_clean();

			//エラーメッセージをログに出力
			$className = get_class( $exception );

			if( !in_array( $className , $EXCEPTION_CONF[ 'SecretExceptionType' ] ) )
			{
				$errorManager = new ErrorManager();
				$errorMessage = $errorManager->GetExceptionStr( $exception );
				$errorManager->OutputErrorLog( $errorMessage );
			}

			ExceptionManager::setHttpStatus( $className );

			//例外に応じてエラーページを出力
			if( $this->_DEBUG ){ d("DrawErrorPage:class ${className},message ".$exception->getMessage() ); }
			ExceptionManager::DrawErrorPage( $className );
		}

		/**
			@brief   例外エラーページを出力する。
			@details 例外の種類に応じてエラーテンプレートを出力します。\n
			         対応するテンプレートが見つからない場合は標準のエラーテンプレートが出力されます。
			@param   $className_ 例外オブジェクトのクラス名。
			@remarks 例外エラーテンプレートはtargetに小文字のクラス名、labelにEXCEPTION_DESIGNを指定します。
		*/
		static function DrawErrorPage( $className )
		{
			global $gm;
			global $loginUserType;
			global $loginUserRank;
			global $template_path;

			try
			{
				ob_start();

				System::$head = false;
				System::$foot = false;

				if( $_GET[ 'type' ] && !is_array( $_GET[ 'type' ] ) && $gm[ $_GET[ 'type' ] ] )
					$tGM = SystemUtil::getGMforType( $_GET[ 'type' ] );
				else
					$tGM = SystemUtil::getGMforType( 'system' );

				print System::getHead( $gm , $loginUserType , $loginUserRank );

				//例外オブジェクトのテンプレートを検索する

				$template = $template_path . 'other/exception/' . $className . '.html';

				if( !file_exists( $template ) ){
					$template = Template::getTemplate( $loginUserType , $loginUserRank , $className , 'EXCEPTION_DESIGN' );
				}

				if( $template && file_exists( $template ) )
					print $tGM->getString( $template );
				else
				{
					//Exceptionオブジェクトのテンプレートを検索する
					if( 'Exception' != $className )
						$template = Template::getTemplate( $loginUserType , $loginUserRank , 'exception' , 'EXCEPTION_DESIGN' );

					if( $template && file_exists( $template ) )
						print $tGM->getString( $template );
					else
						Template::drawErrorTemplate();
				}

				print System::getFoot( $gm , $loginUserType , $loginUserRank );

				System::flush();
			}
			catch( Exception $e_ )
			{
				ob_end_clean();

				print System::getHead( $gm , $loginUserType , $loginUserRank );
				Template::drawErrorTemplate();
				print System::getFoot( $gm , $loginUserType , $loginUserRank );
				System::flush();
			}
		}


		function setHttpStatus($className)
		{
			$header = "";
			switch($className)
			{
			case 'InvalidQueryException':
				$header = 'HTTP/1.0 400 Bad Request';
				break;
			case 'IllegalAccessException':
				$header = 'HTTP/1.0 403 Forbidden';
				break;
			case 'RecordNotFoundException':
				$header = 'HTTP/1.0 404 Not Found';
				break;
			}

			if( strlen($header) > 0 ) { header( $header ); }
		}
	}

	//ハンドラ登録
	function ExceptionManager_ExceptionHandler( $e )
	{
		$object = new ExceptionManager();
		$object->ExceptionHandler( $e );
	}

	set_exception_handler( 'ExceptionManager_ExceptionHandler' );

?>
