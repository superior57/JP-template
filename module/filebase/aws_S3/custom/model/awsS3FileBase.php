<?php
namespace Websquare\FileBase;

class FileBase implements iFileBase
{
    public function init($conf){
	}

    public function put($key,$resource=null){
		global $S3;
		return $S3->put($key,$resource);
	}

    public function get($key){
		global $S3;
		return file_get_contents($S3->getS3SchemePath($key));
	}

    public function rename($key1,$key2){
		global $S3;
		return $S3->rename($key1,$key2);
	}

    public function delete($key){
		global $S3;
		return $S3->unlink($key);
	}

    public function copy($key,$key2){
		global $S3;
		return $S3->copy($key,$key2);
	}

    public function is_dir($key){
		global $S3;
		return $S3->is_dir($key);
	}

	public function is_file($key){
		global $S3;
		return $S3->is_file($key);
	}

    public function file_exists($key){
		global $S3;
		return $S3->file_exists($key);
	}

    public function getimagesize($key){
		global $S3;
		return $S3->getimagesize($key);
	}

    public function filemtime($key){
		global $S3;
		return $S3->filemtime($key);
	}
	
	public function getfilepath($key){
		global $S3;
		return $S3->getS3SchemePath($key);
	}

    public function geturl($key){
		global $S3;
		return $S3->getUrl($key);
	}
	
    public function upload($key,$key2){
		global $S3;
		if( copy($key,$S3->getS3SchemePath($key2),$S3->createStream()))
		{
			unlink($key);
			return true;
		}
		else { return false ;}
	}

	// S3にファイルを配置した時点ではExif情報が欠落してしまう為動作しない。
	public function fixRotate($key)
	{
		return;
	}

    public function getimageresource($type,$key){
		global $S3;
		$resource = false ;

		///<画像リソースを取得する
		switch( $type )
		{
			case IMAGETYPE_GIF :
				$resource = $S3->imagecreatefromgif( $key );
				break;
			case IMAGETYPE_JPEG :
				$resource = $S3->imagecreatefromjpeg( $key );
				break;
			case IMAGETYPE_PNG :
				$resource = $S3->imagecreatefrompng( $key );
				break;
			default :
				break;
		}
		return $resource;
	}
}
