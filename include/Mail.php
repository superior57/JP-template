<?php

	/***************************************************************************************************<pre>
	 * 
	 * メール送信クラス
	 *  staticなクラスなので、インスタンスを生成せずに利用してください。
	 * 
	 * @author 丹羽一智
	 * @version 3.0.0<br/>
	 * 
	 * </pre>
	 ********************************************************************************************************/

	class Mail
	{
		private static $_DEBUG	 = DEBUG_FLAG_MAIL;
		/**
		 * メールの送信。
		 * 外部データとなるメールファイルの内容でメールを送信する。
		 * コマンドコメントは$gm に渡ってきたGUIManagerオブジェクトと
		 * $rec に渡ってきたレコードデータで処理をします。
		 * また、メールデータの sub パートは題名として、 main パートは本文として処理されます。
		 *
		 * @param $mail メールファイル
		 * @param $from 送信元メールアドレス
		 * @param $to 送信先メールアドレス
		 * @param $gm=null GUIManagerオブジェクト
		 * @param $rec=null レコードデータ
		 * @param $from_name 送信元名 *省略可能
		 */
		static function send($mail, $from, $to, $gm = null, $rec = null, $from_name = null, $ccs = null, $bccs = null )
		{
			//smtpモジュールがあった場合SMTP経由の送信フラグがTRUEであればSMTP送信
			if(SystemUtil::existsModule('smtp') && Conf::getData('smtp', 'smtp_flg')) {
				SMTPLogic::send($mail, $from, $to, $gm, $rec, $from_name, $ccs, $bccs);
				return;
			}

			global $MAILSEND_ADDRES;
			global $ALL_DEBUG_FLAG;
			global $MAIL_BLOCK;
			global $SYSTEM_CHARACODE;

			$from = str_replace( Array( "\r" , "\n" ) , '' , $from );

			if(is_null($from_name)){
				$from_str = 'From:'. trim($from);
			}else{
				$from_name = str_replace( Array( "\r" , "\n" ) , '' , $from_name );
				$from_str  = 'From:"'.mb_encode_mimeheader($from_name, $SYSTEM_CHARACODE , "B", "\n").'" <'. trim($from).'>';
			}
			
			if(  isset( $gm )  ){
				$sub	= $gm->getString($mail, $rec, 'subject');
				$main	= $gm->getString($mail, $rec, 'main');
			}else{
				$sub	= GUIManager::partGetString($mail, 'subject');
				$main	= GUIManager::partGetString($mail, 'main');
			}
			$sub	 = str_replace(  Array("\n","\r"), Array("",""), $sub );
			$main	 = str_replace(  "<br/>", "\n", $main );
			
			if( !is_null($ccs) && is_array($ccs) ){
				foreach( $ccs as $cc ){
					if(!isset($cc['name'])){
						$from_str .= "\n".'Cc:'. trim($cc['mail']);
					}else{
						$from_str .= "\n".'Cc:"'.mb_encode_mimeheader($cc['name'], $SYSTEM_CHARACODE , "B", "\n").'" <'. trim($cc['mail']).'>';
					}
				}
			}
			
			if( !is_null($bccs) && is_array($bccs) ){
				foreach( $bccs as $bcc ){
					if(!isset($bcc['name'])){
						$from_str .= "\n".'Bcc:'. trim($bcc['mail']);
					}else{
						$from_str .= "\n".'Bcc:"'.mb_encode_mimeheader($bcc['name'], $SYSTEM_CHARACODE , "B", "\n").'" <'. trim($bcc['mail']).'>';
					}
				}
			}
			
			if($to == $MAILSEND_ADDRES){
				$main .= '-----------------------------------'."\n";
				$main .= 'REMOTE_HOST：'.$_SERVER["REMOTE_ADDR"]."\n";
				$main .= 'HTTP_USER_AGENT：'.$_SERVER["HTTP_USER_AGENT"]."\n";
				$main .= '-----------------------------------'."\n";
			}
			
			mb_language("uni");
			$sub = str_replace("\n", "", $sub);
			$sub = str_replace("\r", "", $sub);

			$main = str_replace("\r", "", $main);
			
			//半角カナを全角カナへ
			$sub = SystemUtil::hankakukana2zenkakukana($sub);
			$main = SystemUtil::hankakukana2zenkakukana($main);
			
		
			if(self::$_DEBUG){
				d($to,'to');
				d($sub,'sub');
				d($main,'main');
				d($from_str,'from_str');
				d($from,'from');
			}
			
			//デバッグモード時にメール送信ブロックが指定されていた場合、実際には送信しない。
			if( $ALL_DEBUG_FLAG && $MAIL_BLOCK ){ return; }
			
            //送信
			$rcd = mb_send_mail( $to, $sub, $main, $from_str , '-f ' . trim($from));
		}



		/**
		 * 添付ファイル付きメールの送信。
		 * 外部データとなるメールファイルの内容でメールを送信する。
		 * コマンドコメントは$gm に渡ってきたGUIManagerオブジェクトと
		 * $rec に渡ってきたレコードデータで処理をします。
		 * また、メールデータの sub パートは題名として、 main パートは本文として処理されます。
		 *
		 * @param $mail メールファイル
		 * @param $from 送信元メールアドレス
		 * @param $to 送信先メールアドレス
		 * @param $gm=null GUIManagerオブジェクト
		 * @param $rec=null レコードデータ
		 * @param $from_name 送信元名 *省略可能
		 */
		static function sendAttach($mail, $from, $to, $gm = null, $rec = null, $attach = null, $from_name = null )
		{
			//smtpモジュールがあった場合SMTP経由の送信フラグがTRUEであればSMTP送信
			if (SystemUtil::existsModule('smtp') && Conf::getData('smtp', 'smtp_flg')) {
				SMTPLogic::sendAttach($mail, $from, $to, $gm, $rec, $attach, $from_name);
				return;
			}

			global $MAILSEND_ADDRES;
			global $HOME;
			global $SYSTEM_CHARACODE;
			
			if( $attach != '' && file_exists( $attach ) )
			{// 添付ファイルがある場合
				if(  isset( $gm ) && isset( $rec )  )
				{
					$sub	 = str_replace(  "\n", "", $gm->getString($mail, $rec, 'subject') );
					$main	 = str_replace(  "<br/>", "\n", $gm->getString($mail, $rec, 'main') );
				}
				else
				{
					$sub	 = stripslashes( str_replace(  "\n", "", GUIManager::partGetString($mail, 'subject') ) );
					$main	 = str_replace(  "<br/>", "\n", GUIManager::partGetString($mail, 'main') );
				}
				
				if($to == $MAILSEND_ADDRES)
				{
					$main .= '-----------------------------------'."\n";
					$main .= 'REMOTE_HOST：'.$_SERVER["REMOTE_ADDR"]."\n";
					$main .= 'HTTP_USER_AGENT：'.$_SERVER["HTTP_USER_AGENT"]."\n";
					$main .= '-----------------------------------'."\n";
				}
	
				mb_language("uni");
				$sub = str_replace("\n", "", $sub);
				$sub = str_replace("\r", "", $sub);
				
				$main = str_replace("\r", "", $main);

				$sub = SystemUtil::hankakukana2zenkakukana($sub);
				$main = SystemUtil::hankakukana2zenkakukana($main);
				
				//画像の取得と画像のエンコード
				if(  strpos( $attach, ".pdf" ) || strpos( $attach, ".PDF" ) )
				{
					$type		 = 'application/pdf';
					$img_name	 = "data.pdf";
				}
				else
				{
					$img_name			 = "image";
					list($width, $height,$type) = getimagesize($attach);
					switch( $type )
					{
						case '1':
							$type		 = 'image/gif';
							$img_name	.= '.gif';
							break;
						case '2':
							$type		 = 'image/jpeg';
							$img_name	.= '.jpeg';
							break;
						case '3':
							$type		 = 'image/png';
							$img_name	.= '.png';
							break;
					}	
				}
				
				$img				 = file_get_contents($HOME.$attach);
				$img_encode64_000	 = chunk_split(base64_encode($img));
				
				//ヘッダ情報
				$from = str_replace( Array( "\r" , "\n" ) , '' , $from );

				if(is_null($from_name))
					{ $headers  = "From:" . trim($from). "\r\n"; }
				else
				{
					$from_name = str_replace( Array( "\r" , "\n" ) , '' , $from_name );
					$headers   = 'From:"'.mb_encode_mimeheader($from_name, $SYSTEM_CHARACODE , "B", "\n").'" <'. trim($from).'>'. "\r\n";
				}

				$headers .= 'MIME-Version: 1.0' . "\r\n";
				$headers .= 'Content-Type: multipart/related;boundary="1000000000"' . "\r\n";
			
//テキストパート
$message =<<<END

--1000000000
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 7bit

$main

--1000000000
Content-Type: $type; name="$img_name"
Content-Disposition: attachment; filename="$img_name"
Content-Transfer-Encoding: base64
Content-Disposition: inline;  filename="$img_name"

$img_encode64_000

--1000000000--

END;
			
				$sub	 = mb_encode_mimeheader($sub, $SYSTEM_CHARACODE , "B", "\n");// テキストだといける
				$message = mb_convert_encoding($message, "UTF-8");
				$rcd 	 = mail( $to, $sub, $message, $headers );
			
			}
			else {
				self::send( $mail, $from, $to, $gm, $rec, $from_name );
			} // 添付ファイルが無い場合

		}

		/**
		 * メールの送信。
		 * 文字列を直接指定してメールを送信します。
		 *
		 * @param $sub タイトル文字列
		 * @param $main 本文文字列
		 * @param $from 送信元メールアドレス
		 * @param $to 送信先メールアドレス
		 * @param $from_name 送信元名 *省略可能
		 */
		static function sendString($sub, $main, $from, $to , $from_name = null)
		{
			//smtpモジュールがあった場合SMTP経由の送信フラグがTRUEであればSMTP送信
			if(SystemUtil::existsModule('smtp') && Conf::getData('smtp', 'smtp_flg')) {
				return SMTPLogic::sendString($sub, $main, $to, $from, $from_name);
			}

			global $ALL_DEBUG_FLAG;
			global $MAIL_BLOCK;
			global $SYSTEM_CHARACODE;
			
			$from = str_replace( Array( "\r" , "\n" ) , '' , $from );

			if(is_null($from_name)){
				$from_str = 'From:'. trim($from);
			}else{
				$from_name = str_replace( Array( "\r" , "\n" ) , '' , $from_name );
				$from_str  = 'From:"'.mb_encode_mimeheader($from_name, $SYSTEM_CHARACODE , "B", "\n").'" <'. trim($from).'>';
			}
			
			
			$sub	 = str_replace(  Array("\n","\r"), Array("",""), $sub );
			$main	 = str_replace(  "<br/>", "\n", $main );

			mb_language("uni");
			$main = str_replace("\r", "", $main);

			$sub = SystemUtil::hankakukana2zenkakukana($sub);
			$main = SystemUtil::hankakukana2zenkakukana($main);
			

			if(self::$_DEBUG){
				d($to,'to');
				d($sub,'sub');
				d($main,'main');
				d($from_str,'from_str');
				d($from,'from');
			}
			
			//デバッグモード時にメール送信ブロックが指定されていた場合、実際には送信しない。
			if( $ALL_DEBUG_FLAG && $MAIL_BLOCK ){ return; }
			
			//送信
			$rcd = mb_send_mail(  $to, $sub, $main, $from_str ,'-f ' . trim($from));
			if(self::$_DEBUG){
				d($rcd,'rcd');
			}
			return $rcd;
		}
		static function onDebug(){ self::$_DEBUG = true; }
		static function offDebug(){ self::$_DEBUG = false; }
	}

	/********************************************************************************************************/
?>