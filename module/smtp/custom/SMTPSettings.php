<?php
class SMTPSettings {
    public static $PROP_OPTIONS = array(
        'Timeout' => 20 //タイムアウト秒
    );
    /*
        $PROP_OPTIONSはphpMailerのオブジェクトのプロパティに対して設定したい値があれば
        配列で設定できる
        例：SMTPSecureをssl tls以外のものを強制的に使用する場合
        $PROP_OPTIONS = array(
            'Timeout' => 20,
            'SMTPSecure' => 'CRAM-MD5'
        );
    */
    public static $SMTP_OPTIONS = array(
    );
    /*
        $SMTP_OPTIONSはphpMailerのSMTPOptionsにオプションを与えることができます
        例：SSL証明関連
        $SMTP_OPTIONS = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
    */
}