<?php
//★クラス //

/**
	@brief systemクラス。
*/
class article_categorySystem extends System
{
	function searchProc(&$gm, &$table, $loginUserType, $loginUserRank)
	{
		$db = $gm[articleLogic::$category_type ]->getDB();
		$cnt = 0;
		if( isset($_GET['child']) ) { $cnt = (int)$_GET['child']+1; }
		$gm[articleLogic::$category_type ]->setVariable('child', $cnt);
		if( isset($_GET['child_view'])) {
			$table = $db->searchTable( $table, 'parent', '!', '' );
		}
		else {
			$table = $db->searchTable( $table, 'parent', 'like', '' );
		}
	}

	function infoCheck(&$gm, &$rec, $loginUserType, $loginUserRank)
	{
		return true;
	}

	function drawDeleteCheck(&$gm, &$rec, $loginUserType, $loginUserRank)
	{
		$db = $gm[articleLogic::$category_type ]->getDB();
		$id = $db->getData($rec, 'id');
		$table = $db->getTable();
		$table = $db->searchTable($table, 'parent', 'like', $id);
		if( $db->existsRow($table) ) {
			$gm[articleLogic::$category_type ]->setVariable('existsChild', '1');
		}
		parent::drawDeleteCheck($gm, $rec, $loginUserType, $loginUserRank);

	}
	
}