<?php
class Entry{

	/*
	 * 求人の応募数を取得する
	 */
	static function getTotalEntry($itemsID,$userID = null){
		$db = GMList::getDB(self::getType());
		$table = $db->getTable();
		$table = $db->searchTable($table,"items_id","=",$itemsID);
		if(!is_null($userID))
			{ $table = $db->searchTable( $table , 'entry_user' , '=' , $userID ); }

		return $db->getRow($table);
	}

	//面接に至らず、不採用が決定していない応募数を取得
	static function getWaitApplicant($itemsID){
		$db = GMList::getDB(self::getType());
		$table = $db->getTable();
		$table = $db->searchTable($table,"items_id","=",$itemsID);
		$table = $db->searchTable($table,"status","not in",array("EP001","FAILE"));
		return $db->getRow($table);
	}

	static function getID($itemsID,$userID){
		$db = GMList::getDB(self::getType());
		$table = $db->getTable();
		$table = $db->searchTable($table,"items_id","=",$itemsID);
		$table = $db->searchTable( $table , 'entry_user' , '=' , $userID );
		if($db->existsRow($table)){
			$rec = $db->getFirstRecord($table);
			return $db->getData($rec,"id");
		}
		return false;
	}

	//ユーザーが指定企業への応募した案件IDを返す
	static function getApplyItemsID($cID,$userID){
		$db = GMList::getDB(self::getType());
		$table = $db->getTable();
		$table = $db->searchTable( $table , 'items_owner' , '=' , $cID );
		$table = $db->searchTable( $table , 'entry_user' , '=' , $userID );

		return $db->getDataList($table,"items_id");
	}


	/****
	 * 進捗一覧を取得
	 * 
	 * @return 進捗配列
	 ****/
	function getProgressList()
	{
		$db = GMList::getDB('entry_progress');

		$table = $db->getTable();
		$table = $db->sortTable( $table, 'sort_rank', 'asc' );

		$row = $db->getRow($table);
		$list = array();
		for( $i=0; $i<$row; $i++ )
		{
			$rec = $db->getRecord( $table, $i );

			$list[ $db->getData( $rec, 'id' )] = $db->getData( $rec, 'name' );
		}

		return $list;
	}


	/****
	 * 求人で絞り込んだ進捗別応募数を返す
	 * 
	 * @param type mid/fresh
	 * @param id 求人ID
	 * @return 進捗別応募数
	 ****/
	function getCountByJob( $type, $id )
	{
		$db = GMList::getDB('entry');

		$table = $db->getTable();
		$table = $db->searchTable( $table, 'items_type', '=', $type );
		$table = $db->searchTable( $table, 'items_id', '=', $id );
		$table = $db->getCountTable( "status", $table );
		$row = $db->getRow($table);
		$countList = array();
		for( $i=0; $i<$row; $i++ )
		{
			$rec = $db->getRecord( $table, $i );

			$countList[$db->getData( $rec, 'status' )] = $db->getData( $rec, 'cnt' );
		}

		return $countList;
	}


	/****
	 * 企業で絞り込んだ進捗別応募数を返す
	 * 
	 * @param type mid/fresh
	 * @param id 求人ID
	 * @return 進捗別応募数
	 ****/
	function getCountBycUser( $id )
	{
		$db = GMList::getDB('entry');

		$table = $db->getTable();
		$table = $db->searchTable( $table, 'items_owner', '=', $id );
		$table = $db->getCountTable( "status", $table );
		$row = $db->getRow($table);
		$countList = array();
		for( $i=0; $i<$row; $i++ )
		{
			$rec = $db->getRecord( $table, $i );

			$countList[$db->getData( $rec, 'status' )] = $db->getData( $rec, 'cnt' );
		}

		return $countList;
	}

	function getType(){
		return "entry";
	}
}