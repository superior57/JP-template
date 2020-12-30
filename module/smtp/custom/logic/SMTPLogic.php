<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * include/Mail.phpと同じメソッドをPHPMailer用に定義したもの
 * include/Mail.php内で分岐する
 */
class SMTPLogic {
    private static $_DEBUG = DEBUG_FLAG_MAIL;

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
     * @param $css
     * @param $bcss
     * @param $attach 添付ファイルパス
     */
    public static function send($mail, $from, $to, $gm = null, $rec = null, $from_name = null, $ccs = null, $bccs = null, $attach = null )
    {
        global $MAILSEND_ADDRES;
        global $ALL_DEBUG_FLAG;
        global $MAIL_BLOCK;
        global $SYSTEM_CHARACODE;

        $sub = '';
        $main = '';
        $mainHTML = '';

        if(  isset( $gm )  ){
            $sub	= $gm->getString($mail, $rec, 'subject');
            $main	= $gm->getString($mail, $rec, 'main');
            $mainHTML = $gm->getString($mail, $rec, 'main_html');
        }else{
            $sub	= GUIManager::partGetString($mail, 'subject');
            $main	= GUIManager::partGetString($mail, 'main');
            $mainHTML = GUIManager::partGetString($mail, $rec, 'main_html');
        }

        $sub	 = self::removeCRLF($sub);
        $main	 = str_replace(  "<br/>", "\n", $main );

        if($to == $MAILSEND_ADDRES){
            $main .= '-----------------------------------'."\n";
            $main .= 'REMOTE_HOST：'.$_SERVER["REMOTE_ADDR"]."\n";
            $main .= 'HTTP_USER_AGENT：'.$_SERVER["HTTP_USER_AGENT"]."\n";
            $main .= '-----------------------------------'."\n";
        }
        
        //半角カナを全角カナへ
        $sub = SystemUtil::hankakukana2zenkakukana($sub);
        $main = SystemUtil::hankakukana2zenkakukana($main);

        $mailer = self::createPHPMailer();

        if (!is_null($ccs) && is_array($ccs)) {
            foreach ($ccs as $cc) {
                if (!isset($cc['name'])) {
                    $mailer->addCC($cc['mail']);
                } else {
                    $mailer->addCC($cc['mail'], $cc['name']);
                }
            }
        }

        if (!is_null($bccs) && is_array($bccs)) {
            foreach ($bccs as $bcc) {
                if (!isset($bcc['name'])) {
                    if (!isset($cc['name'])) {
                        $mailer->addBCC($bcc['mail']);
                    } else {
                        $mailer->addBCC($bcc['mail'], $bcc['name']);
                    }
                }
            }
        }

        self::setBody($mailer, $main, $mainHTML);

		if(strlen($from) == 0){
	        $from = $MAILSEND_ADDRES;
		}

        if(!is_null($from_name)) {
            $from_name = self::removeCRLF($from_name );
            $mailer->FromName = mb_encode_mimeheader($from_name);
        } else {
            $mailer->FromName = $from;
        }
        $mailer->From = self::removeCRLF($from);
        $mailer->Subject = mb_encode_mimeheader($sub);
        $mailer->addAddress(self::removeCRLF($to)); // 送信先

        if( !is_null($attach) && strlen($attach) && file_exists($attach) ) {
            $mailer->AddAttachment($attach);
        }

        if (self::$_DEBUG) {
            d($to, 'to');
            d($sub, 'sub');
            d($main, 'main');
            d($from, 'from');
        }

        if ($ALL_DEBUG_FLAG && $MAIL_BLOCK) {
            return;
        }

        if(!$mailer->send() && self::$_DEBUG) {
            d($mailer->ErrorInfo, 'SMTP_ERROR');
        }
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
     * @param $attach 添付ファイルパス
     * @param $from_name 送信元名 *省略可能
     */
    static function sendAttach($mail, $from, $to, $gm = null, $rec = null, $attach = null, $from_name = null )
    {
        self::send( $mail, $from, $to, $gm, $rec, $from_name, null, null, $attach );
    }

    /**
     * メールの送信。
     * 文字列を直接指定してメールを送信します。
     *
     * @param $sub タイトル文字列
     * @param $main 本文文字列
     * @param $to 送信先メールアドレス
     * @param $from 送信元メールアドレス
     * @param $from_name 送信元名 *省略可能
     */
    static function sendString($sub, $main, $to, $from = NULL, $from_name = null)
    {
        global $MAILSEND_ADDRES;
        global $ALL_DEBUG_FLAG;
        global $MAIL_BLOCK;
        
        $sub	 = self::removeCRLF($sub);
        $main	 = str_replace(  "<br/>", "\n", $main );

        $sub = SystemUtil::hankakukana2zenkakukana($sub);
        $main = SystemUtil::hankakukana2zenkakukana($main);

        $mailer = self::createPHPMailer();

        $mailer->Body = $main;
        $mailer->isHTML(false);

		if(strlen($from) == 0){
	        $from = $MAILSEND_ADDRES;
		}

        if (!is_null($from_name)) {
            $from_name = self::removeCRLF($from_name);
            $mailer->FromName = mb_encode_mimeheader($from_name);
        } else {
            $mailer->FromName = $from;
        }
        $mailer->From = $from;
        $mailer->Subject = mb_encode_mimeheader($sub);
        $mailer->addAddress($to); // 送信先

        if (self::$_DEBUG) {
            d($to, 'to');
            d($sub, 'sub');
            d($main, 'main');
            d($from, 'from');
        }

			//デバッグモード時にメール送信ブロックが指定されていた場合、実際には送信しない。
        if ($ALL_DEBUG_FLAG && $MAIL_BLOCK) {
            return;
        }
			
        //送信
        $rcd = $mailer->send();
        if(!$rcd && self::$_DEBUG) {
            d($mailer->ErrorInfo, 'SMTP_ERROR');
        }
        return $rcd;
    }
        
    /**
     * smtpに必要な設定をしたPHPMailerオブジェクトを返す
     * @return PHPMailer
     */
    private static function createPHPMailer() {
        global $SYSTEM_CHARACODE;
        
        $mailer = new PHPMailer();
        //メール送信SMTP情報
        $mailer->isSMTP();
        $mailer->Host = Conf::getData('smtp', 'host');
        $mailer->Username = Conf::getData('smtp', 'username');
        $mailer->Password = self::decodePassword(Conf::getData('smtp', 'password'));

        self::setSecure($mailer, Conf::getData('smtp', 'secure'));
        $mailer->Port = Conf::getData('smtp', 'port');

		if(strlen($mailer->Username)>0 && strlen($mailer->Password)>0){
			$mailer->SMTPAuth = TRUE;
			if(empty($mailer->AuthType)){
				// PLAINTEXT認証
				$mailer->AuthType = 'PLAIN';
			}
		}else{
			// 認証を行わない
			$mailer->SMTPAuth = FALSE;
		}

        //エンコード情報
        $mailer->CharSet = $SYSTEM_CHARACODE;
        $mailer->Encoding = "base64";

        //その他設定
        self::setOptions($mailer);
        return $mailer;
    }

    public static function sendTest($mail, $gm, $host, $username, $password, $secure, $port, $testmail = '') {
        global $MAILSEND_ADDRES;
        global $ALL_DEBUG_FLAG;
        global $MAIL_BLOCK;
        global $SYSTEM_CHARACODE;
        
        $sub = '';
        $main = '';
        $mainHTML = '';

        $sub	= $gm->getString($mail, null, 'subject');
        $main	= $gm->getString($mail, null, 'main');
        $mainHTML = $gm->getString($mail, null, 'main_html');

        $sub	 = self::removeCRLF($sub);
        $main	 = str_replace(  "<br/>", "\n", $main );

        $to = strlen($testmail) ? $testmail : $MAILSEND_ADDRES;

		if($to == $MAILSEND_ADDRES){
            $main .= '-----------------------------------'."\n";
            $main .= 'REMOTE_HOST：'.$_SERVER["REMOTE_ADDR"]."\n";
            $main .= 'HTTP_USER_AGENT：'.$_SERVER["HTTP_USER_AGENT"]."\n";
            $main .= '-----------------------------------'."\n";
        }
        
        //半角カナを全角カナへ
        $sub = SystemUtil::hankakukana2zenkakukana($sub);
        $main = SystemUtil::hankakukana2zenkakukana($main);

        $mailer = new PHPMailer();
        //メール送信SMTP情報
        $mailer->isSMTP();
        $mailer->Host = $host;
        $mailer->Username = $username;
        $mailer->Password = $password;
		if(strlen($username)>0 && strlen($password)>0){
			$mailer->SMTPAuth = TRUE;
			if(empty($secure)){
				$mailer->AuthType = 'PLAIN';
			}
		}else{
			$mailer->SMTPAuth = FALSE;
		}
		$mailer->setLanguage('ja', 'module/smtp/vendor/phpmailer/phpmailer/language/');
		$mailer->Debugoutput = 'echo';

		$mailer->SMTPDebug = 2;
		ob_start();

        self::setSecure($mailer, $secure);
        $mailer->Port = $port;

        //エンコード情報
        $mailer->CharSet = $SYSTEM_CHARACODE;
        $mailer->Encoding = "base64";

        self::setOptions($mailer);

        self::setBody($mailer, $main, $mainHTML);

        $from = $MAILSEND_ADDRES;

        if (!is_null($from_name)) {
            $from_name = self::removeCRLF($from_name);
            $mailer->FromName = mb_encode_mimeheader($from_name);
        } else {
            $mailer->FromName = $from;
        }

        $mailer->From = self::removeCRLF($from);
        $mailer->Subject = mb_encode_mimeheader($sub);
        $to = strlen($testmail) ? $testmail : $username;
        $mailer->addAddress(self::removeCRLF($to));

        if (self::$_DEBUG) {
            d($to, 'to');
            d($sub, 'sub');
            d($main, 'main');
            d($from, 'from');
        }

        if ($ALL_DEBUG_FLAG && $MAIL_BLOCK) {
            return;
        }
        if (!$mailer->send()) {
            if(self::$_DEBUG) {
                d($mailer->ErrorInfo, 'SMTP_ERROR');
            }
//            throw new Exception('SMTP_ERROR:' . $mailer->ErrorInfo);
			$mailer->smtpClose();
			ob_end_clean();
			return array('status'=>'NG','message'=>$mailer->ErrorInfo);
        }
		return array('status'=>'OK','message'=>ob_get_clean());
    }

    /**
     * 全ての改行削除
     * @return string 改行を削除した文字
     */
    private static function removeCRLF($str) {
        return str_replace(array("\n", "\r", "\r\n"), '', $str);
    }
    /**
     * メール本文を付加
     * @param PHPMailer $mailer
     * @param string $main テストメール本文
     * @param string $mainHTML HTMLメール本文
     */
    private static function setBody(&$mailer, $main, $mainHTML = '') {
        //メール情報
        if (strlen($mainHTML)) {
            $mailer->Body = $mainHTML;
            $mailer->AltBody = $main;
            $mailer->isHTML(true);
        } else {
            $mailer->Body = $main;
            $mailer->isHTML(false);
        }
    }
    /**
     * セキュア設定
     * @param PHPMailer $mailer
     * @param string $secure セキュア ssl or tls or 空
     */
    private static function setSecure(&$mailer, $secure = '') {
        if (strlen($secure)) {
            $mailer->SMTPSecure = $secure;
        } else {
            $mailer->SMTPSecure = false;
            $mailer->SMTPAutoTLS = false;
        }
    }
    /**
     * PHPMailerオプション設定 SMTPSettingsに設定された値を使用する
     * @param PHPMailer $mailer
     */
    private static function setOptions(&$mailer) {
        //その他設定
        foreach (SMTPSettings::$PROP_OPTIONS as $prop => $val) {
            if (property_exists($mailer, $prop)) {
                $mailer->{$prop} = $val;
            }
        }
        //SMTPオプション設定
        if (count(SMTPSettings::$SMTP_OPTIONS)) {
            $mailer->SMTPOptions = SMTPSettings::$SMTP_OPTIONS;
        }
    }

    public static function decodePassword($password) {
        $pass = base64_decode($password);
        return str_replace(self::$PASS_HEAD, '', $pass);
    }
    public static function encodePassword($password) {
        $pass = self::$PASS_HEAD.$password;
        return base64_encode($pass);
    }

    private static $PASS_HEAD = 'WSJeOF1sDd8e'; //これをパスワードの先頭に付けて複合できないようにする。
}