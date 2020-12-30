<?php
class autoMailLogic{

	function setup($mode){
		return new AutoMail($mode);
	}

	/*
	 * テンプレートデータを取得する
	*/
	function getData(&$au){
		$result = Array();
		$globResult = $au->getFileList();

		$cnt = 0;
		foreach( $globResult as $getEntry ){
			$result[$cnt]["path"] = $getEntry;
			$result[$cnt]["modified"] = filemtime($getEntry);
			$result[$cnt]["size"] = filesize($getEntry);
			$cnt++;
		}
		return $result;
	}


	function getFileData($au,$path){
		$current = $au->getTemplatePath().$au->getCommonPath();

		$result["path"] = $path;
		$result["modified"] = date("Y-m-d H:i:s",filemtime($path));
		$result["size"] = filesize($path);

		return $result;
	}

	function checkAccess($path){

	}
}