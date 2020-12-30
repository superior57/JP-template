<?php
class AutoMail{
	private $templatePath = null;
	private $commonPath = "other/mail_contents/";
	private $acceptPath = "*/*.txt";
	private $fileList = array();

	/*
	 * コンストラクタ
	 */
	function __construct($mode = null){
		if(is_null($mode))
			$mode = $_GET["mode"];

		Concept::IsNotEmpty($mode)->OrThrow("InvalidQuery");

		$this->setTemplatePath($mode);
		$this->setFileList();
	}

	function verify($path){
		return in_array($path,$this->getFileList());
	}

	function write($path,$contents){
		if(!$this->verify($path)) return;
		$contents = rawurldecode($contents);
		file_put_contents($path, $contents);
		return true;
	}

	function read($path){
		if(!$this->verify($path)) return;
		$contents = file_get_contents($path);
		return $contents;
	}

	/*
	 * テンプレートパスを指定する
	 *
	 * string $mode  指定したいパスに応じてpc,mobile,spを指定する
	 */
	function setTemplatePath($mode){
		global $template_path;

		switch($mode){
			case "pc";
				$path = "template/pc/";
				break;
			case "mobile";
				$path = "template/mobile/";
				break;
			case "sp";
				$path = "template/sp/";
				break;
			default:
				Concept::IsTrue(false)->OrThrow();
		}
		$this->templatePath = $path;
	}

	function setFileList(){
		$path = $this->getTemplatePath().$this->getCommonPath().$this->getAcceptPath();
		$this->fileList = glob( "{./,./module/*/}".$path ,GLOB_BRACE );
	}

	/*
	 * テンプレートパスを取得する
	 */
	function getTemplatePath(){
		return $this->templatePath;
	}

	function getCommonPath(){
		return $this->commonPath;
	}

	function getAcceptPath(){
		return $this->acceptPath;
	}

	function getMode(){
		return $this->mode;
	}

	function getFileList(){
		return $this->fileList;
	}
}