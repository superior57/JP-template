<?php

	header( 'Content-Type: text/html; charset=UTF-8' );

	//初期値データのハッシュ版を作成するツール

	$FileRules = Array(
		Array( 'db/tdb/*/*.csv' , 'db/tdb_hash/$1/$2.csv' ) ,
		Array( 'module/*/db/tdb/*.csv' , 'module/$1/db/tdb_hash/$2.csv' )
	);

	//ハッシュ化を除外するcsv一覧
	$skipFiles = Array(
		'db/tdb/common/area.csv'    ,
		'db/tdb/common/adds.csv'    ,
		'db/tdb/common/add_sub.csv' ,
		'db/tdb/user/admin.csv'     ,
		'db/tdb/system/system.csv'  ,
		'db/tdb/module/zip.csv'
	);

	foreach( $FileRules as $fileRule ) //全ての設定を処理
	{
		$entries = glob( $fileRule[ 0 ] );
		$pattern = str_replace( '*' , '(.*)' , $fileRule[ 0 ] );

		foreach( $entries as $entry ) //全てのエントリを処理
		{
			print '<p>' . $entry . 'を処理中</p>';

			$hashEntry = preg_replace( '#' . $pattern . '#' , $fileRule[ 1 ] , $entry );

			MakePath( dirname( $hashEntry ) );

			if( in_array( $entry , $skipFiles ) ) //スキップ対象の場合
			{
				print '<p>スキップします</p>';
				copy( $entry , $hashEntry );

				continue;
			}

			$originFP  = fopen( $entry , 'rb' );
			$hashFP    = fopen( $hashEntry , 'wb' );
			$existsIDs = Array();

			while( !feof( $originFP ) ) //全ての行を処理
			{
				$data = fgetcsv( $originFP );

				if( !$data ) //行が空の場合
					{ continue; }

				if( 3 <= count( $data ) ) //列が3つ以上ある場合
					{ $data[ 2 ] = MakeUniqHashID( $data[ 2 ] , $existsIDs ); }

				fputcsv( $hashFP , $data );
			}

			fclose( $hashFP );
			fclose( $originFP );
		}
	}

	print '<p>完了しました</p>';

	function Makepath( $iPath ) //
	{
		$paths = explode( '/' , $iPath );
		$path  = '';

		foreach( $paths as $dir ) //全てのディレクトリを処理
		{
			$path .= $dir . '/';

			if( !is_dir( $path ) ) //ディレクトリが存在しない場合
				{ mkdir( $path ); }
		}
	}

	function MakeUniqHashID( $iID , &$ioExistsIDs ) //
	{
		preg_match( '/([^\d]+)(\d+)/' , $iID , $matches );

		$header = $matches[ 1 ];
		$length = strlen( $matches[ 2 ] );

		if( !$length ) //ハッシュ化処理ができない場合
			{ die( '<p>IDが連番ではありません。このテーブルをハッシュ化するべきか確認してください。</p>' ); }

		$md5     = md5( $iID );
		$hashID  = $header . substr( $md5 , 0 , $length );
		$counter = 0;

		while( in_array( $hashID , $ioExistsIDs ) ) //ハッシュIDが重複している場合
		{
			$oldHashID = $hashID;
			$md5       = md5( $md5 );
			$hashID    = $header . substr( $md5 , 0 , $length );
			$pointer   = 0;

			while( $oldHashID == $hashID ) //再生成してもIDが変化しない場合
			{
				$oldHashID = $hashID;
				$md5       = md5( $md5 );
				$hashID    = $header . substr( $md5 , ++$pointer , $length );

				if( 32 < $pointer ) //再試行の上限回数に達した場合
					{ die( '<p>ハッシュIDの重複を解消できませんでした。処理を中止します。</p>' ); }
			}

			if( 32 < ++$counter ) //再試行の上限回数に達した場合
				{ die( '<p>ハッシュIDの重複を解消できませんでした。処理を中止します。</p>' ); }
		}

		array_push( $ioExistsIDs , $hashID );

		return $hashID;
	}
