<?php

include "include/base/SystemBase.php";

/**
 * システムコールクラス
 * 
 * @author 丹羽一智
 * @version 1.0.0
 * 
 */
class System extends SystemBase
{
	/**********************************************************************************************************
	 * 汎用システム用メソッド
	 **********************************************************************************************************/
	
	
	
	function feed_load( &$gm , $rec , $args )
	{
		global $HOME;
		global $CONF_FEED_ENABLE;
		global $CONF_FEED_TABLES;
		global $CONF_FEED_OUTPUT_DIR;

		if( !$CONF_FEED_ENABLE )
			{ return; }

		foreach( $CONF_FEED_TABLES as $tableName )
		{
			$check = false;
			switch( $tableName )
			{
			case 'mid':
			case 'fresh':
				$check = Conf::checkData( 'job', 'feed', $tableName );
				break;
			case 'cUser':
			case 'nUser':
				$check = Conf::checkData( 'user', 'feed', $tableName );
				break;
			case 'press':
				$check = true;
				break;
			}		
			
			if( !$check ) { continue; }

			$rssPath = $CONF_FEED_OUTPUT_DIR . $tableName . '_rss.xml';

			if( is_file( $rssPath ) )
			{
				$gm       = GMList::getGM( $tableName );
				$template = Template::getTemplate( 'nobody' , 1 , $tableName , 'FEED_RSS_DESIGN' );
				$title    = $gm->getString( $template , null , 'head_title' );

				$this->addBuffer( '<link rel="alternate" href="' . $HOME . $rssPath . '" type="application/rss+xml" title="' . $title . '" />' . "\n" );
			}

			$atomPath = $CONF_FEED_OUTPUT_DIR . $tableName . '_atom.xml';

			if( is_file( $atomPath ) )
			{
				$gm       = GMList::getGM( $tableName );
				$template = Template::getTemplate( 'nobody' , 1 , $tableName , 'FEED_ATOM_DESIGN' );
				$title    = $gm->getString( $template , null , 'head_title' );

				$this->addBuffer( '<link rel="alternate" href="' . $HOME . $atomPath . '" type="application/rss+xml" title="' . $title . '" />' . "\n" );
			}
		}
	}
	
	
	
	
	
	

	/*
	 * 例外を自動の例外出力に回す前にキャッチして、内容によって別の処理に長し込む為のもの。
	 */
	static function manageExceptionView( $className ){
		global $gm;
		global $loginUserType;
		global $loginUserRank;
		global $NOT_LOGIN_USER_TYPE;
		global $THIS_TABLE_REGIST_USER;
        global $controllerName;

		if(is_null($gm)){
			// GUIManagerが生成される前のエラーなので、諦めて例外用のエラーを出している。
			return false;
		}
		
		switch($className){
			case "IllegalAccessException":
				//非ログインかどうか
				if( $loginUserType != $NOT_LOGIN_USER_TYPE ){
					return false;
				}
				
				//特定のユーザータイプでログインすれば見れるコンテンツかどうかをチェックする実装のサンプル。
				// index.php?app_controller=register&type=items に非ログインでアクセスした場合にメッセージを追加してログイン画面を表示しています。
				$type = $_GET['type'];
				//$db = $gm[$type]->getDB();
				if( $controllerName == "Register" && isset($THIS_TABLE_REGIST_USER[$type]) && array_search("cUser",$THIS_TABLE_REGIST_USER[$type]) !== false ){
				
					//ログインを促す場合。
					$gm[$type]->setVariable( 'message', "当該のページはログイン時にのみ表示可能なページです。" );
					
					Template::drawTemplate( $gm[$type] , $rec , $loginUserType , $loginUserRank , '' , 'LOGIN_PAGE_DESIGN' );
					return true;
				}
				
				//特にログイン後のページがない場合は、通常のエラー画面を表示する。
				
				break;
		}
		
		return false;
	}
	
	
}
