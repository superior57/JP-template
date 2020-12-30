<?php

	/*******************************************************************************************************
	 * <PRE>
	 * 
	 * htmlファイル読み込みクラス
	 *  このクラスはstaticなクラスです。インスタンスを生成せずに利用してください。
	 * 
	 * @author 丹羽一智
	 * @version 3.0.0
	 * 
	 * </PRE>
	 *******************************************************************************************************/

	class IncludeObject
	{
		/**
		 * 外部ファイルを読み込みます。
		 * コマンドコメントが存在する場合はGUIManagerオブジェクトとレコードデータを用いて処理をします。
		 * @param $file ファイル名
		 * @param $gm=null GUIManagerオブジェクト
		 * @param $rec=null レコードデータ
		 */
		function run($file, $gm = null, $rec = null)
		{
			if( !file_exists( $file ) )	{ throw new InternalErrorException('INCLUDEファイルが開けません。->'. $file); }
			
			$fp		 = fopen ($file, 'r');
			
		    $state = GUIManager::getDefState( true );
			while(!feof($fp))
			{
				$buffer	 = fgets($fp, 20480);
				$str	 = GUIManager::commandComment($buffer, $gm, $rec, $state , $c_part = null);
				
				$str	 = str_replace( Array("!CODE000;","!CODE001;"), Array("/"," "), $str );

				print DebugUtil::addFilePathComment( $str, $file );
			}
			fclose($fp);
			
		}
		
		/**
		 * 外部ファイルを読み込み、文字列データを返します。
		 * コマンドコメントが存在する場合はGUIManagerオブジェクトとレコードデータを用いて処理をします。
		 * @param $file ファイル名
		 * @param $gm=null GUIManagerオブジェクト
		 * @param $rec=null レコードデータ
		 */
		function get($file, $gm = null, $rec = null)
		{
			if( !file_exists( $file ) )	{ throw new InternalErrorException('INCLUDEファイルが開けません。->'. $file); }
			
			$fp		 = fopen ($file, 'r');
			$ret	 = "";
		    $state = GUIManager::getDefState( true );
			while(!feof($fp))
			{
				$buffer	 = fgets($fp, 20480);
				$ret	 .= GUIManager::commandComment($buffer, $gm, $rec, $state , $c_part = null);
			}
			fclose($fp);
			
			$ret = str_replace( Array("!CODE000;","!CODE001;"), Array("/"," "), $ret );

			return DebugUtil::addFilePathComment( $ret, $file );
		}
		
	}

	/********************************************************************************************************/
?>