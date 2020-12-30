<?php
class threadLogic{

	private function createThreadID($cUser,$nUser){
		$thread["cUser"] = $cUser;
		$thread["nUser"] = $nUser;
		$json = json_encode($thread);
		return base64_encode($json);
	}

	static function getThreadID($cUser,$nUser){
		if(!isset($cUser)		|| !strlen($cUser))		return ;
		if(!isset($nUser)	|| !strlen($nUser))	return ;

		return self::createThreadID($cUser,$nUser);
	}

	static function getData($thread_id){
		$dec = base64_decode($thread_id);
		return json_decode($dec,true);
	}
}