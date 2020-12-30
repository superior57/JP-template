<?php
class SSLUtil{
	
	static function ssl_check(){
		global $CONFIG_SSL_ALWAYS_HTTPS;
		global $CONFIG_SSL_ON_CHECK_FILES;
		global $CONFIG_SSL_OUT_CHECK_FILES;
		global $CONFIG_SSL_DISABLE_REDIRECT_CHECK_FILES;
		global $CONFIG_SSL_ON_CHECK_USERS;
        global $CONFIG_SSL_ON_CHECK_CONTROLLER_NAME;
        global $CONFIG_SSL_OUT_CHECK_CONTROLLER_NAME;
        global    $CONFIG_SSL_DISABLE_REDIRECT_CONTROLLER_NAME;
        global $CONFIG_SSL_CHECK_CONTROLLER_NAME;
        global $controllerName;
		global $loginUserType;

		if( SystemUtil::getDataStak( 'SSLUtil_post' ) ){
			$_POST = SystemUtil::getDataStak( 'SSLUtil_stack' );
			$_FILES = SystemUtil::getDataStak( 'SSLUtil_file' );
			
			SystemUtil::deleteDataStak( 'SSLUtil_post' );
			SystemUtil::deleteDataStak( 'SSLUtil_stack' );
			SystemUtil::deleteDataStak( 'SSLUtil_file' );
		}

		if( $CONFIG_SSL_ALWAYS_HTTPS )
		{
			$scriptName = SystemInfo::GetScriptName();

			if( 'cron' == $controllerName || 'cron' == $scriptName )
				{ return; }

			self::scheme_check();

			return;
		}

        if( isset($CONFIG_SSL_CHECK_CONTROLLER_NAME) && $CONFIG_SSL_CHECK_CONTROLLER_NAME ){
            // mvc による app_controller 利用時の転送判定

			if( array_search( strtolower( $controllerName ), $CONFIG_SSL_DISABLE_REDIRECT_CONTROLLER_NAME ) !== FALSE )
				{ return; }

            if( array_search( $loginUserType , $CONFIG_SSL_ON_CHECK_USERS ) !== FALSE || array_search( strtolower( $controllerName ), $CONFIG_SSL_ON_CHECK_CONTROLLER_NAME ) !== FALSE ){
                self::scheme_check();
            }else if( array_search( strtolower( $controllerName ), $CONFIG_SSL_OUT_CHECK_CONTROLLER_NAME ) !== FALSE ){
                self::ssl_out();
            }
        }else{
            // スクリプト名を利用した転送判定
            $script = SystemInfo::GetScriptName();

			if( array_search( strtolower( $controllerName ), $CONFIG_SSL_DISABLE_REDIRECT_CHECK_FILES ) !== FALSE )
				{ return; }

            if( array_search( $loginUserType , $CONFIG_SSL_ON_CHECK_USERS ) !== FALSE || array_search( $script, $CONFIG_SSL_ON_CHECK_FILES ) !== FALSE ){
                self::scheme_check();
            }else if( array_search( $script, $CONFIG_SSL_OUT_CHECK_FILES ) !== FALSE ){
                self::ssl_out();
            }
        }
	}
	
	static function scheme_check(){
		global $CONFIG_SSL_ENABLE;
		global $CONFIG_SSL_MOBILE;
		global $terminal_type;
		global $HOME;

		if( $CONFIG_SSL_ENABLE && $_SERVER['HTTPS'] != 'on' ){
			if( $CONFIG_SSL_MOBILE || !$terminal_type ){
				
				//POSTがある場合はsessionに保存する。
				//※本来はHTTP status codeを307にする事により対処できる筈だが
				//　IE6,7,8のどれも対応していない。(将来的な対応に期待)
				if( count($_POST) ){
					SystemUtil::setDataStak( 'SSLUtil_post', true );
					SystemUtil::setDataStak( 'SSLUtil_stack', $_POST  );
					if( count($_FILES) ){
						foreach( $_FILES as $name => $file ){
							
							// 拡張子の取得
							preg_match( '/(\.\w*$)/', $file['name'], $tmp );
							$ext		 = strtolower(str_replace( ".", "", $tmp[1] ));
							
							// ファイルパスの作成
							$directory	 = 'file/tmp/';
							$fileName	 = $directory.'SSLUtil_stack_'.md5( time(). $file['name'] ).'.'.$ext;

							if( isset( $file[ 'is_big' ] ) )
								{ rename( $file[ 'tmp_name' ] , $fileName ); }
							else
								{ move_uploaded_file( $file[ 'tmp_name' ] , $fileName ); }

							$_FILES[ $name ]['tmp_name'] = $fileName;
						}
						SystemUtil::setDataStak( 'SSLUtil_file', $_FILES  );
					}
				}
				if( strlen($HOME) > 1)
				{
					$url = parse_url($HOME);
					SSLUtil::sessionLocation('https://' . $url['host'] .$_SERVER["REQUEST_URI"]);
					exit();
				}
			}
		}
	}
	
	static function ssl_out(){
		global $CONFIG_SSL_ENABLE;
		global $CONFIG_SSL_MOBILE;
		global $terminal_type;
		global $HOME;
		
		/*		POSTがある時は解除しない		*/
		if( count( $_POST ) )
			return;

		if( !$CONFIG_SSL_ENABLE )
			{ return false; }
		
		if( strlen($HOME) > 1 && isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ){
					$url = parse_url($HOME);
					SSLUtil::sessionLocation('http://' . $url['host'] .$_SERVER["REQUEST_URI"]);
			exit();
		}
	}
	
	static function change_ssl($url){
		$parses = parse_url($url);
	}

	private function sessionLocation( $url ){
		global $terminal_type;
		global $sid;

		header( "HTTP/1.1 301 Moved Permanently" ); 
		if($terminal_type && strpos($url, "PHPSESSID") === false && strlen($sid) ){
			if( strpos($url, "?") === false){
				header( "Location: ".$url."?".$sid );
			}else{
				header( "Location: ".$url."&".$sid );
			}
		}else{
			header( "Location: ".$url );
		}
	}
}
?>