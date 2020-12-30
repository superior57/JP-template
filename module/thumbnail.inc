<?php

include_once './include/extends/GDImage.inc';
include_once "./custom/extends/thumbnailConf.php";

	/**
	 * サムネイル画像作成モジュール。
	 */

	class mod_Thumbnail extends command_base
	{
		private static $failedCase = false;

		/**
			@brief サムネイル画像を表示します。
			@param
				@li 0 画像のパスを指定します。
				@li 1 幅を指定します。
				@li 2 高さを指定します。
		*/
		function draw( &$_gm , $_rec , $_args )
		{
			$path   = $_args[ 0 ];
			$width  = $_args[ 1 ];
			$height = $_args[ 2 ];

			if( !$path || !is_file( $path ) )
			{
				self::$failedCase = true;
				return;
			}

			$thumbPath = self::Create( $path , $width , $height );
			$path      = 'src="' . $thumbPath . '"';
			$width     = 'width="' . $width . '"';
			$height    = 'height="' . $height . '"';

			$this->addBuffer( '<img ' . join( ' ' , array( $path , $width , $height ) ) . ' />' );

			self::$failedCase = false;
		}

		/**
			@brief サムネイルURLに変換します。
			@param
				@li 0 画像のパスを指定します。
				@li 1 幅を指定します。
				@li 2 高さを指定します。
		*/
		function url( &$_gm , $_rec , $_args )
		{
			$path   = $_args[ 0 ];
			$width  = $_args[ 1 ];
			$height = $_args[ 2 ];

			if( !$path || !file_exists( $path ) )
			{
				return;
			}

			$thumbPath = self::Create( $path , $width , $height );
			$this->addBuffer( $thumbPath );
		}

		/**
			@brief サムネイルの生成のみを行なう。
			@param
				@li 0 画像のパスを指定します。
				@li 1 幅を指定します。
				@li 2 高さを指定します。
		*/
		function dummy( &$_gm , $_rec , $_args )
		{
			$path   = $_args[ 0 ];
			$width  = $_args[ 1 ];
			$height = $_args[ 2 ];

			if( !$path || !file_exists( $path ) )
			{
				return;
			}

			self::Create( $path , $width , $height );
		}

		static function Useable( $_file , $_width , $_height , $_trimming = null )
		{
			global $FileBase;

			$file          = self::GetDetailName( $FileBase->getfilepath($_file ) );
			$thumbFileName = $_width . '_' . $_height . '_' . ( $_trimming ? 't' : 'f' ) . $file[ 'ext' ];
			$thumbPath     = $FileBase->getfilepath(self::CreatePath( $file ) . $thumbFileName);

			if( !is_file( $thumbPath ) )
				{ return false; }

			if( filemtime( $_file ) > filemtime( $thumbPath ) )
				{ return false; }

			return true;
		}

		static function Create( $_file , $_width , $_height , $_trimming = null )
		{
			global $FileBase;

			global $THUMBNAIL_OPTIONS;
			$file = self::GetDetailName( $_file );

			$thumbFileName = $_width . '_' . $_height . '_' . ( $_trimming ? 't' : 'f' ) . $file[ 'ext' ];

			$thumbPath = self::CreatePath( $file )			. $thumbFileName;
			$drawPath  = self::CreatePath( $file, true )	. $thumbFileName;

			if( $FileBase->file_exists( $thumbPath ) )
			{
				if( $FileBase->filemtime( $_file ) < $FileBase->filemtime($thumbPath) )
					{ return $FileBase->geturl($drawPath); }
			}

			self::CreateDir( $thumbPath );

			$origin    = new GDImage();
			$thumbnail = new GDImage();

			if( !$origin->open( $_file ) ) { return $_file; } // 画像サイズが大きすぎる場合はサムネイルは生成しない

			$origin->fixRotate();

			if( !$_width )
				$_width = $_height * ( $origin->getWidth() / $origin->getHeight() );

			if( !$_height )
				$_height = $_width * ( $origin->getHeight() / $origin->getWidth() );

			$thumbnail->create( $_width , $_height );
			$thumbnail->copy( $origin , $_trimming );
			$thumbnail->save( $thumbPath , $origin->getFileType() );

			$origin->close();
			$thumbnail->close();

			return $FileBase->geturl($drawPath);
		}

		/**
			@brief 画像のサムネイルを全て削除します。
			@param $_file 画像ファイルのパスを指定します。
		*/
		static function DeleteAll( $_file )
		{
			if( !$_file )
				return;

			$file = self::GetDetailName( $_file );

			if( !is_dir( './file/thumbs/' . $file[ 'dir' ]. $file[ 'name' ] ) )
				return;

			$dir = opendir( './file/thumbs/' . $file[ 'dir' ]. $file[ 'name' ] );

			while( false !== ( $entry = readdir( $dir ) ) )
			{
				if( '.' == $entry || '..' == $entry )
					continue;

				unlink( './file/thumbs/' . $file[ 'dir' ]. $file[ 'name' ] . '/' . $entry );
			}

			closedir( $dir );
			rmdir( './file/thumbs/' . $file[ 'dir' ]. $file[ 'name' ] );
		}

		/**
			@brief 詳細なファイル名を取得します。
			@param $_file 画像ファイルのパスを指定します。
		*/
		private static function GetDetailName( $_file )
		{
			$result[ 'md5' ] = md5( $_file );

			preg_match( '/(.*?)([^\/]*\/)([^\/]*)$/' , $_file , $match );
			$result[ 'path' ] = $match[ 1 ].$match[ 2 ];
			$result[ 'dir' ]  = $match[ 2 ];
			$_file            = $match[ 3 ];

			preg_match( '/(.*)(\.[^\.]*)/' , $_file , $match );
			$result[ 'name' ] = $match[ 1 ];
			$result[ 'ext' ]  = $match[ 2 ];

			return $result;
		}
		
		
		/**
			@brief  サムネイル画像を出力するパスに存在しないディレクトリがあった場合作成します。
			@param $_path 画像ファイルのパスを指定します。
		*/
		private static function CreateDir( $_path )
		{
			preg_match_all( '/([^\/]*\/)/' , $_path , $match );
			
			$path = "";
			foreach( $match[0] as $dir ){
				$path .= $dir;
				if( !is_dir( $path ) ){
					mkdir( $path, 0777 );
					chmod( $path, 0777 );
				}
			}
		}

		/**
			@brief  サムネイル画像用のパスを取得する。
			@retval 絶対パス 絶対パス指定オプションが有効の場合。
			@reval  相対パス 絶対パス指定オプションが無効の場合。
		*/
		private static function CreatePath( $_file, $_draw = false )
		{
			global $HOME;
			global $THUMBNAIL_OPTIONS;
			
			$path = $THUMBNAIL_OPTIONS[ 'filePath' ] . $_file[ 'dir' ] . $_file[ 'name' ] . '_' . $_file[ 'md5' ] . '/';

			if( $_draw && $THUMBNAIL_OPTIONS[ 'useAbsolutePath' ] ) //絶対パス指定オプションが有効の場合
			{
				if( preg_match( '/\/$/' , $HOME , $match ) ) //末尾がスラッシュの場合
					{ return $HOME . $path; }
				else //末尾がスラッシュでない場合
					{ return $HOME . '/'.$path; }
			}
			else //絶対パス指定オプションが無効の場合
				{ return './'.$path; }
		}
	}
?>