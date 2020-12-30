<?php

class resetterView extends command_base{

	function verifyToken(&$_gm, $_rec, $_args){
		List($token) = $_args;
		$api = new mod_resetterApi();
		$result = $api->verifyResetToken($token)?"TRUE":"FALSE";
		$this->addBuffer($result);;
	}
}