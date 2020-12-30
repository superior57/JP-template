<?php
	include_once 'include/extends/SSLUtil.php';

	$CONFIG_SSL_ENABLE = false; //SSLへのリダイレクトを有効にする場合はtrue
	$CONFIG_SSL_MOBILE = false; //携帯電話でもSSLを仕様する場合はtrue
	$CONFIG_SSL_CHECK_CONTROLLER_NAME = true;
	$CONFIG_SSL_ALWAYS_HTTPS = true; //サイト全体をhttpsにする場合はtrue

	//$CONFIG_SSL_ALWAYS_HTTPSがtrueの場合、以下の設定内容は無視されます

	$CONFIG_SSL_ON_CHECK_USERS = Array( //SSLを常時有効にするユーザー
			'nobody' , 'nUser' , 'cUser', 'admin'
	);

    $CONFIG_SSL_ON_CHECK_CONTROLLER_NAME = Array( //SSLを常時有効にするコントロール
        'register', 'edit', 'login', 'reminder', 'other',
        'activate', 'index', 'info', 'page', 'search'
    );

    $CONFIG_SSL_OUT_CHECK_CONTROLLER_NAME = Array( //SSLを常時無効にするコントロール
    );

    $CONFIG_SSL_DISABLE_REDIRECT_CONTROLLER_NAME = Array( //リダイレクト処理を無効にするコントロール
        'cron'
    );
