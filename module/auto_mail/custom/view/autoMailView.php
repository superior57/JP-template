<?php
class autoMailView extends command_base{

	function drawEditableList($_gm,$_rec,$_args){
		global $loginUserType;
		global $loginUserRank;

		List($mode) = $_args;

		$am = autoMailLogic::setup($mode);
		$data = autoMailLogic::getData($am);

		$design = Template::getTemplate($loginUserType, $loginUserRank, "automail", "AUTO_MAIL_EDITABLE_LIST");

		$gm = GMList::getGM("system");
		$gm->setVariable("mode",$mode);
		foreach($data as $datum){
			$gm->setVariable("path",$datum["path"]);
			$gm->setVariable("modified",date("Y-m-d H:i:s",$datum["modified"]));
			$gm->setVariable("size",$datum["size"]);
			$buffer .= $gm->getString($design,null);
		}
		$this->addBuffer($buffer);
	}

	function drawFileData($_gm,$_rec,$_args){
		List($path,$type,$mode) = $_args;
		$am = autoMailLogic::setup($mode);

		Concept::IsTrue($am->verify($path))->OrThrow("IllegalAccess");

		$data = autoMailLogic::getFileData($am,$path);

		$this->addBuffer($data[$type]);
	}
}
