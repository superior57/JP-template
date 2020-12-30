<?php
class mod_autoMailApi extends apiClass{

	function getMailTemplate(&$param){
		$mode = $param["mode"];
		$path = $param["path"];

		$am = autoMailLogic::setup($mode);
		$contents = $am->read($path);
		print $contents;
	}

	function templateSave(&$param){
		$mode = $param["mode"];
		$path = $param["path"];

		$am = autoMailLogic::setup($mode);
		if($am->write($path,$param["contents"])){
			print "ok";
		}else{
			print "ng";

		}
	}
}