<?php
class mod_mailTemplateApi extends apiClass{


	function getMailTemplateData($param){
		$id = $param["tid"];
		$db = GMlist::getDB($this->getType());
		$rec = $db->selectRecord($id);

		if(isset($rec)){
			print json_encode($rec);
		}else{
			foreach($db->colName as $key)
				{ $empRecord[$key] = ""; }
			print json_encode($empRecord);
		}
	}

	function getType(){
		return "mailTemplate";
	}
}