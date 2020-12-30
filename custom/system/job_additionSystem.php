<?php
class job_additionSystem extends System{
	function searchProc( &$gm, &$table, $loginUserType, $loginUserRank )
	{
		$type = SearchTableStack::getType();
		$db = $gm[ $type ]->getDB();
		$table = $db->sortTable( $table, 'sort_rank', 'asc' );
	}

}