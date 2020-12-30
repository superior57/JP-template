<?php
class mod_cmsSPCode extends command_base{

	/*
	 *  変数挿入のプルダウンを描画
	*/
	function drawSelectVariable( &$_gm , $_rec , $_args ){
		global $loginUserType;
		global $loginUserRank;
		List($type) = $_args;
		$gm = GMList::getGM("system");
		$template = Template::getTemplate($loginUserType, $loginUserRank, "cmsSPCode", "VARIABLE_SELECTOR_PARTS");

		$buffer = "";
		$buffer = $gm->getString($template,null , "variable_selectbox_".$type);
		$this->addBuffer($buffer);
	}
}