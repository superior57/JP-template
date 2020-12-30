<?php

class Job
{
	static $recList;

	/**
	 * 指定IDのレコードを返す。
	 *
	 * @param id 求人レコードID。
	 * @return 求人レコード。
	 */
	function getRecord( $type, $id )
	{
		if( isset(self::$recList[$id]) ) { return self::$recList[$id]; }

		$db  = GMList::getDB($type);
		self::$recList[$id] = $db->selectRecord( $id );

		return self::$recList[$id];
	}

    /**
     * 応募上限到達フラグをセット
     *
     * @param Database $db  DBオブジェクト
     * @param array    $rec レコードデータ
     *
     * @return void
     */
    function setApplyPos($db, &$rec)
    {
        $applyPos = false;
        $id = $db->getData($rec, "id");

        if (SystemUtil::convertBool($db->getData($rec, 'use_max_apply'))) {
            $applyPos = Entry::getWaitApplicant($id) >= $db->getData($rec, 'max_apply');
        }

        $db->setData($rec, 'apply_pos', $applyPos);
    }

	/**
	 * おすすめ掲載期間を延長する。
	 *
	 * @param id 求人ID。
	 * @param day おすすめ掲載日数。
	 */
	function addAttention( $id, $day )
	{
		$rec = self::getRecord( $id );
		if( isset($rec) )
		{
			$db  = GMList::getDB('job');

			$now = time();
			$time = $db->getData( $rec, 'attention_time' );
			if( $time < $now  ) { $time = $now; }

			$time += $day * 86400;

			$year = date( 'Y', $time );
			$month = date( 'm', $time );
			$day = date( 'd', $time );

			$db->setData( $rec , 'attention_year' , $year );
			$db->setData( $rec , 'attention_month' , $month );
			$db->setData( $rec , 'attention_day' , $day );
			$db->setData( $rec , 'attention_time' , DateUtil::getUnixTime($year, $month, $day, 'end') );
			$db->updateRecord( $rec );

			self::$recList[$id] = $rec;
		}

	}


	/**
	 * おすすめ掲載期間を差し引く。
	 *
	 * @param id 求人ID。
	 * @param day おすすめ掲載日数。
	 */
	function minusAttention( $id, $day )
	{
		$rec = self::getRecord( $id );
		if( isset($rec) )
		{
			$db  = GMList::getDB('job');

			$time  = $db->getData( $rec, 'attention_time' );
			$time -= $day * 86400;

			$year = date( 'Y', $time );
			$month = date( 'm', $time );
			$day = date( 'd', $time );

			$db->setData( $rec , 'attention_year' , $year );
			$db->setData( $rec , 'attention_month' , $month );
			$db->setData( $rec , 'attention_day' , $day );
			$db->setData( $rec , 'attention_time' , DateUtil::getUnixTime($year, $month, $day, 'end') );
			$db->updateRecord( $rec );

			self::$recList[$id] = $rec;
		}

	}


	/**
	 * 指定オーナーの求人情報を全て削除する。
	 *
	 * @param owner 求人オーナーID。
	 */
	function deleteTable( $owner )
	{
		if( !is_array($owner) ) { $owner = explode( "/", $owner ); }

		$db = GMList::getDB('job');

		$table = $db->getTable();
		$table = $db->searchTable( $table, 'owner', 'in', $owner );
		$db->deleteTable($table);
	}


	/**
	 * 応募資格を配列で返す。
	 *
	 * @param id 求人レコードID。
	 * @return 応募資格配列。
	 */
	function getReq( $id )
	{
		$rec = self::getRecord( $id );
		if( !isset($rec) ) { return array(); }

		$db  = GMList::getDB('job');

		$colList = array( 'req01', 'req02', 'req03', 'req04', 'req05' );
		$data = array();
		foreach( $colList as $col )
		{
			$tmp = $db->getData( $rec, $col );
			if( strlen($tmp) ) { $data[$col] = SystemUtil::ccEscape($tmp); }
		}

		return $data;
	}


	/**
	 * 求人の課金方式を一括設定する
	 *
	 * @param term_type apply/employment
	 * @return 設定件数
	 */
	function setCharges($term_type)
	{
		$job = array('mid','fresh');

		foreach($job as $type){
			$db	 = GMList::getDB($type);
			$table = $db->getTable();

			$pDB = GMList::getDB("pay_job");
			$pTable = pay_jobLogic::getLsatTerm($type);
			$pTable = $pDB->searchTable($pTable,"target_id","!","");
			$limitContractUser = $pDB->getDataList($pTable, "owner");

			switch($term_type)
			{
				case 'apply': $notChange = 'employment'; break;
				case 'employment': $notChange = 'apply'; break;
			}

			if(cUserLogic::checkPlanSelect()){
				if( Conf::getData( 'charges', $notChange ) == 'on' ) {
					$table = $db->searchTable( $table, 'term_type', '!', $notChange );
				}

				$limitTable = $db->searchTable($table, "owner", "in", $limitContractUser);
				$jobTable	= $db->searchTable($table, "owner", "not in", $limitContractUser);

				$row += $db->getRow($table);

				$db->setTableDataUpdate( $limitTable, 'term_type', "user_limit" );
				$db->setTableDataUpdate( $jobTable, 'term_type', $term_type );
			}else{
				$table = $db->searchTable( $table, 'term_type', '!', $term_type );
				if( Conf::getData( 'charges', $notChange ) == 'on' ) {
					$table = $db->searchTable( $table, 'term_type', '!', $notChange );
				}

				$row += $db->getRow($table);
				$db->setTableDataUpdate( $table, 'term_type', $term_type );
			}
		}

		return $row;
	}


	/**
	 * 求人企業の課金方式を元に求人の課金方式を一括設定する
	 */
	function setChargesBycUser()
	{
		if( Conf::getData( 'charges', 'plan_select' ) == 'on' ) { return; }

		$db	 = GMList::getDB('mid');

		//ここでCuserの求人企業の中で、月額課金をしているIDリストを取得する何かを用意する
		//$idList = cUserLogic::getUl_termIdList();
		$pDB = GMList::getDB("pay_job");
		$table = pay_jobLogic::getLsatTerm("mid");
		$table = $pDB->searchTable($table,"target_id","!","");

		$idList = $pDB->getDataList($table, "owner");

		if( count($idList) > 0 ) {

			$table = $db->getTable();
			$table = $db->searchTable( $table, 'owner', 'in', $idList );

			$row += $db->getRow($table);

			$table = $db->setTableDataUpdate( $table, 'term_type', 'user_limit' );//求人情報のterm_typeにapplyでもemploymentでもないときの値をセット

		}
		$db	 = GMList::getDB('fresh');


		if( count($idList) == 0 ) { return; }

		$table = $db->getTable();
		$table = $db->searchTable( $table, 'owner', 'in', $idList );

		$row += $db->getRow($table);

		$table = $db->setTableDataUpdate( $table, 'term_type', 'user_limit' );

		return $row;
	}

	static function updateAttention($id,$limits){
		$db = GMList::getDB(SystemUtil::getJobType($id));
		$rec = $db->selectRecord($id);
		$db->setData($rec, "attention", true);
		$db->setData($rec, "attention_time", $limits);
		$db->updateRecord($rec);
	}

	static function updateRegist($type,$userID,$regist){
		$db = GMList::getDB($type);
		$table = $db->getTable();
		$table =$db->searchTable($table,"owner","=",$userID);
		if($db->existsRow($table))
			$db->setTableDataUpdate($table,"regist",$regist);
	}
}

?>