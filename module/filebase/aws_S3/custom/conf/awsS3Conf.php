<?php
	// 設定が正常にできているかの確認は、管理者ログイン後の画面下部に
	//「アップロードファイル設定確認」がありますので、そこで確認ができます。
	$AWS_S3_USEDFLAG = false ; // awsS3に保存する場合はtrueにする
	$AWS_S3_ACCESS_KEY = "";  // awsS3にアクセスするためのアクセスキー
	$AWS_S3_SEACRET_KEY = ""; // awsS3にアクセスするためのシークレットキー
	$AWS_S3_BUCKET_NAME = "base"; // S3に作成されているバケット名
	$AWS_S3_PARTITION = "base"; // ファイルを保存するバケット内のフォルダ

	//テストを行われる際に設定下さい。通常は変更する必要はありません
	//trueにするとPARTITIONに設定されているディレクトリの一階層下にディレクトリが生成されその中にファイルが収められる。
	//本番用データを上書きしないようにするための設定。
	$AWS_S3_DEBUG = false ;
	$AWS_S3_DEBUG_DIR = 'debug' ;