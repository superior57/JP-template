<?php
namespace Websquare\FileBase;

class FileBase implements iFileBase
{
    public function init($conf){
	}

    public function put($key,$resource=null)
	{
		@chmod( $key,0766);
		return ;
	}

    public function get($key){
		return file_get_contents($key);
	}

    public function rename($key1,$key2){
		return rename($key1,$key2);
	}

    public function delete($key){
		unlink($key);
	}

    public function copy($key,$key2){
		return copy($key,$key2);
	}

	public function is_dir($key){
		return is_dir($key);
	}

	public function is_file($key){
		return is_file($key);
	}

    public function file_exists($key){
		return file_exists($key);
	}
	
    public function getimagesize($key){
		return getimagesize($key);
	}

    public function filemtime($key){
		return filemtime($key);
	}
	
    public function getfilepath($key){
		return $key;
	}

    public function geturl($key){
		return $key;
	}

    public function upload($key,$key2){
		if( file_exists($key) && $key == $key2 )
		{ return true ; } 
		else if( copy($key,$key2) )
		{
			unlink($key);
			return true;
		}
		return false ;
	}

	public function fixRotate($key)
	{
		$exif = exif_read_data( $key );
		$flag = $exif[ 'Orientation' ];

		if( 2 > $flag ) //修正の必要がない場合
			{ return; }

		$details = $this->getimagesize( $key );
		$memory  = ini_get( 'memory_limit' );

		if( 'M' != substr( $memory , -1 ) )
			{ $memory = '8M'; }

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

		if( !( memory_get_usage() + $memoryNeeded < $memoryLimit ) )
			{ return; }

		$width    = $details[ 0 ];
		$height   = $details[ 1 ];
		$fileType = $details[ 2 ];
		$resource = $this->getimageresource( $fileType , $key );

		if( 7 <= $flag ) //右90度回転
			{ $resource = imagerotate( $resource , 90 , 0 ); }
		else if( 5 <= $flag ) //左90度回転
			{ $resource = imagerotate( $resource , -90 , 0 ); }
		else if( 3 <= $flag ) //180度回転
			{ $resource = imagerotate( $resource , 180 , 0 ); }

		if( 5 <= $flag ) //90度回転
			{ list( $width , $height ) = Array( $height , $width ); }

		if( $flag && ( 4 >= $flag ) ^ ( 1 & $flag ) ) //2,4,5,7は鏡像
		{
			$oldResource = $resource;
			$resource    = imagecreatetruecolor( $width , $height );

			imagecopyresampled( $resource , $oldResource , 0 , 0 , $width- 1 , 0 , $width , $height , -$width , $height );
			imagedestroy( $oldResource );
		}

		switch( $fileType )
		{
			case IMAGETYPE_GIF :
				$ret = imagegif( $resource , $key );
				break;

			case IMAGETYPE_JPEG :
				$ret = imagejpeg( $resource , $key , 100 );
				break;

			case IMAGETYPE_PNG :
				$ret = imagepng( $resource , $key , 0 );
				break;
		}
	}

    public function getimageresource($type,$key){
		$resource = false;

		///<画像リソースを取得する
		switch( $type )
		{
			case IMAGETYPE_GIF :
				$resource = imagecreatefromgif( $key );
				break;
			case IMAGETYPE_JPEG :
				$resource = imagecreatefromjpeg( $key );
				break;
			case IMAGETYPE_PNG :
				$resource = imagecreatefrompng( $key );
				break;
			default :
				break;
		}
		return $resource;
	}
}
