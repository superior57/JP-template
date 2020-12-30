<?php
use Aws\S3\S3Client;

class S3{

	private static $debug ;
	private static $debugDirName ;
	private static $usedflag = false;

	Const S3SCHEME = "s3://";
	Const DIR_SEPARATOR = "/";

	private $s3 = null;
	private $exitstsFiles = array();

	function __construct(){
		global $AWS_S3_DEBUG;
		global $AWS_S3_DEBUG_DIR;
		global $AWS_S3_USEDFLAG;
		global $AWS_S3_ACCESS_KEY;
		global $AWS_S3_SEACRET_KEY;

		self::$debug = $AWS_S3_DEBUG;
		self::$debugDirName = $AWS_S3_DEBUG_DIR;

		if( $AWS_S3_USEDFLAG && strlen($AWS_S3_ACCESS_KEY) && strlen($AWS_S3_SEACRET_KEY) )
		{
			self::$usedflag = true;
			$this->s3 =	new S3Client(
				array(
					'credentials' => array(
						'key'    => $AWS_S3_ACCESS_KEY,
						'secret' => $AWS_S3_SEACRET_KEY,
					),
					'region' => "ap-northeast-1",
					'version' => '2006-03-01',
				)
			);
		}

		if($this->s3 != null)
		{
			$this->s3->registerStreamWrapper();
		}
	}

	//バケットへファイルを設置する
	function put($key,$resource){
		if(!self::getUsedflag())
		{
			// 保存先がローカルのときは、移動先のディレクトリを確認する
			$tmp_path = explode('/', $key);
			array_pop($tmp_path);
			$directory = implode('/', $tmp_path);
			if(!is_dir($directory)) { mkdir( $directory, 0777, true ); }

			@chmod( $key, 0766 );
			return ; 
		}
		elseif(!is_resource($resource)){
			if(file_exists($resource))
				{ $resource = fopen($resource,"r"); }
			else if(file_exists($key))
				{ $resource = file_get_contents($key); }
		}

		return file_put_contents($this->getS3SchemePath($key),$resource,0,$this->createStream());
	}

	//バケットからファイルを削除する
	function unlink($key){
		return unlink($this->getS3SchemePath($key));
	}

	function rename($oldfile,$newfile){
		$oldfile = $this->getS3SchemePath($oldfile);
		$newfile = $this->getS3SchemePath($newfile);
		if(!self::getUsedflag())
		{
			// 保存先がローカルのときは、移動先のディレクトリを確認する
			$tmp_path = explode('/', $newfile);
			array_pop($tmp_path);
			$directory = implode('/', $tmp_path);
			if(!is_dir($directory)) { mkdir( $directory, 0777, true ); }
		}
		return rename($oldfile,$newfile,$this->createStream());
	}

	//バケットのファイルをコピーする
	function copy($before,$after){
		$before = $this->getS3SchemePath($before);
		$after = $this->getS3SchemePath($after);
		if(!self::getUsedflag())
		{
			// 保存先がローカルのときは、移動先のディレクトリを確認する
			$tmp_path = explode('/', $after);
			array_pop($tmp_path);
			$directory = implode('/', $tmp_path);
			if(!is_dir($directory)) { mkdir( $directory, 0777, true ); }
		}
		return copy($before,$after,$this->createStream());
	}

	//バケットのファイル内容を取得する
	function get($key){
		return file_get_contents($this->getS3SchemePath($key));
	}

	//ディレクトリかどうか判定
	function is_dir($key){
		$path = $this->getS3SchemePath($key);
		return filetype($path) === "dir";
	}

	//ファイルかどうか判定
	function is_file($key){
		$path = $this->getS3SchemePath($key);
		return filetype($path) === "file";
	}

	//バケットへのフルパスのURLを取得する
	function getUrl($key,$scheme = "https"){
		global $AWS_S3_BUCKET_NAME;
		global $AWS_S3_PARTITION ;

		if(empty($key)) return;
		if(!self::$usedflag)
		{
			return $key;
		}

		if(self::$debug)
		{
			return $this->s3->getObjectUrl($AWS_S3_BUCKET_NAME,$AWS_S3_PARTITION.self::DIR_SEPARATOR.self::$debugDirName.self::DIR_SEPARATOR.self::filter($key),null,array("Scheme"=>$scheme));
		}
		else{
			return $this->s3->getObjectUrl($AWS_S3_BUCKET_NAME,$AWS_S3_PARTITION.self::DIR_SEPARATOR.self::filter($key),null,array("Scheme"=>$scheme));
		}

	}

	function createStream(){
		if(!self::getUsedflag())
		{
			return stream_context_create();
		}
		$context = stream_context_create(array(
			's3' => array(
				'ACL' => 'public-read'
			)
		));
		return $context;
	}

	function getimagesize($key){
		return getimagesize($this->getUrl($key));
	}

	//ファイルの有無判定
	function file_exists($key){
		return file_exists($this->getS3SchemePath($key));
	}

	function imagecreatefromgif($key){
		return imagecreatefromgif($this->getS3SchemePath($key));
	}

	function imagecreatefromjpeg($key){
		return imagecreatefromjpeg($this->getS3SchemePath($key));
	}

	function imagecreatefrompng($key){
		return imagecreatefrompng($this->getS3SchemePath($key));
	}

	function filemtime($key){
		return filemtime($this->getS3SchemePath($key));
	}

	//keyの文字列から不純物を取り去る
	function filter($key){
		return str_replace("","",$key);
	}

	//debugモードを有効にする
	function onDebug(){
		self::$debug = true;
	}

	//debugモードを無効にする
	function offDebug(){
		self::$debug = false;
	}

	//usedfalgを返す
	function getUsedflag(){
		return self::$usedflag;
	}
	
	//接続設定確認
	function getS3Setting(){
		global $AWS_S3_BUCKET_NAME;
		if(self::$usedflag)
		{
			try{
				$result = $this->s3->listObjects( array('Bucket' => $AWS_S3_BUCKET_NAME));
				return "ok";
			} catch (Exception $ex) {
				return "error";
			}
		}  else {
			return "not";
		}
	}
	
	//S3スキーマを付加したバケット内パスを返す
	function getS3SchemePath($key){
		global $AWS_S3_BUCKET_NAME;
		global $AWS_S3_PARTITION ;
		
		if(!self::getUsedflag())
		{
			return $key;
		}
		else if(self::$debug)
		{
			$path = self::S3SCHEME.$AWS_S3_BUCKET_NAME.self::DIR_SEPARATOR.$AWS_S3_PARTITION.self::DIR_SEPARATOR.self::$debugDirName.self::DIR_SEPARATOR.self::filter($key);
		}
		else
		{
			$path = self::S3SCHEME.$AWS_S3_BUCKET_NAME.self::DIR_SEPARATOR.$AWS_S3_PARTITION.self::DIR_SEPARATOR.self::filter($key);
		}

		return $path;
	}
}
