<?php


/*******************************************************************************************************
 * <PRE>
 *
 * 汎用のラメータ設定で分岐する機能の拡張用クラス
 *
 * @version 1.0.0
 *
 * </PRE>
 *******************************************************************************************************/


class ExtensionBase extends command_base
{	
	
	// lstのExtendカラムの内容を受けて、テキスト('string','varchar','char')の置換を行なう場合の拡張
	// GUIManager::replaceString
	static function GUIManager_replaceStringParam($method,$val,&$before,&$after,$replace_type){
		global $SYSTEM_CHARACODE;
        switch($method){
        	default:
        		//入力内容が1文字の場合は、指定された文字を全角にする。
        		if( strlen($method) == 1 ){
        			$before[] = $method;
        			$after[]  =  mb_convert_kana( $method, A, $SYSTEM_CHARACODE );
        			$replace_type = "my";
        		}else{
        		}
        		break;
        	case 'html':
        		$replace_type = "";
        		break;
        }
		return $replace_type;
	}
	static function GUIManager_replaceStringExecute( $replace_type, $before, $after, $str ){
        switch( $replace_type ){
        	case "my":
        	case "nohtml":
        		$str = str_replace( $before, $after, $str );
        		break;
        	default:
	        	break;
        }
		return $str;
	}
	
	// lstのExtendカラムの内容を受けて、レコードの内容を置き換える
	static function Database_registExtension( $param, $str ){
		if(!empty($param) )
		{
			$params = explode('/', $param );
	
			foreach( $params as $p ){
		        switch( $p ){
		        	case "updatetime":
		        		$str = time();
		        		break;
		        	default:
			        	break;
		        }
	        	
			}
		}
		return $str;
	}
	
	// lstのExtendカラムの内容を受けて、レコードの内容を置き換える
	static function Database_updateExtension( $param, $str )
	{
		if(!empty($param) )
		{
			$params = explode('/', $param );
	
			foreach( $params as $p ){
		        switch( $p ){
		        	case "updatetime":
		        		$str = time();
		        		break;
		        	default:
			        	break;
		        }
	        	
			}
		}
		return $str;
	}
	
}