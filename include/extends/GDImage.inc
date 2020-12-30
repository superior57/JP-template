<?php

	/**
	 * GDライブラリによるサムネイル作成クラス。
	 * ※modコマンドからは呼び出せません。
	 */

	class GDImage
	{
		private $resource = null; ///<画像リソース
		private $filePath = null; ///<画像パス
		private $fileType = '';   ///<画像フォーマット
		private $width    = 0;    ///<画像の幅
		private $height   = 0;    ///<画像の高さ

		function create( $_width , $_height )
		{
			//リソースを作成する
			$this->resource = imagecreatetruecolor( $_width , $_height );

			if( !$this->resource )
				return false;

			$this->width  = $_width;
			$this->height = $_height;

			imagefill( $this->resource , 0 , 0 , imagecolorallocate( $this->resource , WS_SYSTEM_GDIMAGE_BACKGROUND_R , WS_SYSTEM_GDIMAGE_BACKGROUND_G , WS_SYSTEM_GDIMAGE_BACKGROUND_B ) );

			return true;
		}

		function close()
		{
			if( $this->resource )
				imagedestroy( $this->resource );

			$this->resource = null;
			$this->filetype = '';
			$this->width    = 0;
			$this->height   = 0;
		}

		function getFileType()
		{
			return $this->fileType;
		}

		function getWidth()
		{
			return $this->width;
		}

		function getHeight()
		{
			return $this->height;
		}

		function open( $_file )
		{
			global $FileBase;
			$image_f = false;
			//ファイルの有無を確認する
			if( !$FileBase->file_exists($_file) && !file_exists($_file) ){
				if( strpos( $_file, 'http://' ) !== FALSE ){
					$head = get_headers( $_file, 1 );
					if( strpos( $head[0], '200' ) !== FALSE ){
						if( strpos( $head['Content-Type'], 'image' ) !== FALSE ){
							$image_f = true;
						}
					}
				}
			}else{
				$image_f = true;
			}
			
			if( !$image_f ){ return $image_f; }

			//ファイルの情報を取得する
			$details = $FileBase->getimagesize($_file);

			if( !$details[ 0 ] || !$details[ 1 ] ) //幅・高さが取得できない
				return false;

			// メモリーリミットを取得
			$memory = ini_get('memory_limit');
			if( substr($memory, -1) != 'M' ) { $memory = '8M'; }
			if( !$this->checkMemory( $details, $memory ) )
				return false;

			$this->width    = $details[ 0 ];
			$this->height   = $details[ 1 ];
			$this->fileType = $details[ 2 ];

			$this->resource = $FileBase->getimageresource($this->fileType,$_file);

			if( !$this->resource )
				return false;

			$this->filePath = $FileBase->getfilepath($_file);

			return true;
		}

		function fixRotate()
		{
			global $FileBase;
			if( !$FileBase ){ return ; }

			$exif = exif_read_data( $this->filePath );
			$flag = $exif[ 'Orientation' ];

			if( 7 <= $flag ) //右90度回転
				{ $this->resource = imagerotate( $this->resource , 90 , 0 ); }
			else if( 5 <= $flag ) //左90度回転
				{ $this->resource = imagerotate( $this->resource , -90 , 0 ); }
			else if( 3 <= $flag ) //180度回転
				{ $this->resource = imagerotate( $this->resource , 180 , 0 ); }

			if( 5 <= $flag ) //90度回転
				{ list( $this->width , $this->height ) = Array( $this->height , $this->width ); }

			if( $flag && ( 4 >= $flag ) ^ ( 1 & $flag ) ) //2,4,5,7は鏡像
			{
				$newResource = imagecreatetruecolor( $this->width , $this->height );
				$oldResource = $this->resource;
				imagecopyresampled( $newResource , $this->resource , 0 , 0 , $this->width- 1 , 0 , $this->width , $this->height , -$this->width , $this->height );

				$this->resource = $newResource;

				imagedestroy( $oldResource );
			}

			return true;
		}

		function copy( &$_origin , $_trimming = null )
		{
			global $THUMBNAIL_OPTIONS;

			if( !$this->resource || !$_origin->resource )
				return false;

			$wRate = $_origin->width / $this->width;
			$hRate = $_origin->height / $this->height;

			if( is_null( $_trimming ) )
			{
				if( $THUMBNAIL_OPTIONS[ 'trimming' ] )
					{ list( $wRate , $hRate ) = Array( $hRate , $wRate ); }
			}
			else if( $_trimming )
				{ list( $wRate , $hRate ) = Array( $hRate , $wRate ); }

			if( $wRate > $hRate )
			{
				$newWidth  = $this->width;
				$newHeight = ($_origin->height / $_origin->width ) * $newWidth;
			}
			else
			{
				$newHeight = $this->height;
				$newWidth  = ( $_origin->width / $_origin->height ) * $newHeight;
			}

			$newX = ( $this->width - $newWidth ) / 2 + 0.5;
			$newY = ( $this->height - $newHeight ) / 2 + 0.5;

			return imagecopyresampled( $this->resource , $_origin->resource , $newX , $newY , 0 , 0 , $newWidth , $newHeight , $_origin->width , $_origin->height );
		}

		function save( $_newPath , $_ext = null )
		{
			global $THUMBNAIL_OPTIONS;
			global $FileBase;

			if( !$this->resource )
				return false;

			///<指定がなければ自分のフォーマットを使う
			if( is_null( $_ext ) )
				$_ext = $this->fileType;
			$ret = false;
			switch( $_ext )
			{
				case IMAGETYPE_GIF :
					$ret = imagegif( $this->resource , $_newPath );
					break;

				case IMAGETYPE_JPEG :
					$ret = imagejpeg( $this->resource , $_newPath , $THUMBNAIL_OPTIONS[ 'jpgQuarity' ] );
					break;

				case IMAGETYPE_PNG :
					$ret = imagepng( $this->resource , $_newPath ,  $THUMBNAIL_OPTIONS[ 'pngQuarity' ] );
					break;
			}
			if($ret){
				$FileBase->put($_newPath,$ret);
			}
			return $ret;
		}

		// リサイズを実行するのに必要なメモリが利用可能か確認
		// http://jp2.php.net/manual/ja/function.imagecreatefromjpeg.php#64155
		function checkMemory( $imageInfo, $memory )
		{
			$MB = 1048576;  // number of bytes in 1M
			$K64 = 65536;    // number of bytes in 64K
			$TWEAKFACTOR = 2.2;  // Or whatever works for you
			$memoryNeeded = round( ( $imageInfo[0] * $imageInfo[1]
												   * $imageInfo['bits']
												   * $imageInfo['channels'] / 8
									 + $K64
								   ) * $TWEAKFACTOR
								 );
			$memoryLimit = (int)substr($memory, 0, -1) * $MB;
			$result = false;
			if( memory_get_usage() + $memoryNeeded < $memoryLimit ) { $result = true; }

			return $result;
		}

	}

?>