<?php


class add_subSystem extends System
{

	function drawSearch( &$gm, &$sr, $table, $loginUserType, $loginUserRank )
	{
		// 都道府県IDがセットされていない場合はエラー
		if( strlen($_GET['adds_id']) == 0 ) { Template::drawErrorTemplate(); return; }
		parent::drawSearch( $gm, $sr, $table, $loginUserType, $loginUserRank );
	}

	function drawSearchNotFound( &$gm, $loginUserType, $loginUserRank )
	{
		// 都道府県IDがセットされていない場合はエラー
		if( strlen($_GET['adds_id']) == 0 ) { Template::drawErrorTemplate(); return; }
		parent::drawSearchNotFound( $gm, $loginUserType, $loginUserRank );
	}
	function searchProc( &$gm, &$table, $loginUserType, $loginUserRank )
	{
		$type = SearchTableStack::getType();
		$db = $gm[ $type ]->getDB();
		$table = $db->sortTable( $table, 'sort_rank', 'asc' );
	}
}

?>
