<?php

/*
 *  ./include/ 以下のクラスの設定を記述
 */

// include/extends/GDImager.php サムネイル生成時の背景色
	define( 'WS_SYSTEM_GDIMAGE_BACKGROUND_R', 255 );
	define( 'WS_SYSTEM_GDIMAGE_BACKGROUND_G', 255 );
	define( 'WS_SYSTEM_GDIMAGE_BACKGROUND_B', 255 );

// サムネイルの生成のタイミング( true:ブラウザ側でimgタグを読み込み時 false:内部で生成してからhtmlを出力 )
	define( 'WS_SYSTEM_GDIMAGE_PROGRESS_IMAGE', true );

// 一度に表示できる検索結果件数の上限(0で無制限)
	define( 'WS_SYSTEM_SEARCH_RESULT_NUM_MAX', 100 );

// InfoCheckの処理で「アクセスしようとしたユーザーがそのレコードを検索可能であること」を自動的に確認する場合はtrue
	define( 'WS_SYSTEM_AUTO_INFO_CHECK_SEARCHABLE', true );

// アップロードを許可する拡張子
	$UPLOAD_FILE_EXT = Array( 'jpg' , 'jpeg' , 'gif' , 'png' );

	//テンプレートキャッシュを使用する場合はtrue
	$USE_TEMPLATE_CACHE = false;

	//データ登録処理中に排他ロックを使用する場合はtrue
	$USE_REGISTER_PROCCESS_LOCK = false;
