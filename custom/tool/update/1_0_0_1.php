<?php


$UPDATE_NAME = '1.0.0.1';

$UPDATE_NAMES[ ] = $UPDATE_NAME;
$UPDATE_DESCRIPTION[ $UPDATE_NAME ] = 'アップデートのサンプル';

$UPDATE_CLASS[ $UPDATE_NAME ] = "xxxxx";	//更新で呼出したい任意のクラス名。  このファイル内に実装しても良いしLogic等に実装しても良い。。
$UPDATE_METHOD[ $UPDATE_NAME ] = "yyyyy";	//更新で呼出したい任意のメソッド名。  このファイル内に実装しても良い。

class xxxxx
{
	function yyyyy(){
		//update実行内容
	}
}