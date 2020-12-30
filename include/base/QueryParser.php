<?php

	//★クラス //

	/**
		@brief   POSTクエリの手動解析用クラス
		@details 大容量ファイルのアップロードに対応するための処理をまとめたクラスです。
		@remarks このクラスを使用する場合は php.ini の enable_post_data_reading を無効にするか、または post_max_size と upload_max_filesize の値を一致させてください。\n
		         (post_max_size を超えずに upload_max_filesize だけを超えた場合、POSTクエリの手動読み込みができなくなります)
	*/
	class QueryParser //
	{
		/**
			@brief   入力を読み込んで $_POST 、 $REEUQST 及び $_FILES に代入する
			@details ファイルの送信を含む場合は self::$UploadPath の指定に従って一時ファイルを出力します。
			@remarks $_FILES の処理を move_uploaded_file で行っている場合はファイルの移動が失敗する可能性があります。\n
			         このクラスの機構でアップロードされた場合は識別のために $_FILES の各要素に [ 'is_big' ] = true を追加しますので、この値がある場合は rename で処理してください。
		*/
		static function Initialize() //
		{
			if( self::$Initialized ) //初期化済みの場合
				{ return; }

			if( count( $_POST ) ) //POSTが空ではない場合
				{ return; }

			self::$Stream = fopen( 'php://input' , 'rb' );
			$firstLine    = fgets( self::$Stream , self::$ReadSize );

			if( preg_match( '/^-+\w+$/' , rtrim( $firstLine ) ) ) //境界文字列の書式に一致する場合
			{
				self::$Boundary   = rtrim( $firstLine , "\r\n" );
				self::$EndOfQuery = self::$Boundary . '--';
				$buffer           = Array();
				$uploadInfo       = Array();

				while( !feof( self::$Stream ) ) //全ての入力を処理
				{
					$header = self::GetNextHeader();

					if( !$header ) //ヘッダが取得できない場合
						{ break; }

					if( isset( $header[ 'filename' ] ) ) //ファイルの場合
					{
						if( !$header[ 'filename' ] ) //ファイル名が空の場合
							{ self::SkipNextBody(); }
						else //ファイルが送信されている場合
						{
							$mimeType       = preg_replace( '/^Content-Type: (.*)$/' , '$1' , rtrim( fgets( self::$Stream ) ) );
							$originNameInfo = pathInfo( $header[ 'filename' ] );
							$extension      = ( isset( $originNameInfo[ 'extension' ] ) ? $originNameInfo[ 'extension' ] : 'dat' );
							$savePath       = self::$UploadPath . time() . rand() . '.' . $extension;

							self::SaveNextBodyTo( $savePath );

							if( is_file( $savePath ) ) //ファイルが生成された場合
							{
								if( preg_match( '/(.*?)((\[\])*)\[\]$/' , $header[ 'name' ] , $matches ) ) //引数名が配列形式の場合
								{
									$uploadInfo[ $matches[ 1 ] . '[name]' . $matches[ 2 ] ][]     = $header[ 'filename' ];
									$uploadInfo[ $matches[ 1 ] . '[type]' . $matches[ 2 ] ][]     = $mimeType;
									$uploadInfo[ $matches[ 1 ] . '[size]' . $matches[ 2 ] ][]     = filesize( $savePath );
									$uploadInfo[ $matches[ 1 ] . '[tmp_name]' . $matches[ 2 ] ][] = $savePath;
									$uploadInfo[ $matches[ 1 ] . '[error]' . $matches[ 2 ] ][]    = 0;
									$uploadInfo[ $matches[ 1 ] . '[is_big]' . $matches[ 2 ] ][]   = true;
								}
								else //引数名がスカラ形式の場合
								{
									$uploadInfo[ $header[ 'name' ] . '[name]' ]     = $header[ 'filename' ];
									$uploadInfo[ $header[ 'name' ] . '[type]' ]     = $mimeType;
									$uploadInfo[ $header[ 'name' ] . '[size]' ]     = filesize( $savePath );
									$uploadInfo[ $header[ 'name' ] . '[tmp_name]' ] = $savePath;
									$uploadInfo[ $header[ 'name' ] . '[error]' ]    = 0;
									$uploadInfo[ $header[ 'name' ] . '[is_big]' ]   = true;
								}
							}
						}
					}
					else //それ以外の場合
						{ $buffer[] = $header[ 'name' ] . '=' . urlencode( self::GetNextBody() ); }
				}

				parse_str( implode( '&' , $buffer )     , $_POST );
				parse_str( http_build_query( $uploadInfo ) , $_FILES );
			}
			else //境界文字列を含まない場合
			{
				$buffer = $firstLine;

				while( !feof( self::$Stream ) ) //全ての入力を処理
					{ $buffer .= $firstLine; }

				parse_str( $buffer , $_POST );
			}

			$_REQUEST = array_merge( $_REQUEST , $_POST );

			self::$Initialized = true;
		}

		/**
			@brief     入力を読み込んで指定のファイルに保存する
			@param[in] $iSavePath 出力先ファイル名。
			@remarks   動作確認・デバッグ用のメソッドです。
		*/
		static function SaveQuery( $iSavePath ) //
		{
			self::$Stream = fopen( 'php://input' , 'rb' );
			$saveFP       = fopen( $iSavePath , 'wb' );

			while( !feof( self::$Stream ) ) //全てのクエリを処理
				{ fputs( $saveFP , fgets( self::$Stream , self::$ReadSize ) ); }
		}

		/**
			@brief  QueryPerserが使用可能な設定になっているか確認する。
			@return 使用可能な場合はtrue それ以外はfalse
		*/
		static function CheckPHPConfig()
		{
			if( !ini_get( 'enable_post_data_reading' ) ) //POSTの自動解析が無効になっている場合
				{ return true; }

			if( ini_get( 'post_max_filesize' ) == ini_get( 'upload_max_filesize' ) ) //POST最大サイズとアップロード最大サイズが同じになっている場合
				{ return true; }

			return false;
		}

		/**
			@brief ヘッダを取得する。
		*/
		private static function GetNextHeader() //
		{
			$headerSource  = rtrim( fgets( self::$Stream ) , "\r\n" );

			if( !$headerSource ) //これ以上入力から読み込めない場合
				{ return false; }

			$headerSource = explode( '; ' , $headerSource );
			$headerResult = Array();

			foreach( $headerSource as $parameter ) //全てのパラメータを処理
			{
				if( preg_match( '/^(\w+)="(.*)"$/' , $parameter , $matches ) ) //パラメータをパースできる場合
					{ $headerResult[ $matches[ 1 ] ] = $matches[ 2 ]; }
			}

			return $headerResult;
		}

		/**
			@brief 送信内容を取得する。
		*/
		private static function GetNextBody() //
		{
			$result = '';
			$return = fgets( self::$Stream );

			while( !feof( self::$Stream ) ) //全ての入力を処理
			{
				$readLine = fgets( self::$Stream , self::$ReadSize );
				$trimLine = rtrim( $readLine , "\r\n" );

				if( self::$Boundary == $trimLine || self::$EndOfQuery == $trimLine ) //境界に着いた場合
					{ break; }

				$result .= $readLine;
			}

			return rtrim( $result , "\r\n" );
		}

		/**
			@brief     送信内容をファイルに出力する。
			@param[in] $iSavePath 出力先ファイル名。
		*/
		private static function SaveNextBodyTo( $iSavePath ) //
		{
			$result   = '';
			$return   = fgets( self::$Stream );
			$saveLine = '';
			$lookLine = '';
			$saveFP   = fopen( $iSavePath , 'wb' );

			while( !feof( self::$Stream ) ) //全ての入力を処理
			{
				$saveLine  = $lookLine;
				$lookLine  = fgets( self::$Stream , self::$ReadSize );
				$trimLine  = rtrim( $lookLine , "\r\n" );

				if( self::$Boundary == $trimLine || self::$EndOfQuery == $trimLine ) //境界に着いた場合
				{
					fputs( $saveFP , rtrim( $saveLine , "\r\n" ) );

					break;
				}

				fputs( $saveFP , $saveLine );
			}
		}

		/**
			@brief 次の境界まで送信内容を読み飛ばす。
		*/
		private static function SkipNextBody() //
		{
			while( !feof( self::$Stream ) ) //全ての入力を処理
			{
				$readLine = fgets( self::$Stream , self::$ReadSize );
				$trimLine = rtrim( $readLine , "\r\n" );

				if( self::$Boundary == $trimLine || self::$EndOfQuery == $trimLine ) //境界に着いた場合
					{ break; }
			}
		}

		private static $Initialized = false;              ///<初期化済みならtrue
		private static $Stream      = null;               ///<入力ストリーム
		private static $ReadSize    = 8192;               ///<入力ストリームから一度に読み込む長さ
		private static $Boundary    = '';                 ///<送信値の境界を表す値
		private static $EndOfQuery  = '';                 ///<送信値の末尾を表す値
		private static $UploadPath  = 'file/big_upload/'; ///<一時ファイルのアップロード先
	}

//	QueryParser::SaveQuery( 'file/query.log' ); //クエリ内容を確認したい場合にコメントアウトしてください
	QueryParser::Initialize();
