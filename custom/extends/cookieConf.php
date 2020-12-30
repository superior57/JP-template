<?php

/*************************
 **     クッキー関係の設定     **
 *************************/

// サブドメインも含めて共通のクッキーを利用したい場合、最上位のドメインを指定する。
// 例： example.com や sub1.example.com で共通のクッキーを指定したい場合に $cookie_domain = "example.com"; とする。

/***************** ユーザー設定 ******************/

$cookie_domain = "";

/***************** システム側の動作 ******************/
ini_set( 'session.cookie_domain', $cookie_domain );
unset( $cookie_domain );