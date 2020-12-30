<?php

cron_master::setCron('countUpdate','CountCron','update');
cron_master::setCron('jobCountUpdate','CountCron','jobTotal');

class CountCron
{	
	/**
	 * cronを実行した当日の各求人情報のPV数をcountテーブルのtotalカラムに上書きする
	 */
	function jobTotal()
	{
	
		$db = GMList::getDB("count");
		$table = $db->getTable();
		$table = $db->searchTable($table,"year","=",date('Y'));
		$row = $db->getRow($table);

		for($i=0;$i<$row;$i++){
		
			$rec=$db->getRecord($table,$i);
			$countData=unserialize($db->getData($rec,"count_data"));
			$total=$countData[date('Y')][date('m')][date('d')];
			if(!is_null($total)){
				$db->setData($rec,"total",$total);
				$db->updateRecord($rec);
			}else{
				$db->setData($rec,"total",0);
				$db->updateRecord($rec);
			}
		}

	}
	
	/**
	 * 特徴の件数事前集計
	 */
	function update()
	{
	
		$dbList = array( 'job_addition', 'items_type', 'items_form' );
		foreach( $dbList as $db )
		{
			$userList = array( 'nUser','nobody' );
			foreach( $userList as $user )
			{
				$countMid = new Count( $db, $user, "mid" );
				$countFresh = new Count( $db, $user, "fresh" );

				$countMid->deleteAll();
				$countFresh->deleteAll();

				$countList = CountLogic::controller( 'mid', $db, null, $user );
				$countMid->update( $countList );

				$countList = CountLogic::controller( 'fresh', $db, null, $user );
				$countFresh->update( $countList );
			}
		}

		$adds = Conf::getData( 'job', 'def_adds');
		$param['adds'] = $adds;
		$param['adds_PAL'][] = 'match comp';

		$userList = array( 'nUser', 'nobody' );
		foreach($userList as $user){
			
			$countMid = new Count( 'area', $user, "mid" );
			$countFresh = new Count( 'area', $user, "fresh" );
			$countMid->deleteAll();
			$countFresh->deleteAll();
			
			if( strlen($adds) ) { $countList = CountLogic::controller( 'mid', 'add_sub', $param, $user ); }
			else				{ $countList = CountLogic::controller( 'mid', 'adds', null, $user ); }

			$countMid->update( $countList );
			
			if( strlen($adds) ) { $countList = CountLogic::controller( 'fresh', 'add_sub', $param, $user ); }
			else				{ $countList = CountLogic::controller( 'fresh', 'adds', null, $user ); }
			
			$countFresh->update( $countList );
			
		}

	}

}