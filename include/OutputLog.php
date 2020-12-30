<?php

	include_once "./custom/extends/logConf.php";

	/***************************************************************************************************<pre>
	 * 
	 * ログファイル書き出しストリーム
	 * 
	 * @author 丹羽一智
	 * @version 3.0.0<br/>
	 * 
	 * </pre>
	 ********************************************************************************************************/

	class OutputLog
	{
		var $file;
		var $MAX_LOGFILE_SIZE = 20971520; //20MB | 1024 * 1024 * 20
//		var $MAX_LOGFILE_SIZE = 5242880;  //5MB | 1024 * 1024 * 5
//		var $MAX_LOGFILE_SIZE = 20480;  //20KB | 1024 * 50
		
		/**
		 * コンストラクタ。
		 * @param $file ログを書き出すファイルへのパス
		 */
		function __construct($file)
		{
			if( !file_exists( $file ) )	{ throw new InternalErrorException('LOGファイルが開けません。->'. $file); }
			$this->file = $file;
		}
		
		/**
		 * ログの書き出し。
		 * @param $str 書き出す文字列
		 */
		function write($str)
		{
			$existsLogFile = file_exists( $this->file );

			$fp = fopen($this->file, 'a');
			
			// ファイルがロックされているかの確認
			if(flock($fp, LOCK_EX))
			{
				fwrite($fp, $str. $_SERVER['HTTP_USER_AGENT']. ",". $_SERVER['REMOTE_ADDR']. ",". date("Y_m_d_H_i_s"). "\n");
				flock($fp, LOCK_UN);
			}
			
			fclose($fp);

			if( !$existsLogFile )
				{ chmod( $this->file, 0766 ); }

			//print filesize($this->file)."/".$this->MAX_LOGFILE_SIZE;
			//ファイルサイズを確認し最大値を超えている場合、リネームする。
			if($this->MAX_LOGFILE_SIZE < filesize($this->file)){
				$new_file = $this->file.date("_Y_m_d_H_i_s");
				if(rename($this->file, $new_file)){
					if(touch($this->file)){
						if(!@chmod($this->file, 0777)){
							//パーミッション変更失敗
							unlink($this->file);
							rename($new_file, $this->file);
						}else{
							@chmod( $new_file, 0766 );
						}
					}else{
						//新規ログファイル生成失敗
						rename($new_file, $this->file);
					}
				}
			}
		}
	}

	/********************************************************************************************************/
?>