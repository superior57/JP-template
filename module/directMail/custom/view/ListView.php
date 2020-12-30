<?php

class listView extends command_base{

	function drawListCnt( &$_gm, $_rec, $_args ){
		List($id) = $_args;
		$db = GMList::getDB(self::getType());
		$table = DMList::getUserTable($id);
		$row = $db->getRow($table);

		$this->addBuffer($row);
	}

	function drawRejectCnt( &$_gm, $_rec, $_args ){
		List($id) = $_args;
		$db = GMList::getDB(self::getType());
		$table = DMList::getUserTable($id);
		$table = $db->searchTable($table,"forse_reject","=",true);
		$row = $db->getRow($table);

		$this->addBuffer($row);
	}


	function getType(){
		return "list";
	}
}
