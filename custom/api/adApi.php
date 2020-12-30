<?php

	class mod_adApi extends apiClass
	{
		function clickCount( &$param )
		{
			
			$db = GMlist::getDB("count");
			$table	 = $db->getTable() ;
			$table	 = $db->searchTable( $table, 'owner', '=', $param['aid'] );
			$table	 = $db->searchTable( $table, 'c_type', '=', $param['c_type'] );
			$table	 = $db->searchTable( $table, 'year', '=', date('Y'));
	
			if( $db->getRow($table) > 0 )
			{// 対象レコードが存在する場合ソートランクを交換
				$rec = $db->getRecord( $table, 0 );
				$countData=unserialize($db->getData($rec,"count_data"));
				$countData[date('Y')][date('m')][date('d')]++;
				$db->setData($rec, "count_data", serialize($countData));
				$db->updateRecord($rec);
			}else{
				
				$countData[date('Y')][date('m')][date('d')]=1;
				
				$rec = $db->getNewRecord();
				$db->setData($rec, "owner", $param['aid']);
				$db->setData($rec, "c_type", $param['c_type']);
				$db->setData($rec, "count_data", serialize($countData));
				$db->setData($rec, "year", date('Y'));
				$db->setData($rec, "regist", time());
				$db->addRecord($rec);
				
			}
			//echo "callback({\"aid\":\"".$param["aid"]."\"})";
			
		}

	}

?>