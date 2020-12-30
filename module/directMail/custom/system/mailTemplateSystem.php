<?php
class mailTemplateSystem extends System{

	function editProc( &$gm, &$rec, $loginUserType, $loginUserRank ,$check = false)
	{
		$db	 = $gm[ $_GET['type'] ]->getDB();
		$db->setData($rec,"edit",time());

		parent::editProc( $gm, $rec, $loginUserType, $loginUserRank ,$check);
	}

	function searchProc(&$gm, &$table, $loginUserType, $loginUserRank){

		$db	 = $gm[ $_GET['type'] ]->getDB();

		if(isset($_GET["keyword"]))
			$table = $db->searchConcat($table,array("name","sub","main"),$_GET["keyword"]);

		parent::searchProc($gm, $table, $loginUserType, $loginUserRank);
	}
}