<?PHP

class CountView extends command_base
{

	function drawRankData( &$gm, $rec, $args )
	{
		global $loginUserType;
		global $loginUserRank;

		$type = $args[0];

		$db		 = GMList::getDB($type);
		$table = $db->getTable();

		$iDB = GMList::getDB("count");
		$iTable = $iDB->getTable();

		$table = $db->joinTable($table,$type,"count","id","owner");
		$table = $db->joinTableSearch($iDB,$table, 'c_type', '=', $type );
		$table = $db->joinTableSearch($iDB,$table, 'total', '>', 0 );
		$table = $db->joinTableSearch($iDB,$table,'year','=',date("Y"));
		$table = $db->sortTable($table,"total","desc");

		if($args[1]){
			$table = $db->limitOffset( $table,0,$args[1]);
		}else{
			$table = $db->limitOffset( $table,0,5);
		}

		$row=$db->getRow($table);


		$template = Template::getTemplate($loginUserType, $loginUserRank, "count", "ACCESS_RANKING");
		$buffer .= $gm->getString($template,null,"head");
		for($i=0;$i<$row;$i++){
			$rec = $db->getRecord($table, $i);
			$gm->setVariable("rank",$i+1);
			$buffer .= $gm->getString($template,$rec,"list");
		}
		$buffer .= $gm->getString($template,null,"foot");
		$this->addBuffer($buffer);
	}

	function drawRankData4c( &$gm, $rec, $args )
	{
		global $loginUserType;
		global $loginUserRank;
		global $LOGIN_ID;

		$type = $args[0];

		$db		 = GMList::getDB($type);
		$table = $db->getTable();
		$table = $db->searchTable($table, 'owner', '=', $LOGIN_ID );
		$iDB = GMList::getDB("count");
		$iTable = $iDB->getTable();

		$table = $db->joinTable($table,$type,"count","id","owner");

		$table = $db->joinTableSearch($iDB,$table, 'c_type', '=', $type );
		$table = $db->joinTableSearch($iDB,$table, 'total', '>', 0 );
		$table = $db->joinTableSearch($iDB,$table,'year','=',date("Y"));
		$table = $db->sortTable($table,"total","desc");


		if($args[1]){
			$table = $db->limitOffset( $table,0,$args[1]);
		}else{
			$table = $db->limitOffset( $table,0,5);
		}

		$row=$db->getRow($table);

		$template = Template::getTemplate($loginUserType, $loginUserRank, "count", "ACCESS_RANKING");
		$buffer .= $gm->getString($template,null,"head");
		if($row>0){
			for($i=0;$i<$row;$i++){
				$rec = $db->getRecord($table, $i);
				$gm->setVariable("rank",$i+1);
				$buffer .= $gm->getString($template,$rec,"list4c");
			}
		}else{
			$buffer .= $gm->getString($template,$rec,"failed");
		}
		$buffer .= $gm->getString($template,null,"foot");
		$this->addBuffer($buffer);
	}

}

?>